<?php

namespace Okay\Modules\RokaStudio\TwoFactorAuthentication\Plugins;

use Okay\Core\Design;
use Okay\Core\SmartyPlugins\Func;

class EmailVerifyNoticePlugin extends Func
{
    protected $tag = 'rs_tfa_email_verify_notice';

    /** @var Design */
    private $design;

    public function __construct(Design $design)
    {
        $this->design = $design;
    }

    public function run()
    {
        $user        = $this->design->getVar('user');
        $userUpdated = $this->design->getVar('user_updated');

        // Only show after a profile save when the email became unverified
        if (empty($userUpdated) || empty($user)) {
            return null;
        }

        // If verified or field not present, nothing to show
        if (!isset($user->rs_tfa_email_verified) || $user->rs_tfa_email_verified) {
            return null;
        }

        $this->design->assign('rs_tfa_show_verify_notice', true);

        return $this->design->fetch('email_verify_notice.tpl');
    }
}
