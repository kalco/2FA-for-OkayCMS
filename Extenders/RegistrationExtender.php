<?php

namespace Okay\Modules\RokaStudio\TwoFactorAuthentication\Extenders;

use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Core\Response;
use Okay\Core\Router;
use Okay\Entities\UsersEntity;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Helpers\TwoFaHelper;

class RegistrationExtender implements ExtensionInterface
{
    private $twoFaHelper;
    private $entityFactory;

    /**
     * Static cache: userId => email.
     * Populated by cacheUserEmail() (chained to UsersEntity::get()),
     * consumed by afterUserUpdate() to detect email changes.
     */
    private static $oldEmailCache = [];

    public function __construct(TwoFaHelper $twoFaHelper, EntityFactory $entityFactory)
    {
        $this->twoFaHelper   = $twoFaHelper;
        $this->entityFactory = $entityFactory;
    }

    // -------------------------------------------------------------------------
    // Chain extension for UserHelper::register()
    // -------------------------------------------------------------------------

    /**
     * Called after UserHelper::register() completes.
     * When email verification is enabled, prevents auto-login and redirects
     * to the not-verified page so the user must activate their account first.
     *
     * @param int|false $userId  The newly registered user ID
     * @param object    $user    The user data passed to register()
     * @return int|false
     */
    public function interceptRegister($userId, $user)
    {
        if (!$userId || !$this->twoFaHelper->isVerifyRegistrationEnabled()) {
            return $userId;
        }

        unset($_SESSION['user_id']);
        $_SESSION['tfa_unverified_user_id'] = (int)$userId;

        $notVerifiedUrl = Router::generateUrl('RokaStudio.TwoFactorAuthentication.not_verified', [], true);
        Response::redirectTo($notVerifiedUrl);
        return false;
    }

    // -------------------------------------------------------------------------
    // Chain extension for UsersEntity::get()  –  cache user email
    // -------------------------------------------------------------------------

    /**
     * Called after UsersEntity::get($id) completes.
     * Caches the user's current email so afterUserUpdate() can detect changes.
     *
     * @param object|false $user  The user object (or false if not found)
     * @param mixed        $id    The user ID or email passed to get()
     * @return object|false
     */
    public function cacheUserEmail($user, $id)
    {
        if ($user && !empty($user->id) && !empty($user->email)) {
            self::$oldEmailCache[(int)$user->id] = $user->email;
        }
        return $user;
    }

    // -------------------------------------------------------------------------
    // Chain extension for UsersEntity::add()
    // -------------------------------------------------------------------------

    /**
     * Called after UsersEntity::add($data) completes.
     *
     * @param int|false $userId  The newly created user ID (or false on failure)
     * @param array     $data    The data array passed to add()
     * @return int|false
     */
    public function afterUserAdd($userId, $data)
    {
        // Nothing to do if add failed or verification is disabled
        if (!$userId || !$this->twoFaHelper->isVerifyRegistrationEnabled()) {
            return $userId;
        }

        $data  = (array)$data;
        $email = $data['email'] ?? '';
        if (empty($email)) {
            return $userId; // No email – cannot send activation link
        }

        /** @var UsersEntity $usersEntity */
        $usersEntity = $this->entityFactory->get(UsersEntity::class);

        // Mark user as unverified
        $usersEntity->update((int)$userId, ['rs_tfa_email_verified' => 0]);

        // Create token and send activation email
        $this->twoFaHelper->sendActivationEmail((int)$userId, $email);

        return $userId;
    }

    // -------------------------------------------------------------------------
    // Chain extension for UsersEntity::update()
    // -------------------------------------------------------------------------

    /**
     * Called after UsersEntity::update($ids, $object) completes.
     *
     * CRUD::update() calls ExtenderFacade::execute with func_get_args(),
     * so the chain receives: ($result, $ids, $object).
     *
     * Uses the static $oldEmailCache (populated by cacheUserEmail) to compare
     * old email vs new email, since the DB already contains the updated value
     * by the time this extension runs.
     *
     * @param bool             $result  Return value from update (true on success)
     * @param int|array        $ids     User ID(s) being updated
     * @param object|array     $data    The update data
     * @return bool
     */
    public function afterUserUpdate($result, $ids, $data)
    {
        if (!$result || !$this->twoFaHelper->isVerifyRegistrationEnabled()) {
            return $result;
        }

        $data = (array)$data;

        // Only act when the email field is present in the update
        if (empty($data['email'])) {
            return $result;
        }

        $newEmail = $data['email'];

        /** @var UsersEntity $usersEntity */
        $usersEntity = $this->entityFactory->get(UsersEntity::class);

        // update() may receive multiple IDs, but typically it's a single user
        $userIds = (array)$ids;
        foreach ($userIds as $userId) {
            $userId = (int)$userId;

            // Compare with cached old email (populated by cacheUserEmail via get())
            $oldEmail = self::$oldEmailCache[$userId] ?? null;

            if ($oldEmail === null) {
                // No cached email – cannot determine if it changed, skip
                continue;
            }

            // Skip if email hasn't actually changed
            if ($oldEmail === $newEmail) {
                continue;
            }

            // Email changed! Mark as unverified and send activation email to the new address
            $usersEntity->update($userId, ['rs_tfa_email_verified' => 0]);
            $this->twoFaHelper->sendActivationEmail($userId, $newEmail);

            // Update cache with new email to prevent duplicate sends
            self::$oldEmailCache[$userId] = $newEmail;

            // If this is the currently logged-in user, log them out
            if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $userId) {
                unset($_SESSION['user_id']);
                $_SESSION['tfa_unverified_user_id'] = $userId;
            }
        }

        return $result;
    }
}
