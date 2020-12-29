<?php


namespace App\ReadXYZ\Enum;


use MyCLabs\Enum\Enum;

class ReturnMethod extends Enum
{
    const ECHO_RESULTS = 'echo';
    const RETURN_RESULTS = 'return';
}
