<?php

namespace Okay\Modules\RokaStudio\TwoFactorAuthentication\Extenders;

use Okay\Admin\Controllers\AuthAdmin;
use Okay\Core\BackendTranslations;
use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Managers;
use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Core\Request;
use Okay\Core\Response;
use Okay\Core\Settings;
use Okay\Entities\ManagersEntity;
use Okay\Entities\UsersEntity;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Helpers\TwoFaHelper;

class BackendExtender implements ExtensionInterface
{
    private $twoFaHelper;
    private $request;
    private $response;
    private $design;
    private $settings;
    private $managers;
    private $entityFactory;
    private $managersEntity;
    private $usersEntity;
    private $backendTranslations;

    public function __construct(
        TwoFaHelper   $twoFaHelper,
        Request       $request,
        Response      $response,
        Design        $design,
        Settings      $settings,
        Managers      $managers,
        EntityFactory $entityFactory,
        BackendTranslations $backendTranslations
    ) {
        $this->twoFaHelper   = $twoFaHelper;
        $this->request       = $request;
        $this->response      = $response;
        $this->design        = $design;
        $this->settings      = $settings;
        $this->managers      = $managers;
        $this->entityFactory = $entityFactory;
        $this->managersEntity  = $entityFactory->get(ManagersEntity::class);
        $this->usersEntity     = $entityFactory->get(UsersEntity::class);
        $this->backendTranslations = $backendTranslations;
    }

    /**
     * Queue extension on [AuthAdmin::class, 'fetch'].
     *
     * Since queue extensions run AFTER AuthAdmin::fetch(), auth.tpl is already
     * rendered. When TFA-specific UI is needed, we assign Smarty variables and
     * re-render auth.tpl (which includes tfa_auth.tpl via modules.json injection).
     */
    public function interceptAdminAuth($output, $class): void
    {
        if ($class !== AuthAdmin::class) {
            return;
        }

        $passwordlessEnabled = $this->twoFaHelper->isPasswordlessForAdminsEnabled();
        $tfaEnabled          = $this->twoFaHelper->isEnabledForAdmins();

        // If neither feature is active, let AuthAdmin handle normally
        if (!$passwordlessEnabled && !$tfaEnabled) {
            return;
        }

        // ── Handle magic token from email link (GET ?magic_token=xxx) ────
        $magicToken = $this->request->get('magic_token');
        if (!$this->request->method('post') && !empty($magicToken)) {
            $row = $this->twoFaHelper->getValidToken((string)$magicToken, TwoFaHelper::TYPE_MAGIC_ADMIN);

            if ($row && !empty($row->manager_login)) {
                $this->twoFaHelper->consumeToken((int)$row->id);
                $this->completeAdminLogin($row->manager_login);
                return;
            }

            $this->design->assign('tfa_passwordless_enabled', $passwordlessEnabled);
            $this->design->assign('tfa_magic_message', 'magic_token_invalid');
            $this->renderAuthPage();
            return;
        }

        // ── Handle POST ─────────────────────────────────────────────────
        if ($this->request->method('post')) {

            // Passwordless login request
            if ($this->request->post('magic_admin_request')) {
                $this->handleMagicAdminRequest();
                return;
            }

            // 2FA code verification
            if ($this->request->post('tfa_code') && $this->twoFaHelper->hasAdminPending()) {
                $submittedCode = (string)$this->request->post('tfa_code');

                if ($login = $this->twoFaHelper->verifyAdminCode($submittedCode)) {
                    $this->completeAdminLogin($login);
                    return;
                }

                // Wrong code
                $this->design->assign('tfa_passwordless_enabled', $passwordlessEnabled);
                $this->design->assign('tfa_error', 'invalid_code');
                $this->design->assign('tfa_pending', true);
                $this->renderAuthPage();
                return;
            }

            // Credentials POST: intercept valid credentials when 2FA is needed
            if ($tfaEnabled && !$passwordlessEnabled) {
                $login    = (string)$this->request->post('login');
                $password = (string)$this->request->post('password');

                if (!empty($login) && !empty($password)) {
                    /** @var ManagersEntity $managersEntity */
                    $managersEntity = $this->entityFactory->get(ManagersEntity::class);
                    $manager        = $managersEntity->get($login);

                    if ($manager && $this->managers->checkPassword($password, $manager->password)) {
                        // Per-manager 2FA disable or no email — let AuthAdmin log in normally
                        if (!empty($manager->rs_tfa_disabled) || empty($manager->email)) {
                            return;
                        }

                        // Valid credentials, 2FA required — set pending and redirect (PRG)
                        $this->twoFaHelper->setAdminPending($manager->login, $manager->email);
                        Response::redirectTo($this->request->getRootUrl() . '/backend/index.php?controller=AuthAdmin');
                        return;
                    }

                    // Wrong credentials — AuthAdmin already rendered the error, nothing to do
                    return;
                }
            }
        }

        // ── GET request: check for pending 2FA ──────────────────────────
        if ($this->twoFaHelper->hasAdminPending()) {
            if ($this->twoFaHelper->isAdminCodeExpired()) {
                $this->twoFaHelper->clearAdminPending();
                return;
            }

            $this->design->assign('tfa_passwordless_enabled', $passwordlessEnabled);
            $this->design->assign('tfa_pending', true);
            $this->renderAuthPage();
            return;
        }

        // Passwordless mode: re-render so JS can modify the form
        if ($passwordlessEnabled) {
            $this->design->assign('tfa_passwordless_enabled', true);
            $this->renderAuthPage();
            return;
        }
    }

