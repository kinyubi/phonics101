<?php


namespace App\ReadXYZ\Enum;


use MyCLabs\Enum\Enum;

class TabTypeId extends Enum
{
    const STORY_TIME = 'book';
    const READING    = 'fluency';
    const STRETCH    = 'intro';
    const MASTERY    = 'mastery';
    const PRACTICE   = 'practice';
    const CHAIN      = 'spell';
    const TEST       = 'test';
    const WARMUP     = 'warmup';
    const WRITE      = 'write';

}
