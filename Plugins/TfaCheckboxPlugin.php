<?php

namespace Okay\Modules\RokaStudio\TwoFactorAuthentication\Plugins;

use Okay\Core\Design;
use Okay\Core\SmartyPlugins\Func;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Helpers\TwoFaHelper;

class TfaCheckboxPlugin extends Func
{
    protected $tag = 'rs_tfa_checkbox';

    /** @var Design */
    private $design;

    /** @var TwoFaHelper */
    private $twoFaHelper;

    public function __construct(Design $design, TwoFaHelper $twoFaHelper)
    {
        $this->design      = $design;
        $this->twoFaHelper = $twoFaHelper;
    }

    public function run()
    {
        $user = $this->design->getVar('user');
        if (empty($user) || !isset($user->rs_tfa_disabled)) {
            return null;
        }

        $this->design->assign('rs_tfa_global_off', !$this->twoFaHelper->isEnabledForUsers());
        $this->design->assign('rs_tfa_user_disabled', !empty($user->rs_tfa_disabled));

        return $this->design->fetch('user_tfa_checkbox.tpl');
    }
}
