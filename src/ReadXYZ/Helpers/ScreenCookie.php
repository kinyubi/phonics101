<?php


namespace App\ReadXYZ\Helpers;


use App\ReadXYZ\Models\Session;

class ScreenCookie
{
    public const PHONE_DEVICE = 'phone';
    public const TABLET_DEVICE = 'tablet';
    public const COMPUTER_DEVICE = 'computer';
    public const UNKNOWN_DEVICE = 'unknown';



    public static function getScreenInfo()
    {
        Session::sessionContinue();
        if (!isset($_COOKIE['readxyz_screen'])) {
            return self::makeObject(0,0,0, 0, self::UNKNOWN_DEVICE);
        }
        $dims = explode(',', $_COOKIE['readxyz_screen']);
        $type = self::getDeviceType($dims[0], $dims[1]);
        return self::makeObject(intval($dims[0]), intval($dims[1]), intval($dims[2]), intval($dims[3]), $type);
    }

    public static function getDeviceType(int $width, int $height): string
    {
        if ($width == 0) {
            $type = self::UNKNOWN_DEVICE;
        } elseif ($width < 500 || $height < 500) {
            $type = self::PHONE_DEVICE;
        } elseif ($width < 900 || $height < 900) {
            $type = self::TABLET_DEVICE;
        } else {
            $type = self::COMPUTER_DEVICE;
        }
        return $type;
    }

    public static function isScreenSizeSmall(): bool
    {
        $sizes = self::getScreenInfo();
        return ($sizes->screenWidth < 800);
    }

    private static function makeObject(int $screenWidth, int $screenHeight, int $windowWidth, int $windowHeight, string $type)
    {
        return (object) [
            'screenWidth' => $screenWidth,
            'screenHeight' => $screenHeight,
            'windowWidth' => $windowWidth,
            'windowHeight' => $windowHeight,
            'deviceType' => $type
        ];
    }
}
