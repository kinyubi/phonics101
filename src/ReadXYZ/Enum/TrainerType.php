<?php


namespace App\ReadXYZ\Enum;


use MyCLabs\Enum\Enum;

class TrainerType extends Enum
{
    private static array $permissions = [

    ];

    const RESERVE   = 'reserve';
    const PARENT    = 'parent';
    const TRAINER   = 'trainer';
    const STAFF     = 'staff';
    const ADMIN     = 'admin';
    const TEST      = 'test';
}
