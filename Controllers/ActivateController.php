<?php

namespace Okay\Modules\RokaStudio\TwoFactorAuthentication\Controllers;

use Okay\Controllers\AbstractController;
use Okay\Core\EntityFactory;
use Okay\Entities\UsersEntity;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Helpers\TwoFaHelper;

class ActivateController extends AbstractController
{
    public function activate(TwoFaHelper $twoFaHelper, EntityFactory $entityFactory)
    {
        $tokenValue = (string)($this->request->get('token') ?? '');

        $this->design->assign('noindex_follow', true);

        if (empty($tokenValue)) {
            $this->design->assign('activation_result', 'invalid');
            $this->response->setContent('activate_notice.tpl');
            return;
        }

        $row = $twoFaHelper->getValidToken($tokenValue, TwoFaHelper::TYPE_VERIFY_REG);

        if (!$row || empty($row->user_id)) {
            $this->design->assign('activation_result', 'invalid');
            $this->response->setContent('activate_notice.tpl');
            return;
        }

        $userId = (int)$row->user_id;

        /** @var UsersEntity $usersEntity */
        $usersEntity = $entityFactory->get(UsersEntity::class);
        $usersEntity->update($userId, ['rs_tfa_email_verified' => 1]);

        $twoFaHelper->consumeToken((int)$row->id);

        unset($_SESSION['tfa_unverified_user_id']);

        $this->design->assign('activation_result', 'success');
        $this->response->setContent('activate_notice.tpl');
    }
}
