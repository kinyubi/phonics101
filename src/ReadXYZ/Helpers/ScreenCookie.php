<?php


namespace App\ReadXYZ\Helpers;


use App\ReadXYZ\Models\Session;

class ScreenCookie
{
    public const PHONE_DEVICE = 'phone';
    public const TABLET_DEVICE = 'tablet';
    public const COMPUTER_DEVICE = 'computer';
    public const UNKNOWN_DEVICE = 'unknown';

    private static ScreenCookie $instance;

    private function __construct() {Session::sessionContinue();}

    public static function getInstance(): ScreenCookie
    {
        if ( ! isset(self::$instance)) {
            self::$instance = new ScreenCookie();
        }

        return self::$instance;
    }

    public function getScreenInfo()
    {
        if (!isset($_COOKIE['readxyz_screen'])) {
            return self::makeObject(0,0,0, 0, self::UNKNOWN_DEVICE);
        }
        $dims = explode(',', $_COOKIE['readxyz_screen']);
        $type = self::getDeviceType($dims[0], $dims[1]);
        $screenInfo = self::makeObject(intval($dims[0]), intval($dims[1]), intval($dims[2]), intval($dims[3]), $type);
        Session::sessionContinue();
        $_SESSION['SCREEN_DIM'] = $screenInfo;
        return $screenInfo;
    }

    public function getIdealPartWidth(int $parts, float $padPercent=.9)
    {
        $screenInfo = $this->getScreenInfo();
        if ($screenInfo->deviceType == 'phone') {
            $fullWidth = $screenInfo->screenWidth;
        } else {
            $fullWidth = min($screenInfo->windowWidth, 800);
        }
        return round(($padPercent * $fullWidth) / $parts);
    }

    public function getDeviceType(int $width=0, int $height=0): string
    {
        if ($width == 0) {
            $screenInfo = $this->getScreenInfo();
            $width = $screenInfo->screenWidth;
            $height = $screenInfo->screenHeight;
        }
        if ($width < 500 || $height < 500) {
            $type = self::PHONE_DEVICE;
        } elseif ($width < 900 || $height < 900) {
            $type = self::TABLET_DEVICE;
        } else {
            $type = self::COMPUTER_DEVICE;
        }
        return $type;
    }

    public function isScreenSizeSmall(): bool
    {
        $sizes = self::getScreenInfo();
        return ($sizes->screenWidth < 800 || $sizes->screenHeight < 800);
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
