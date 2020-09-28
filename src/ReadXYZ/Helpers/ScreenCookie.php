<?php


namespace App\ReadXYZ\Helpers;


class ScreenCookie
{
    public const PHONE_DEVICE = 'phone';
    public const TABLET_DEVICE = 'tablet';
    public const COMPUTER_DEVICE = 'computer';
    public const UNKNOWN_DEVICE = 'unknown';



    public static function getDimensions()
    {
        if (!isset($_SESSION)) { //You can't start a session that's already going
            session_start(); // continues the existing session
        }
        if (!isset($_COOKIE['readxyz_screen'])) {
            return self::makeObject(0,0,0, 0);
        }
        $dims = explode(',', $_COOKIE['readxyz_screen']);
        return self::makeObject(intval($dims[0]), intval($dims[1]), intval($dims[2]), intval($dims[3]));
    }

    public static function getDeviceType(): string
    {
        $sizes = self::getDimensions();
        $screenWidth = $sizes->screenWidth;
        $screenHeight = $sizes->screenHeight;
        if ($screenWidth == 0) {
            return self::UNKNOWN_DEVICE;
        } elseif ($screenWidth < 500 || $screenHeight < 500) {
            return self::PHONE_DEVICE;
        } elseif ($screenWidth < 1000 || $screenHeight < 1000) {
            return self::TABLET_DEVICE;
        } else {
            return self::COMPUTER_DEVICE;
        }
    }

    public static function useSmallIcons(): bool
    {
        $sizes = self::getDimensions();
        return ($sizes->windowWidth < 950);
    }

    private static function makeObject(int $screenWidth, int $screenHeight, int $windowWidth, int $windowHeight)
    {
        return (object) [
            'screenWidth' => $screenWidth,
            'screenHeight' => $screenHeight,
            'windowWidth' => $windowWidth,
            'windowHeight' => $windowHeight
        ];
    }
}
