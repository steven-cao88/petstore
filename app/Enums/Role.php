<?php

namespace App\Enums;

class Role
{
    const STORE_ADMIN = 'store_admin';
    const NORMAL_USER = 'normal_user';

    public static function getOptions()
    {
        return [
            static::STORE_ADMIN,
            static::NORMAL_USER
        ];
    }
}