    // -------------------------------------------------------------------------
    // Magic (passwordless) admin login
    // -------------------------------------------------------------------------

    private function handleMagicAdminRequest(): void
    {
        $this->design->assign('tfa_passwordless_enabled', $this->twoFaHelper->isPasswordlessForAdminsEnabled());

        if (!$this->twoFaHelper->isPasswordlessForAdminsEnabled()) {
            $this->design->assign('tfa_magic_message', 'magic_disabled');
            $this->renderAuthPage();
            return;
        }

        $login = trim((string)$this->request->post('magic_admin_login'));

        if (empty($login)) {
            $this->design->assign('tfa_magic_message', 'magic_empty');
            $this->renderAuthPage();
            return;
        }

        /** @var ManagersEntity $managersEntity */
        $managersEntity = $this->entityFactory->get(ManagersEntity::class);
        $manager        = $managersEntity->get($login);

        if ($manager && !empty($manager->email)) {
            $adminBaseUrl = $this->request->getRootUrl() . '/backend/index.php';
            $this->twoFaHelper->sendMagicAdminEmail($manager->email, $manager->login, $adminBaseUrl);
        }

        // Always show "sent" to prevent user enumeration
        $this->design->assign('tfa_magic_message', 'magic_sent');
        $this->renderAuthPage();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function completeAdminLogin(string $login): void
    {
        $_SESSION['admin'] = $login;
        $redirectUrl = !empty($_SESSION['before_auth_url'])
            ? $_SESSION['before_auth_url']
            : $this->request->getBasePathWithDomain() . '/backend/index.php';
        unset($_SESSION['before_auth_url']);
        Response::redirectTo($redirectUrl);
    }

    /**
     * Re-render auth.tpl with assigned Smarty variables.
     * The tfa_auth.tpl snippet is automatically injected via modules.json.
     */
    private function renderAuthPage(): void
    {
        $this->backendTranslations->initTranslations($this->settings->get('email_lang'));
        $this->design->assign('btr', $this->backendTranslations);
        $this->response->setContent($this->design->fetch('auth.tpl'));
        $this->response->sendContent();
        exit;
    }

    public function updateTfa()
    {
        $enabledTfa = $this->request->post('update_2fa');
        $id = $this->request->post('id', 'integer');
        $this->managersEntity->update($id, ['rs_tfa_disabled' => (bool)$enabledTfa]);
    }

    public function updateTfaUser()
    {
        $enabledTfaUser = $this->request->post('rs_tfa_disabled');
        $id = $this->request->post('id', 'integer');
        $this->usersEntity->update($id, ['rs_tfa_disabled' => (bool)$enabledTfaUser]);

        // Admin manually activates user email
        if ($this->request->post('rs_tfa_verify_email')) {
            $this->usersEntity->update($id, ['rs_tfa_email_verified' => 1]);
        }
    }
}
