<?php

use ReadXYZ\Helpers\Debug;

function __getPhonicsVendorDir()
{
    // The web site has a parent path of public_html and the local site does not
    $publicSite = false !== strpos(__DIR__, 'public_html');

    // on the local site, the vendor dir is at C:\laragon\www\bopp\ which has a subdirectory of public
    // on the web site, the vendor dir is at ~/public_html/readxyz.org which has a subdirectory of phonics
    // by searching for the subdirectory, we can get the substring containing the vendor directory
    $subDir = $publicSite ? 'phonics' : 'public';
    $pos = strpos(__DIR__, $subDir);

    // if we found the parent directory we need to make sure it has a vendor subdirectory
    if (false !== $pos) {
        $vendorParentDir = substr(__DIR__, 0, $pos);
    } else {
        $vendorParentDir = false;
    }
    if ((false !== $vendorParentDir) && (file_exists($vendorParentDir . 'vendor'))) {
        return $vendorParentDir . 'vendor';
    } else {
        return false;
    }
}

/**
 * This error handler is used when the server is not localhost.
 *
 * @see https://www.php.net/manual/en/function.set-error-handler.php
 *
 * @param int    $errno      the error number
 * @param string $errMessage the error message
 * @param string $errFile    the file the error occured in
 * @param int    $errLine    the line number the error occured in
 *
 * @return bool
 */
function userErrorHandler($errno, $errMessage, $errFile, $errLine)
{
    // echo "userErrorHandler($errno, $errMessage, $filename, $linenum, $vars)";
    // I am plagued with multiple hits on the same error, filter them here
    $lastError = "$errno/$errMessage/$errFile/$errLine";
    if (isset($GLOBALS['lastError']) and $GLOBALS['lastError'] == $lastError) {
        return true;
    }

    $GLOBALS['lastError'] = $lastError;

    $err = "$errMessage  at  $errFile($errLine)" . Debug::traceBack();
    error_log($err);
    if (method_exists('Debug', 'writeSystemLog')) {
        // might happen that the error fires before utilities loaded
        Debug::writeSystemLog('ERROR', $err);
        assert(false, $err);
    } else {
        echo "ERROR $err<br>";
    }
    exit();
}

/**
 * global fatal error handler.
 */
function fatal_handler(): void
{
    $error = error_get_last();

    if (null !== $error) {
        $errNumber = $error['type'];
        $errFile = $error['file'];
        $errLine = $error['line'];
        $errString = $error['message'];

        echo "$errNumber, $errString, $errFile, $errLine";
    } else {
        echo 'CORE ERROR at unknown location. Shutting down, ';
    }
    exit('Fatal error');
}

function not(bool $expression): bool
{
    return !$expression;
}

function areEqual(bool $expression1, bool $expression2): bool
{
    return $expression1 == $expression2;
}

function areNotEqual(bool $expression1, bool $expression2): bool
{
    return $expression1 != $expression2;
}

/**
 * PHP equivalent of Python's __name__ == '__main__'.
 *
 * @see https://stackoverflow.com/questions/2413991/php-equivalent-of-pythons-name-main
 *
 * @return bool true if we are running the PHP standalone rather than part of a website
 */
function runningStandalone(): bool
{
    return !count(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
}

/**
 * tests if the passed variable is a populated associative array.
 *
 * @param $array
 *
 * @return bool returns true if the passed variable is a populated associative array
 */
function isAssociative($array): bool
{
    try {
        foreach (array_keys($array) as $k => $v) {
            if ($k !== $v) {
                return true;
            }
        }

        return false;
    } catch (Throwable $ex) {
        return false;
    }
}

/**
 * If an array entry exists, append to it, otherwise  set the value of it.
 *
 * @param array  $array the array of interest
 * @param string $key   the key in the array we want to use
 * @param mixed  $value the value we want added or appended
 */
function addAssociative(array &$array, string $key, $value): void
{
    if (isset($array[$key])) {
        $array[$key] .= $value;
    } else {
        $array[$key] = $value;
    }
}
