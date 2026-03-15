<?php

use Okay\Core\Design;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Plugins\TfaCheckboxPlugin;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Plugins\EmailVerifyNoticePlugin;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Helpers\TwoFaHelper;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;

return [
    TfaCheckboxPlugin::class => [
        'class'     => TfaCheckboxPlugin::class,
        'arguments' => [
            new SR(Design::class),
            new SR(TwoFaHelper::class),
        ],
    ],
    EmailVerifyNoticePlugin::class => [
        'class'     => EmailVerifyNoticePlugin::class,
        'arguments' => [
            new SR(Design::class),
        ],
    ],
];
