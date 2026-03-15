<?php

namespace Okay\Modules\RokaStudio\TwoFactorAuthentication\Init;

use Okay\Admin\Controllers\AuthAdmin;
use Okay\Admin\Controllers\ManagerAdmin;
use Okay\Admin\Controllers\UserAdmin;
use Okay\Core\Modules\AbstractInit;
use Okay\Core\Modules\EntityField;
use Okay\Entities\ManagersEntity;
use Okay\Entities\UsersEntity;
use Okay\Helpers\UserHelper;
use Okay\Helpers\ValidateHelper;
use Okay\Requests\UserRequest;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Entities\TwoFaTokensEntity;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Extenders\BackendExtender;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Extenders\FrontExtender;
use Okay\Modules\RokaStudio\TwoFactorAuthentication\Extenders\RegistrationExtender;

class Init extends AbstractInit
{
    const PERMISSION = 'roka_studio__two_factor_auth';

    public function install()
    {
        $this->setBackendMainController('TwoFaAdmin');

        $this->migrateEntityTable(TwoFaTokensEntity::class, [
            (new EntityField('id'))->setIndexPrimaryKey()->setTypeInt(11, false)->setAutoIncrement(),
            (new EntityField('token'))->setTypeVarchar(64)->setIndexUnique(),
            (new EntityField('type'))->setTypeVarchar(20),
            (new EntityField('user_id'))->setTypeInt(11)->setNullable()->setDefault(null),
            (new EntityField('manager_login'))->setTypeVarchar(64)->setNullable()->setDefault(null),
            (new EntityField('expires_at'))->setTypeInt(11, false)->setDefault(0),
            (new EntityField('created_at'))->setTypeInt(11, false)->setDefault(0),
        ]);

        $this->migrateEntityField(
            UsersEntity::class,
            (new EntityField('rs_tfa_disabled'))->setTypeTinyInt(1)->setDefault(0)
        );
        $this->migrateEntityField(
            UsersEntity::class,
            (new EntityField('rs_tfa_email_verified'))->setTypeTinyInt(1)->setDefault(1)
        );

        $this->migrateEntityField(
            ManagersEntity::class,
            (new EntityField('rs_tfa_disabled'))->setTypeTinyInt(1)->setDefault(0)
        );
    }

    public function init()
    {
        $this->addPermission(self::PERMISSION);
        $this->registerBackendController('TwoFaAdmin');
        $this->addBackendControllerPermission('TwoFaAdmin', self::PERMISSION);

        $this->registerEntityField(UsersEntity::class, 'rs_tfa_disabled', false);
        $this->registerEntityField(UsersEntity::class, 'rs_tfa_email_verified', false);
        $this->registerEntityField(ManagersEntity::class, 'rs_tfa_disabled', false);

        $this->registerChainExtension(
            [ValidateHelper::class, 'getUserLoginError'],
            [FrontExtender::class, 'skipLoginValidation']
        );

        $this->registerChainExtension(
            [UserHelper::class, 'login'],
            [FrontExtender::class, 'interceptLogin']
        );

        $this->registerChainExtension(
            [UserRequest::class, 'postProfileUser'],
            [FrontExtender::class, 'extendProfileUser']
        );

        $this->registerChainExtension(
            [UserHelper::class, 'register'],
            [RegistrationExtender::class, 'interceptRegister']
        );

        $this->registerChainExtension(
            [UsersEntity::class, 'add'],
            [RegistrationExtender::class, 'afterUserAdd']
        );

        $this->registerChainExtension(
            [UsersEntity::class, 'get'],
            [RegistrationExtender::class, 'cacheUserEmail']
        );

        $this->registerChainExtension(
            [UsersEntity::class, 'update'],
            [RegistrationExtender::class, 'afterUserUpdate']
        );

        $this->registerQueueExtension(
            [AuthAdmin::class, 'fetch'],
            [BackendExtender::class, 'interceptAdminAuth']
        );

        $this->registerQueueExtension(
            [ManagerAdmin::class, 'fetch'],
            [BackendExtender::class, 'updateTfa']
        );

        $this->registerQueueExtension(
            [UserAdmin::class, 'fetch'],
            [BackendExtender::class, 'updateTfaUser']
        );

        $this->addBackendBlock('user_fields_2', 'tfa_user_block.tpl');
    }
}
