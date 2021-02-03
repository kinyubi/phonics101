<?php


namespace App\ReadXYZ\Models;


use App\ReadXYZ\Data\SystemLogData;
use App\ReadXYZ\Enum\LogLevel;
use App\ReadXYZ\Helpers\Util;

/**
 * Class Log logs messages to
 * @package App\ReadXYZ\Models
 */
class Log
{

    public static function debug(string $message, string $method='', string $file='', int $line=0): void
    {
        self::write('DEBUG', $message, $method, $file, $line);
    }

    public static function error(string $message, string $method='', string $file='', int $line=0): void
    {
        self::write('ERROR', $message, $method, $file, $line);
    }

    public static function fatal(string $message, string $method='', string $file='', int $line=0): void
    {
        self::write('FATAL', $message, $method, $file, $line);
    }

    public static function info(string $message, string $method='', string $file='', int $line=0): void
    {
        self::write('INFO', $message, $method, $file, $line);
    }

    public static function warning(string $message, string $method='', string $file='', int $line=0): void
    {
        self::write('WARNING', $message, $method, $file, $line);
    }

    public static function elapsedTime(string $message, float $mSecs)
    {
        $stamp = Util::getHumanReadableDateTime();
        $file = Util::getProjectPath('docs/elapsedTimes.log');
        $fullMessage = sprintf("%s Elapsed time: %.4f %s\n", $stamp, $mSecs, $message);
        error_log($fullMessage, 3, $file);
    }


    private static function write(string $level, string $message, string $method='', string $file='', int $line=0): void
    {
        $loc = empty($method) ? '' : " ($method || $file line $line).";
        error_log($level . ': ' . $message . $loc );
        $lcLevel = strtolower($level);
        if (!LogLevel::isValid($lcLevel)) $lcLevel = LogLevel::FATAL;
        (new SystemLogData())->add($lcLevel, '',$message);
    }

    private static function writeTime(string $message, float $msecs)
    {

    }
}
