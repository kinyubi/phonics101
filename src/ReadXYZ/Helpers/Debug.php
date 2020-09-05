<?php

namespace ReadXYZ\Helpers;

use Exception;
use ReadXYZ\Database\SystemLog;
use ReadXYZ\Models\Document;

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
            $result .= ' args: ';
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

    public static function printNice(string $tabName, $data): void
    {
        if (self::isDebug()) {
            Document::getInstance()->writeTabDebug($tabName, self::printNiceHelper($data));
        }
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

    private static function printNiceHelper($data, int $max_level = 10, array $stack = [], string $HTML = ''): string
    {
        $backtrace = debug_backtrace();   // if no title, then show who called us

        if (isset($backtrace[1]['class'])) {
            $HTML .= "<hr /><h1>class {$backtrace[1]['class']}, " .
                "function {$backtrace[1]['function']}() " .
                "(line:{$backtrace[1]['line']})</h1>";
        }

        if (is_string($data)) {
            $HTML .= htmlentities($data) . '<br>';

            return $HTML;
        }

        if (is_array($data) || is_object($data)) {
            if (in_array($data, $stack, true)) {
                $document = Document::getInstance();        // it's a recursive function, but this is a static so should work
                $document->appendToErrorMessage("<div style='color: red;'>RECURSION</div>");
                $HTML .= "<hr /><h1>class {$backtrace[1]['class']}, " .
                    "function {$backtrace[1]['function']}() " .
                    "(line:{$backtrace[1]['line']})</h1>";

                return $HTML;
            }
            if ($max_level < 1) {
                $document = Document::getInstance();        // it's a recursive function, but this is a static so should work
                $document->appendToErrorMessage("<div style='color: red;'>max stack level of $max_level exceeded</div>");
                $HTML .= "<div style='color: red;'>MAX STACK LEVEL OF $max_level EXCEEDED</div>";

                return $HTML;
            }

            $print_nice_stack[] = &$data;
            --$max_level;
            $HTML .= "<table style='border-collapse: collapse; border-spacing: 0 padding: 3px; border: 1px;  width:100%'>";
            if (is_array($data)) {
                $HTML .= '<tr><td colspan=2 style="background-color:#333333; color: white;">';
                $HTML .= '<strong>ARRAY</strong></td></tr>';
            } else {
                $HTML .= '<tr><td colspan=2 style="background-color:#333333; color:white;"><strong>';
                $HTML .= 'OBJECT Type: ' . get_class($data) . '</strong></td></tr>';
            }
            $color = 0;
            foreach ($data as $k => $v) {
                if ($max_level % 2) {
                    $rgb = ($color++ % 2) ? '#888888' : '#BBBBBB';
                } else {
                    $rgb = ($color++ % 2) ? '#8888BB' : '#BBBBFF';
                }
                $HTML .= '<tr><td style="vertical-align:top; width:40px;background-color:' . $rgb . ';">';
                $HTML .= '<strong>' . $k . '</strong></td><td>';
                $HTML .= self::printNiceHelper($v, $max_level, $print_nice_stack);

                $HTML .= '</td></tr>';
            }

            $HTML .= '</table>';

            return $HTML;
        }
        if (null === $data) {
            $HTML .= "<div style='color:green'>NULL</div>";
        } elseif (0 === $data) {
            $HTML .= '0';
        } elseif (true === $data) {
            $HTML .= "<div style='color:green'>TRUE</div>";
        } elseif (false === $data) {
            $HTML .= "<div style='color:green'>FALSE</div>";
        } elseif ('' === $data) {
            $HTML .= "<div style='color:green'>EMPTY STRING</div>";
        } else {
            $HTML .= str_replace("\n", "<div style='color:red; font-weight: bold'>*</div><br>\n", $data);
        }

        return $HTML;
    }

    public static function writeSystemLog(string $action, string $comment): void
    {
        SystemLog::getInstance()->write($action, $comment . PHP_EOL . self::getBackTrace());
    }

    public static function logException(Exception $ex)
    {
        if (Util::isLocal()()) {
            var_dump($ex->getTraceAsString());
        }
        error_log(var_export($ex->getTraceAsString(), true));
    }
}
