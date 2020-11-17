<?php

namespace App\ReadXYZ\POPO;

use stdClass;

class GameType
{
    public string $gameTypeId;
    public string $gameDisplayAs;
    public string $thumbNailUrl;
    public string $belongsOnTab;
    public string $cssClass;
    public bool $isUniversal;
    public string $universalGameUrl;

    /**
     * GameType constructor.
     * This is a plain old data class. The stdClass comes from the json file or the stdClass created
     * by GamesTypesData in sqlToGameTime
     * @param stdClass $stdGameType
     */
    public function __construct(stdCLass $stdGameType)
    {
        $this->gameTypeId = $stdGameType->gameTypeId;
        $this->gameDisplayAs = $stdGameType->gameDisplayAs;
        $this->thumbNailUrl = $stdGameType->thumbNailUrl;
        $this->belongsOnTab = $stdGameType->belongsOnTab;
        $this->cssClass = $stdGameType->cssClass;
        $isUniversal = $stdGameType->isUniversal;
        if (is_string($isUniversal)) {
            $this->isUniversal = $stdGameType->isUniversal == 'Y';
        } else {
            $this->isUniversal = $isUniversal;
        }
        $this->universalGameUrl = $stdGameType->universalGameUrl;
    }

}
