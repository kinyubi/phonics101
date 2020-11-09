<?php


namespace App\ReadXYZ\Models;


class Log
{

    public static function debug(string $message, string $method='', string $file='', int $line=0): void
    {
        self::write('DEBUG', $message);
    }

    public static function error(string $message, string $method='', string $file='', int $line=0): void
    {
        self::write('ERROR', $message);
    }

    public static function fatal(string $message, string $method='', string $file='', int $line=0): void
    {
        self::write('FATAL', $message);
    }

    public static function info(string $message, string $method='', string $file='', int $line=0): void
    {
        self::write('INFO', $message);
    }

    private static function write(string $level, string $message, string $method='', string $file='', int $line=0): void
    {
        $loc = empty($method) ? '' : " ($method || $file line $line.";
        error_log($level . ': ' . $message . $loc );
    }
}
