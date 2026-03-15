<?php

namespace Okay\Modules\RokaStudio\TwoFactorAuthentication\Controllers;

use Okay\Controllers\AbstractController;
use Okay\Core\EntityFactory;
use Okay\Core\Response;
use Okay\Core\Router;
use Okay\Entities\UsersEntity;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Helpers\TwoFaHelper;

class MagicLoginController extends AbstractController
{
    public function index(TwoFaHelper $twoFaHelper, EntityFactory $entityFactory)
    {
        if (!empty($_SESSION['user_id'])) {
            Response::redirectTo(Router::generateUrl('user', [], true));
        }

        $this->design->assign('noindex_follow', true);

        if (!$twoFaHelper->isPasswordlessForUsersEnabled()) {
            $this->design->assign('magic_disabled', true);
            $this->response->setContent('magic_login.tpl');
            return;
        }

        if ($this->request->method('post')) {
            $email = trim((string)$this->request->post('magic_email'));

            if (!empty($email)) {
                /** @var UsersEntity $usersEntity */
                $usersEntity = $entityFactory->get(UsersEntity::class);
                $user        = $usersEntity->findOne(['email' => $email]);

                if ($user) {
                    $twoFaHelper->sendMagicLoginEmail((int)$user->id, $email);
                }
            }

            $this->design->assign('magic_sent', true);
        }

        $this->response->setContent('magic_login.tpl');
    }

    public function handleLink(TwoFaHelper $twoFaHelper)
    {
        $this->design->assign('noindex_follow', true);

        $tokenValue = (string)($this->request->get('token') ?? '');

        if (empty($tokenValue)) {
            $this->design->assign('magic_invalid', true);
            $this->response->setContent('magic_login.tpl');
            return;
        }

        $row = $twoFaHelper->getValidToken($tokenValue, TwoFaHelper::TYPE_MAGIC_USER);

        if (!$row || empty($row->user_id)) {
            $this->design->assign('magic_invalid', true);
            $this->response->setContent('magic_login.tpl');
            return;
        }

        $userId = (int)$row->user_id;
        $twoFaHelper->consumeToken((int)$row->id);
        $twoFaHelper->completeUserLogin($userId);

        Response::redirectTo(Router::generateUrl('user', [], true));
    }
}
