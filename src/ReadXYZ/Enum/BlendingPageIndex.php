<?php


namespace App\ReadXYZ\Enum;


use MyCLabs\Enum\Enum;

/**
 * Class BlendingPageIndex
 * @package App\ReadXYZ\Enum
 * In the original blending model pages were made up of indices that contained information about the display class
 * that was used to render the page. We still have methods to convert the old blending information but will no longer be
 * needed once all of the necessary original data is converted to new formats.
 */
class BlendingPageIndex extends Enum
{
    const DISPLAY_CLASS = 0;
    const LAYOUT        = 1;
    const STYLE         = 2;
    const TAB_NAME      = 3;
    const METHOD        = 4;
    const DATA          = 5;
    const NOTE          = 6;
}
