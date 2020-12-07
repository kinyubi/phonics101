<?php


namespace App\ReadXYZ\Enum;


use MyCLabs\Enum\Enum;

class Sql extends Enum
{
    const FOREIGN_KEY_CHECKS_ON     = 1;
    const FOREIGN_KEY_CHECKS_OFF    = 0;

    const ACTIVE    = 'Y';
    const INACTIVE  = 'N';

    const READXYZ0_1    = 'readxyz0_1';
    const READXYZ1_1    = 'readxyz1_1';

}
