<?php

namespace App\ReadXYZ\POPO;

use LogicException;
use stdClass;

class GameType
{
    public string $gameTypeId;
    public string $gameDisplayAs;
    public string $thumbNailUrl;
    public string $belongsOnTab;
    public string $cssClass;
    public bool   $isUniversal;
    public string $universalGameUrl;
    public bool   $active;

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
        $this->isUniversal = $this->sqlEnumToBool($stdGameType->isUniversal);

        $this->universalGameUrl = $stdGameType->universalGameUrl;
        $this->active = $this->sqlEnumToBool($stdGameType->active ?? true);
    }

    /**
     * @param string|bool $value Y/N or true/false
     * @return bool
     */
    private function sqlEnumToBool($value)
    {
        if (is_string($value)) return $value = 'Y';
        if (is_bool($value)) return $value;
        throw new LogicException('Value was neither string nor bool.');
    }

}
