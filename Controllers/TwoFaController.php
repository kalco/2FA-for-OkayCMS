<?php

namespace Okay\Modules\RokaStudio\TwoFactorAuthentication\Controllers;

use Okay\Controllers\AbstractController;
use Okay\Core\Response;
use Okay\Core\Router;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Helpers\TwoFaHelper;

class TwoFaController extends AbstractController
{
    public function verify(TwoFaHelper $twoFaHelper)
    {
        if (!$twoFaHelper->hasUserPending()) {
            Response::redirectTo(Router::generateUrl('login', [], true));
        }

        if ($this->request->method('post')) {
            $submittedCode = (string)$this->request->post('tfa_code');

            if ($userId = $twoFaHelper->verifyUserCode($submittedCode)) {
                $twoFaHelper->completeUserLogin($userId);
                Response::redirectTo(Router::generateUrl('user', [], true));
            }

            $this->design->assign('tfa_error', 'invalid_code');
        }

        $this->design->assign('noindex_follow', true);
        $this->response->setContent('tfa.tpl');
    }
}
