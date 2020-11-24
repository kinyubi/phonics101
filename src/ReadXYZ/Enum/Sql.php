<?php


namespace App\ReadXYZ\Enum;


use MyCLabs\Enum\Enum;

class Sql extends Enum
{
    const THROW_ON_NOT_FOUND = true;
    const NOT_FOUND_IS_VALID = false;

    const FOREIGN_KEY_CHECKS_ON     = 1;
    const FOREIGN_KEY_CHECKS_OFF    = 0;

    const ACTIVE    = 'Y';
    const INACTIVE  = 'N';

    const READXYZ0_1    = 0;
    const READXYZ1_1    = 1;

}
