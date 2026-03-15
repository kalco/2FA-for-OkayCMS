<?php

namespace Okay\Modules\RokaStudio\TwoFactorAuthentication\Entities;

use Okay\Core\Entity\Entity;

class TwoFaTokensEntity extends Entity
{
    protected static $table      = '__rs_tfa_tokens';
    protected static $tableAlias = 'rt';
    protected static $primaryKey = 'id';

    protected static $fields = [
        'id',
        'token',
        'type',
        'user_id',
        'manager_login',
        'expires_at',
        'created_at',
    ];

    protected static $defaultOrderFields = ['id DESC'];
}
