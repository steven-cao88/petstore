<?php

namespace App\Enums;

class PetStatus
{
    const AVAILABLE = 'available';
    const PENDING = 'pending';
    const SOLD = 'sold';

    public static function getOptions()
    {
        return [
            static::AVAILABLE,
            static::PENDING,
            static::SOLD
        ];
    }
}
