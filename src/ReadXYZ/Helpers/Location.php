<?php


namespace ReadXYZ\Helpers;


use App\ReadXYZ\Helpers\ScreenCookie;
use ReadXYZ\Models\Cookie;

class Location
{
    public const URL = 'url';
    public const FILE = 'file';
    public const RANDOM = 'random';

    public const TIC_TAC_TOE_ANIMAL_DIR = '/images/animals/named/';
    public const TIC_TAC_TOE_GRAYED_ANIMAL_DIR = '/images/animals/gray/';
    public const BDP_DIR = '/images/bdp/';
    public const FAVICON_DIR = '/images/favicons/';
    public const LESSON_LIST_ICONS_DIR = '/images/lessonlist/';
    public const PRONOUNCE_IMAGES_DIR = '/images/pronounce/';
    public const SIDEBAR_IMAGES_DIR = '/images/sidebar/';
    public const LESSON_TAB_IMAGES_DIR = '/images/tabs/';
    public const SOUND_BOX_GAME = '/sound-box.php';
    public const TIC_TAC_TOE_GAME = '/tictactoe/tictac.php';
    public const DICE_GAME = '/dice/rolldice-orig.php';


    public static function getGameThumbnail(string $gameTypeId): string
    {
        $isSmall = ScreenCookie::isScreenSizeSmall();
        if ($isSmall) {
            return self::SIDEBAR_IMAGES_DIR . $gameTypeId . '_sm.png';
        } else {
            return self::SIDEBAR_IMAGES_DIR . $gameTypeId . '.jpg';
        }
    }

    public static function getTicTacToeAnimal(string $name): string
    {
        return self::TIC_TAC_TOE_ANIMAL_DIR . $name . '.jpg';
    }

    public static function getPronounceImage(string $imageName): string
    {
        if (empty($imageName)) {
            return '';
        }
        if (Util::contains($imageName, 'b-d-p')) {
            return self::BDP_DIR . 'b-d-p_poster.jpg';
        } else {
            return self::PRONOUNCE_IMAGES_DIR . $imageName;
        }
    }

    public static function getPronounceImageThumb(string $imageName): string
    {
        if (empty($imageName)) {
            return '';
        }
        if (Util::contains($imageName, 'b-d-p')) {
            return self::BDP_DIR . 'thumb_b-d-p_poster.jpg';
        } else {
            return self::PRONOUNCE_IMAGES_DIR . 'thumb_' . $imageName;
        }
    }
}
