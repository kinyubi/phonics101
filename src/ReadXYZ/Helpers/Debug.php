<?php

namespace App\ReadXYZ\Helpers;

use Exception;

/**
 * @copyright (c) 2020 ReadXYZ, LLC
 * @author Carl Baker (carlbaker@gmail.com)
 * @license GPL3+
 */

/**
 * Class Debug
 * cookie readxyz_debug_on set by bopp.test/debug.php
 * display debug status         - bopp.test/debug.php?status=Y
 * set debug cookie 1 hour      - bopp.test/debug.php
 * set debug cookie (hours)     - bopp.test/debug.php?hours=nn
 * set debug cookie (minutes)   - bopp.test/debug.php?minutes=nn
 *
 * @package ReadXYZ\Helpers
 */
class Debug
{
    public static function isDebug(): bool
    {
        return isset($_COOKIE['readxyz_debug_on']);
    }

    private static function getArgString($args): string
    {
        $result = '';
        if ($args) {
            $json = json_encode($args, JSON_UNESCAPED_SLASHES);
            if (strlen($json) > 90) {
                return 'args: ' . substr($json, 0, 90) . '...';
            }

            return "args: $json";
        }

        return '';
    }

    public static function getBackTrace(bool $isHtml = true): string
    {
        $EOL = ($isHtml) ? ('  <br/>' . PHP_EOL) : PHP_EOL;
        $trace = debug_backtrace();
        $message = [];
        $caller = array_shift($trace);
        $function_name = $caller['function'];
        $args = self::getArgString($caller['args'] ?? '');
        $message[] = sprintf('%s: Called from %s:%s %s', $function_name, $caller['file'], $caller['line'], $args);
        foreach ($trace as $entry_id => $entry) {
            $entry['file'] = $entry['file'] ?: '-';
            $entry['line'] = $entry['line'] ?: '-';
            $args = self::getArgString($entry['args'] ?? '');
            if (empty($entry['class'])) {
                $message[] = sprintf('%s %3s. %s() %s:%s %s', $function_name, $entry_id + 1, $entry['function'],
                    $entry['file'], $entry['line'], $args);
            } else {
                $message[] = sprintf('%s %3s. %s->%s() %s:%s %s', $function_name, $entry_id + 1, $entry['class'],
                    $entry['function'], $entry['file'], $entry['line'], $args);
            }
        }

        return implode($EOL, $message);
    }

    public static function traceBack()
    {
        $foo = debug_backtrace();
        $errString = '';
        $loopCount = 10;

        foreach ($foo as $trace) {
            // don't need to show this function
            if ('trace' == $trace['function']) {
                continue;
            }

            if (0 == $loopCount--) { // only need a few on the top, not everything back into Joomla!
                break;
            }

            $errString .= '<br />&nbsp;&nbsp;&nbsp;Called from ';

            if (array_key_exists('file', $trace)) {
                $errString .= "{$trace['file']} ";
            }

            if (array_key_exists('class', $trace)) {
                $errString .= "{$trace['class']} ";
            }

            if (array_key_exists('line', $trace)) {
                $errString .= "({$trace['line']}) ";
            }

            if (array_key_exists('function', $trace)) {
                $errString .= "{$trace['function']} ";
            }
        }

        return $errString . '<br />';
    }

    public static function logException(Exception $ex)
    {
        if (Util::isLocal()()) {
            var_dump($ex->getTraceAsString());
        }
        error_log(var_export($ex->getTraceAsString(), true));
    }
}
