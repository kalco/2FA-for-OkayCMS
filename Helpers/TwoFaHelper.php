<?php

namespace Okay\Modules\RokaStudio\TwoFactorAuthentication\Helpers;

use Okay\Core\BackendTranslations;
use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\FrontTranslations;
use Okay\Core\Notify;
use Okay\Core\Request;
use Okay\Core\Settings;
use Okay\Core\TemplateConfig\FrontTemplateConfig;
use Okay\Entities\UsersEntity;
use Okay\Helpers\UserHelper;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Entities\TwoFaTokensEntity;

class TwoFaHelper
{
    const SESSION_USER_PREFIX  = 'tfa_user_';
    const SESSION_ADMIN_PREFIX = 'tfa_admin_';

    const CODE_TTL = 600;

    const TYPE_VERIFY_REG  = 'verify_reg';
    const TYPE_MAGIC_USER  = 'magic_user';
    const TYPE_MAGIC_ADMIN = 'magic_admin';

    private $notify;
    private $request;
    private $entityFactory;
    private $design;
    private $settings;
    private $frontTemplateConfig;
    private $frontTranslations;
    private $backendTranslations;
    private $userHelper;

    public function __construct(
        Notify              $notify,
        Request             $request,
        EntityFactory       $entityFactory,
        Design              $design,
        Settings            $settings,
        FrontTemplateConfig $frontTemplateConfig,
        FrontTranslations   $frontTranslations,
        BackendTranslations $backendTranslations,
        UserHelper          $userHelper
    ) {
        $this->notify              = $notify;
        $this->request             = $request;
        $this->entityFactory       = $entityFactory;
        $this->design              = $design;
        $this->settings            = $settings;
        $this->frontTemplateConfig = $frontTemplateConfig;
        $this->frontTranslations   = $frontTranslations;
        $this->backendTranslations = $backendTranslations;
        $this->userHelper          = $userHelper;
    }

