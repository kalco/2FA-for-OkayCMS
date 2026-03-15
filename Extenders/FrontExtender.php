<?php

namespace Okay\Modules\RokaStudio\TwoFactorAuthentication\Extenders;

use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Core\Request;
use Okay\Core\Response;
use Okay\Core\Router;
use Okay\Entities\UsersEntity;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Helpers\TwoFaHelper;

class FrontExtender implements ExtensionInterface
{
    private $twoFaHelper;
    private $request;
    private $response;
    private $entityFactory;

    public function __construct(
        TwoFaHelper   $twoFaHelper,
        Request       $request,
        Response      $response,
        EntityFactory $entityFactory
    ) {
        $this->twoFaHelper   = $twoFaHelper;
        $this->request       = $request;
        $this->response      = $response;
        $this->entityFactory = $entityFactory;
    }

    /**
     * Chain extension for ValidateHelper::getUserLoginError.
     * Skips login validation for magic-link requests and TFA code submissions,
     * so UserHelper::login() is still called and our interceptLogin can handle them.
     */
    public function skipLoginValidation($error, $email, $password)
    {
        if ($this->request->post('magic_user_request')) {
            return null;
        }

        if ($this->request->post('tfa_code') && $this->twoFaHelper->hasUserPending()) {
            return null;
        }

        return $error;
    }

    /**
     * Chain extension for UserHelper::login.
     *
     * Handles three special POST types from tfa_login.tpl (injected into login.tpl):
     *   1. magic_user_request — send magic-login email, redirect back with ?magic_sent=1
     *   2. tfa_code           — verify 2FA code, complete login or redirect with error
     *   3. normal login       — if 2FA required, set pending and redirect back to login page
     */
    public function interceptLogin($userId, $email, $password)
    {
        // ── Magic-link request (passwordless login) ──────────────────────
        if ($this->request->post('magic_user_request')) {
            unset($_SESSION['user_id']);

            $email = trim((string)$email);
            if (!empty($email)) {
                /** @var UsersEntity $usersEntity */
                $usersEntity = $this->entityFactory->get(UsersEntity::class);
                $user = $usersEntity->get($email);

                if ($user && !empty($user->id)) {
                    $this->twoFaHelper->sendMagicLoginEmail((int)$user->id, $email);
                }
            }

            // Always show "sent" to prevent user enumeration
            Response::redirectTo(Router::generateUrl('login', [], true) . '?magic_sent=1');
            return false;
        }

        // ── TFA code verification ────────────────────────────────────────
        if ($this->request->post('tfa_code') && $this->twoFaHelper->hasUserPending()) {
            unset($_SESSION['user_id']);

            $code = (string)$this->request->post('tfa_code');

            if ($verifiedUserId = $this->twoFaHelper->verifyUserCode($code)) {
                $this->twoFaHelper->completeUserLogin($verifiedUserId);
                Response::redirectTo(Router::generateUrl('user', [], true));
                return $verifiedUserId;
            }

            // Wrong code or expired — check if pending was cleared (= expired)
            $error = $this->twoFaHelper->hasUserPending() ? 'invalid_code' : 'code_expired';
            Response::redirectTo(Router::generateUrl('login', [], true) . '?tfa_error=' . $error);
            return false;
        }

        // ── Normal login flow ────────────────────────────────────────────
        if (empty($userId)) {
            return $userId;
        }

        $userId = (int)$userId;

        // Registration verification check
        if ($this->twoFaHelper->isVerifyRegistrationEnabled() && !$this->twoFaHelper->isUserEmailVerified($userId)) {
            unset($_SESSION['user_id']);
            $_SESSION['tfa_unverified_user_id'] = $userId;

            $notVerifiedUrl = Router::generateUrl('RokaStudio.TwoFactorAuthentication.not_verified', [], true);
            Response::redirectTo($notVerifiedUrl);
            return false;
        }

        // 2FA disabled globally
        if (!$this->twoFaHelper->isEnabledForUsers()) {
            return $userId;
        }

        // 2FA disabled for this user
        /** @var UsersEntity $usersEntity */
        $usersEntity = $this->entityFactory->get(UsersEntity::class);
        $user = $usersEntity->get($userId);
        if ($user && !empty($user->rs_tfa_disabled)) {
            return $userId;
        }

        // Start 2FA: set pending and redirect back to login page
        $this->twoFaHelper->setUserPending($userId, $email);
        Response::redirectTo(Router::generateUrl('login', [], true));
        return false;
    }

    public function extendProfileUser($user)
    {
        if ($user === null) {
            return $user;
        }

        $user->rs_tfa_disabled = $this->request->post('rs_tfa_disabled') ? 1 : 0;

        return $user;
    }
}
