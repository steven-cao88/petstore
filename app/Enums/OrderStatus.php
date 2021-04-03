<?php

namespace App\Enums;

class OrderStatus
{
    const PLACED = 'placed';
    const APPROVED = 'approved';
    const DELIVERED = 'delivered';

    public static function getOptions()
    {
        return [
            static::PLACED,
            static::APPROVED,
            static::DELIVERED
        ];
    }
}