    /**
     * Generate a random 6-digit numeric code.
     */
    public function generateCode(): string
    {
        return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /** Returns true if 2FA is enabled for frontend users (default: true). */
    public function isEnabledForUsers(): bool
    {
        $val = $this->settings->get('rs__tfa__enable_users');
        return $val === null ? true : (bool)$val;
    }

    /** Returns true if 2FA is enabled for admin managers (default: true). */
    public function isEnabledForAdmins(): bool
    {
        $val = $this->settings->get('rs__tfa__enable_admins');
        return $val === null ? true : (bool)$val;
    }

    /** Returns true if registration email verification is enabled (default: false). */
    public function isVerifyRegistrationEnabled(): bool
    {
        return (bool)$this->settings->get('rs__tfa__verify_registration');
    }

    /** Returns true if passwordless login is enabled for frontend users (default: false). */
    public function isPasswordlessForUsersEnabled(): bool
    {
        return (bool)$this->settings->get('rs__tfa__passwordless_users');
    }

    /** Returns true if passwordless login is enabled for admin managers (default: false). */
    public function isPasswordlessForAdminsEnabled(): bool
    {
        return (bool)$this->settings->get('rs__tfa__passwordless_admins');
    }

    /** Returns the configured code TTL in seconds (falls back to CODE_TTL). */
    public function getCodeTtlSeconds(): int
    {
        $minutes = (int)$this->settings->get('rs__tfa__code_ttl');
        return $minutes > 0 ? $minutes * 60 : self::CODE_TTL;
    }

    // -------------------------------------------------------------------------
    // Per-user / per-manager 2FA disable helpers
    // -------------------------------------------------------------------------

    /** Returns true if 2FA is disabled for this specific user. */
    public function isUserTfaDisabled(int $userId): bool
    {
        /** @var UsersEntity $usersEntity */
        $usersEntity = $this->entityFactory->get(UsersEntity::class);
        $user = $usersEntity->get($userId);
        return $user && !empty($user->rs_tfa_disabled);
    }

    /** Returns true if the user's email is verified (or verification is disabled). */
    public function isUserEmailVerified(int $userId): bool
    {
        if (!$this->isVerifyRegistrationEnabled()) {
            return true;
        }
        /** @var UsersEntity $usersEntity */
        $usersEntity = $this->entityFactory->get(UsersEntity::class);
        $user = $usersEntity->get($userId);
        // If field not present (module installed without running install), treat as verified
        if (!$user) {
            return true;
        }
        return isset($user->rs_tfa_email_verified) ? (bool)$user->rs_tfa_email_verified : true;
    }

    // -------------------------------------------------------------------------
    // Frontend (user) session helpers
    // -------------------------------------------------------------------------

    /**
     * Store a pending user 2FA state in the session.
     * Clears any previously set $user_id from session so the user is NOT yet logged in.
     */
    public function setUserPending(int $userId, string $email): void
    {
        unset($_SESSION['user_id']);

        $code = $this->generateCode();

        $_SESSION[self::SESSION_USER_PREFIX . 'pending_id']  = $userId;
        $_SESSION[self::SESSION_USER_PREFIX . 'code']        = $code;
        $_SESSION[self::SESSION_USER_PREFIX . 'expires']     = time() + $this->getCodeTtlSeconds();

        $this->sendUserCode($email, $code);
    }

    /**
     * Verify a user-submitted 2FA code.
     * Returns the pending user ID on success, false on failure / expiry.
     */
    public function verifyUserCode(string $submittedCode)
    {
        if (empty($_SESSION[self::SESSION_USER_PREFIX . 'pending_id'])) {
            return false;
        }

        if (empty($_SESSION[self::SESSION_USER_PREFIX . 'code'])) {
            return false;
        }

        if (time() > (int)$_SESSION[self::SESSION_USER_PREFIX . 'expires']) {
            $this->clearUserPending();
            return false;
        }

        if (!hash_equals($_SESSION[self::SESSION_USER_PREFIX . 'code'], trim($submittedCode))) {
            return false;
        }

        $userId = (int)$_SESSION[self::SESSION_USER_PREFIX . 'pending_id'];
        $this->clearUserPending();
        return $userId;
    }

    public function hasUserPending(): bool
    {
        return !empty($_SESSION[self::SESSION_USER_PREFIX . 'pending_id']);
    }

    public function clearUserPending(): void
    {
        unset(
            $_SESSION[self::SESSION_USER_PREFIX . 'pending_id'],
            $_SESSION[self::SESSION_USER_PREFIX . 'code'],
            $_SESSION[self::SESSION_USER_PREFIX . 'expires']
        );
    }

    /**
     * Complete the user login after successful 2FA or magic-link verification.
     * Mirrors UserHelper::login() — sets session, updates IP, merges cart/wishlist.
     */
    public function completeUserLogin(int $userId): void
    {
        /** @var UsersEntity $usersEntity */
        $usersEntity = $this->entityFactory->get(UsersEntity::class);

        $_SESSION['user_id'] = $userId;
        $usersEntity->update($userId, ['last_ip' => $_SERVER['REMOTE_ADDR'] ?? '']);

        $this->userHelper->mergeCart();
        $this->userHelper->mergeWishlist();
        $this->userHelper->mergeComparison();
        $this->userHelper->mergeBrowsedProducts();
    }

    // -------------------------------------------------------------------------
    // Admin session helpers
    // -------------------------------------------------------------------------

    /**
     * Store a pending admin 2FA state in the session.
     * Does NOT set $_SESSION['admin'] so the manager is NOT yet authenticated.
     */
    public function setAdminPending(string $managerLogin, string $email): void
    {
        $code = $this->generateCode();

        $_SESSION[self::SESSION_ADMIN_PREFIX . 'pending_login'] = $managerLogin;
        $_SESSION[self::SESSION_ADMIN_PREFIX . 'code']          = $code;
        $_SESSION[self::SESSION_ADMIN_PREFIX . 'expires']       = time() + $this->getCodeTtlSeconds();

        $this->sendAdminCode($email, $code);
    }

    /**
     * Verify the submitted admin 2FA code.
     * Returns the pending manager login on success, false on failure / expiry.
     */
    public function verifyAdminCode(string $submittedCode)
    {
        if (empty($_SESSION[self::SESSION_ADMIN_PREFIX . 'pending_login'])) {
            return false;
        }

        if (empty($_SESSION[self::SESSION_ADMIN_PREFIX . 'code'])) {
            return false;
        }

        if (time() > (int)$_SESSION[self::SESSION_ADMIN_PREFIX . 'expires']) {
            $this->clearAdminPending();
            return false;
        }

        if (!hash_equals($_SESSION[self::SESSION_ADMIN_PREFIX . 'code'], trim($submittedCode))) {
            return false;
        }

        $login = $_SESSION[self::SESSION_ADMIN_PREFIX . 'pending_login'];
        $this->clearAdminPending();
        return $login;
    }

    public function hasAdminPending(): bool
    {
        return !empty($_SESSION[self::SESSION_ADMIN_PREFIX . 'pending_login']);
    }

    public function clearAdminPending(): void
    {
        unset(
            $_SESSION[self::SESSION_ADMIN_PREFIX . 'pending_login'],
            $_SESSION[self::SESSION_ADMIN_PREFIX . 'code'],
            $_SESSION[self::SESSION_ADMIN_PREFIX . 'expires']
        );
    }

    public function isAdminCodeExpired(): bool
    {
        if (empty($_SESSION[self::SESSION_ADMIN_PREFIX . 'expires'])) {
            return true;
        }
        return time() > (int)$_SESSION[self::SESSION_ADMIN_PREFIX . 'expires'];
    }

    // -------------------------------------------------------------------------
    // Token management (for registration verification and magic/passwordless login)
    // -------------------------------------------------------------------------

    /**
     * Create and persist a one-time token.
     * Cleans up any previous tokens of the same type for the same subject.
     *
     * @return string  The raw 64-char hex token value
     */
    public function createToken(string $type, ?int $userId = null, ?string $managerLogin = null): string
    {
        /** @var TwoFaTokensEntity $tokensEntity */
        $tokensEntity = $this->entityFactory->get(TwoFaTokensEntity::class);

        // Remove old tokens of same type for this subject
        if ($userId !== null) {
            $old = $tokensEntity->find(['user_id' => $userId, 'type' => $type]);
            foreach ($old as $row) {
                $tokensEntity->delete((int)$row->id);
            }
        } elseif ($managerLogin !== null) {
            $old = $tokensEntity->find(['manager_login' => $managerLogin, 'type' => $type]);
            foreach ($old as $row) {
                $tokensEntity->delete((int)$row->id);
            }
        }

        $token = bin2hex(random_bytes(32)); // 64 hex chars

        $tokensEntity->add([
            'token'         => $token,
            'type'          => $type,
            'user_id'       => $userId,
            'manager_login' => $managerLogin,
            'expires_at'    => time() + $this->getCodeTtlSeconds(),
            'created_at'    => time(),
        ]);

        return $token;
    }

    /**
     * Retrieve and validate a token by its raw value.
     * Returns the token row on success, null if not found or expired.
     */
    public function getValidToken(string $tokenValue, string $expectedType)
    {
        /** @var TwoFaTokensEntity $tokensEntity */
        $tokensEntity = $this->entityFactory->get(TwoFaTokensEntity::class);

        $row = $tokensEntity->findOne(['token' => $tokenValue, 'type' => $expectedType]);

        if (!$row) {
            return null;
        }

        if (time() > (int)$row->expires_at) {
            $tokensEntity->delete((int)$row->id);
            return null;
        }

        return $row;
    }

    /**
     * Delete a token by ID (after it has been consumed).
     */
    public function consumeToken(int $tokenId): void
    {
        $tokensEntity = $this->entityFactory->get(TwoFaTokensEntity::class);
        $tokensEntity->delete($tokenId);
    }

    // -------------------------------------------------------------------------
    // Email delivery: 2FA codes
    // -------------------------------------------------------------------------

    private function sendUserCode(string $to, string $code): void
    {
        $this->settings->initSettings();
        $this->design->assign('settings', $this->settings);
        $this->design->assign('lang', $this->frontTranslations);
        $this->design->assign('tfa_code', $code);
        $this->design->assign('tfa_code_ttl_min', (int)($this->getCodeTtlSeconds() / 60));

        $tpl  = __DIR__ . '/../design/html/email/email_2fa_user.tpl';
        $body = $this->design->fetch($tpl);

        $subject = $this->design->getVar('subject');

        $from = $this->settings->get('notify_from_name')
            ? $this->settings->get('notify_from_name') . ' <' . $this->settings->get('notify_from_email') . '>'
            : $this->settings->get('notify_from_email');

        $this->notify->email($to, $subject, $body, $from);

        $this->design->smarty->clearAssign('tfa_code');
        $this->design->smarty->clearAssign('tfa_code_ttl_min');
    }

    private function sendAdminCode(string $to, string $code): void
    {
        $this->backendTranslations->initTranslations($this->settings->get('email_lang'));
        $this->design->assign('btr', $this->backendTranslations);
        $this->design->assign('tfa_code', $code);
        $this->design->assign('tfa_code_ttl_min', (int)($this->getCodeTtlSeconds() / 60));

        $tpl  = __DIR__ . '/../Backend/design/html/email/email_2fa_admin.tpl';
        $body = $this->design->fetch($tpl);

        $subject = $this->design->getVar('subject');

        $this->notify->email($to, $subject, $body, $this->settings->get('notify_from_name'));

        $this->design->smarty->clearAssign('tfa_code');
        $this->design->smarty->clearAssign('tfa_code_ttl_min');
    }

    // -------------------------------------------------------------------------
    // Email delivery: registration activation
    // -------------------------------------------------------------------------

    /**
     * Create an activation token for the user and send the activation email.
     */
    public function sendActivationEmail(int $userId, string $email): void
    {
        $token       = $this->createToken(self::TYPE_VERIFY_REG, $userId);
        $activateUrl = $this->buildFrontendUrl('/account/activate?token=' . $token);

        $this->settings->initSettings();
        $this->design->assign('settings', $this->settings);
        $this->design->assign('lang', $this->frontTranslations);
        $this->design->assign('activation_url', $activateUrl);
        $this->design->assign('tfa_code_ttl_min', (int)($this->getCodeTtlSeconds() / 60));

        $tpl  = __DIR__ . '/../design/html/email/email_activation.tpl';
        $body = $this->design->fetch($tpl);

        $subject = $this->design->getVar('subject');

        $from = $this->settings->get('notify_from_name')
            ? $this->settings->get('notify_from_name') . ' <' . $this->settings->get('notify_from_email') . '>'
            : $this->settings->get('notify_from_email');

        $this->notify->email($email, $subject, $body, $from);

        $this->design->smarty->clearAssign('activation_url');
        $this->design->smarty->clearAssign('tfa_code_ttl_min');
    }

    // -------------------------------------------------------------------------
    // Email delivery: magic (passwordless) login
    // -------------------------------------------------------------------------

    /**
     * Create a magic-login token for the user and send the email.
     */
    public function sendMagicLoginEmail(int $userId, string $email): void
    {
        $token    = $this->createToken(self::TYPE_MAGIC_USER, $userId);
        $loginUrl = $this->buildFrontendUrl('/account/magic-link?token=' . $token);

        $this->settings->initSettings();
        $this->design->assign('settings', $this->settings);
        $this->design->assign('lang', $this->frontTranslations);
        $this->design->assign('magic_login_url', $loginUrl);
        $this->design->assign('tfa_code_ttl_min', (int)($this->getCodeTtlSeconds() / 60));

        $tpl  = __DIR__ . '/../design/html/email/email_magic_login_user.tpl';
        $body = $this->design->fetch($tpl);

        $subject = $this->design->getVar('subject');

        $from = $this->settings->get('notify_from_name')
            ? $this->settings->get('notify_from_name') . ' <' . $this->settings->get('notify_from_email') . '>'
            : $this->settings->get('notify_from_email');

        $this->notify->email($email, $subject, $body, $from);

        $this->design->smarty->clearAssign('magic_login_url');
        $this->design->smarty->clearAssign('tfa_code_ttl_min');
    }

    /**
     * Create a magic-login token for an admin manager and send the email.
     *
     * @param string $adminBaseUrl  e.g. "https://example.com/backend/index.php"
     */
    public function sendMagicAdminEmail(string $email, string $managerLogin, string $adminBaseUrl): void
    {
        $token    = $this->createToken(self::TYPE_MAGIC_ADMIN, null, $managerLogin);
        $loginUrl = $adminBaseUrl . '?controller=AuthAdmin&magic_token=' . $token;

        $this->backendTranslations->initTranslations($this->settings->get('email_lang'));
        $this->design->assign('btr', $this->backendTranslations);
        $this->design->assign('magic_login_url', $loginUrl);
        $this->design->assign('tfa_code_ttl_min', (int)($this->getCodeTtlSeconds() / 60));

        $tpl  = __DIR__ . '/../Backend/design/html/email/email_magic_admin.tpl';
        $body = $this->design->fetch($tpl);

        $subject = $this->design->getVar('subject');

        $this->notify->email($email, $subject, $body, $this->settings->get('notify_from_name'));

        $this->design->smarty->clearAssign('magic_login_url');
        $this->design->smarty->clearAssign('tfa_code_ttl_min');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function buildFrontendUrl(string $path): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . $path;
    }
}
