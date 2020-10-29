<?php


namespace App\ReadXYZ\Models;


use stdClass;

class Session
{
    /**
     * We never destroy the session so we are just continuing the existing one. Every page that
     * gets rendered needs to have a session_start. Without it, $_SESSION variables aren't visible.
     */
    public static function sessionContinue(): void
    {
        if (self::testingInProgress()) return;
        if (!isset($_SESSION)) { //You can't start a session that's already going
            session_start(); // continues the existing session
        }
    }

    /**
     * Allows unit tests to not get messed up because there are no session variables
     * @return bool true if Testing define is set, otherwise false
     */
    public static function testingInProgress(): bool
    {
        return defined('TESTING_IN_PROGRESS');
    }

    public static function persistUserSession(string $userId, stdClass $session): void
    {
        $_SESSION[$userId] = $session;
    }


}
