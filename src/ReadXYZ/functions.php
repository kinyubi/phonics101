<?php

/**
 * If an array entry exists, append to it, otherwise  set the value of it.
 *
 * @param array $array the array of interest
 * @param string $key the key in the array we want to use
 * @param mixed $value the value we want added or appended
 */
function addAssociative(array &$array, string $key, $value): void
{
    if (isset($array[$key])) {
        $array[$key] .= $value;
    } else {
        $array[$key] = $value;
    }
}

function clamp($current, $min, $max)
{
    return max($min, min($max, $current));
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

function not(bool $expression): bool
{
    return ! $expression;
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
    return ! count(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
}

