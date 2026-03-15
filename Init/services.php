<?php

namespace Okay\Modules\RokaStudio\TwoFactorAuthentication;

use Okay\Core\BackendTranslations;
use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\FrontTranslations;
use Okay\Core\Managers;
use Okay\Core\Notify;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;
use Okay\Core\Request;
use Okay\Core\Response;
use Okay\Core\Settings;
use Okay\Core\TemplateConfig\FrontTemplateConfig;
use Okay\Helpers\UserHelper;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Extenders\BackendExtender;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Extenders\FrontExtender;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Extenders\RegistrationExtender;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Helpers\TwoFaHelper;

return [
    TwoFaHelper::class => [
        'class'     => TwoFaHelper::class,
        'arguments' => [
            new SR(Notify::class),
            new SR(Request::class),
            new SR(EntityFactory::class),
            new SR(Design::class),
            new SR(Settings::class),
            new SR(FrontTemplateConfig::class),
            new SR(FrontTranslations::class),
            new SR(BackendTranslations::class),
            new SR(UserHelper::class),
        ],
    ],

    FrontExtender::class => [
        'class'     => FrontExtender::class,
        'arguments' => [
            new SR(TwoFaHelper::class),
            new SR(Request::class),
            new SR(Response::class),
            new SR(EntityFactory::class),
        ],
    ],

    BackendExtender::class => [
        'class'     => BackendExtender::class,
        'arguments' => [
            new SR(TwoFaHelper::class),
            new SR(Request::class),
            new SR(Response::class),
            new SR(Design::class),
            new SR(Settings::class),
            new SR(Managers::class),
            new SR(EntityFactory::class),
            new SR(BackendTranslations::class),
        ],
    ],

    RegistrationExtender::class => [
        'class'     => RegistrationExtender::class,
        'arguments' => [
            new SR(TwoFaHelper::class),
            new SR(EntityFactory::class),
        ],
    ],
];
