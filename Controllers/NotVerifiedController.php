<?php

namespace Okay\Modules\RokaStudio\TwoFactorAuthentication\Controllers;

use Okay\Controllers\AbstractController;
use Okay\Core\EntityFactory;
use Okay\Entities\UsersEntity;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Helpers\TwoFaHelper;

class NotVerifiedController extends AbstractController
{
    public function index(TwoFaHelper $twoFaHelper, EntityFactory $entityFactory)
    {
        $this->design->assign('noindex_follow', true);

        $pendingUserId = (int)($_SESSION['tfa_unverified_user_id'] ?? 0);

        if ($this->request->method('post') && $pendingUserId > 0) {
            /** @var UsersEntity $usersEntity */
            $usersEntity = $entityFactory->get(UsersEntity::class);
            $user        = $usersEntity->get($pendingUserId);

            if ($user && !empty($user->email)) {
                $twoFaHelper->sendActivationEmail($pendingUserId, $user->email);
                $this->design->assign('resent', true);
            }
        }

        $hasSession = $pendingUserId > 0;
        $this->design->assign('has_session', $hasSession);

        $this->response->setContent('not_verified.tpl');
    }
}
