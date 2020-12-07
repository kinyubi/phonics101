<?php


namespace App\ReadXYZ\Enum;


use App\ReadXYZ\Helpers\PhonicsException;
use MyCLabs\Enum\Enum;

class Throwable extends Enum
{
    const THROW_ON_NOT_FOUND = 'throw';
    const NOT_FOUND_IS_VALID = 'no_throw';

}
