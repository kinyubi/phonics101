<?php


namespace App\ReadXYZ\Enum;


use MyCLabs\Enum\Enum;

class MasteryLevel extends Enum
{
    const NONE = 'none';
    const ADVANCING = 'advancing';
    const MASTERED = 'mastered';

    public static function toIntegral(string $sqlValue)
    {
        $array = ['none' => 0, 'advancing' => 1, 'mastered' => 2];
        return $array[$sqlValue] ?? 0;
    }

    public static function toSqlValue(int $integral): string
    {
        $array = ['none', 'advancing', 'mastered'];
        return $array[clamp($integral, 0,2)];
    }

}
