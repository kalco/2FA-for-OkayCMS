<?php

namespace Okay\Modules\RokaStudio\TwoFactorAuthentication\Init;

use Okay\Modules\RokaStudio\TwoFactorAuthentication\Controllers\ActivateController;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Controllers\MagicLoginController;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Controllers\NotVerifiedController;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Controllers\TwoFaController;

return [
    'RokaStudio.TwoFactorAuthentication.verify' => [
        'slug'     => '/2fa/verify',
        'params'   => [
            'controller' => TwoFaController::class,
            'method'     => 'verify',
        ],
        'to_front' => true,
    ],

    'RokaStudio.TwoFactorAuthentication.activate' => [
        'slug'     => '/account/activate',
        'params'   => [
            'controller' => ActivateController::class,
            'method'     => 'activate',
        ],
        'to_front' => true,
    ],

    'RokaStudio.TwoFactorAuthentication.not_verified' => [
        'slug'     => '/account/not-verified',
        'params'   => [
            'controller' => NotVerifiedController::class,
            'method'     => 'index',
        ],
        'to_front' => true,
    ],

    'RokaStudio.TwoFactorAuthentication.magic_login' => [
        'slug'     => '/account/magic-login',
        'params'   => [
            'controller' => MagicLoginController::class,
            'method'     => 'index',
        ],
        'to_front' => true,
    ],

    'RokaStudio.TwoFactorAuthentication.magic_link' => [
        'slug'     => '/account/magic-link',
        'params'   => [
            'controller' => MagicLoginController::class,
            'method'     => 'handleLink',
        ],
        'to_front' => true,
    ],
];
