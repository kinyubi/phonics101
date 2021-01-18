<?php


namespace App\ReadXYZ\Enum;


use App\ReadXYZ\Helpers\PhonicsException;
use MyCLabs\Enum\Enum;

class BoolEnumTreatment extends Enum
{
    const CONVERT_TO_BOOL = 'convert_bool';
    const KEEP_AS_Y_N     = 'no_conversion';

    /**
     * We expect $enum to be ActiveType::IS_ACTIVE or ActiveType::IS_INACTIVE but we'll accept a bool value as well
     * @param $enum
     * @return bool
     */
    public static function enumToBool($enum): bool
    {
        if (is_string($enum)) return $enum = ActiveType::IS_ACTIVE;
        if (is_bool($enum)) return $enum;
        return false;
    }
}
