<?php


namespace App\ReadXYZ\Enum;


use App\ReadXYZ\Helpers\PhonicsException;
use MyCLabs\Enum\Enum;

class LogLevel extends Enum
{
    const INFO = 'info';
    const DEBUG = 'debug';
    const WARNING = 'warning';
    const ERROR = 'error';
    const FATAL = 'fatal';

    public static function getIntegral(string $value)
    {
        if (!LogLevel::isValid($value)) {
            throw new PhonicsException("$value is illegal log level.");
        }
        return array_search($value, LogLevel::values());
    }
}
