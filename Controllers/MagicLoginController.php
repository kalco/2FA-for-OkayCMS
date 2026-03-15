<?php

namespace Okay\Modules\RokaStudio\TwoFactorAuthentication\Controllers;

use Okay\Controllers\AbstractController;
use Okay\Core\Response;
use Okay\Core\Router;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Helpers\TwoFaHelper;

class MagicLoginController extends AbstractController
{
    public function handleLink(TwoFaHelper $twoFaHelper)
    {
        $tokenValue = (string)($this->request->get('token') ?? '');

        if (empty($tokenValue)) {
            Response::redirectTo(Router::generateUrl('login', [], true));
            return;
        }

        $row = $twoFaHelper->getValidToken($tokenValue, TwoFaHelper::TYPE_MAGIC_USER);

        if (!$row || empty($row->user_id)) {
            Response::redirectTo(Router::generateUrl('login', [], true));
            return;
        }

        $userId = (int)$row->user_id;
        $twoFaHelper->consumeToken((int)$row->id);
        $twoFaHelper->completeUserLogin($userId);

        Response::redirectTo(Router::generateUrl('user', [], true));
    }
}
