<?php


namespace App\ReadXYZ\Enum;


use App\ReadXYZ\Helpers\PhonicsException;
use MyCLabs\Enum\Enum;

class BoolEnumTreatment extends Enum
{
    const CONVERT_TO_BOOL = 'convert_bool';
    const KEEP_AS_Y_N     = 'no_conversion';


}
