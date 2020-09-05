<?php

namespace ReadXYZ\Lessons;

use stdClass;

class GameType
{
    private string $gameTypeId;
    private string $gameDisplayAs;
    private string $thumbNailUrl;
    private string $belongsOnTab;
    private int $ordering;
    private bool $isUniversal;
    private string $universalGameUrl;

    public function __construct(stdCLass $popoGameType)
    {
        $this->gameTypeId = $popoGameType->gameTypeId;
        $this->gameDisplayAs = $popoGameType->gameDisplayAs;
        $this->thumbNailUrl = $popoGameType->thumbNailUrl;
        $this->belongsOnTab = $popoGameType->belongsOnTab;
        $this->isUniversal = $popoGameType->isUniversal;
        $this->ordering = $popoGameType->ordering;
        $this->universalGameUrl = $popoGameType->universalGameUrl;
    }

    public function setUniversalGameUrl(string $url): void
    {
        $this->universalGameUrl = $url;
    }

    /**
     * @return string
     */
    public function getGameTypeId(): string
    {
        return $this->gameTypeId;
    }

    /**
     * @return string
     */
    public function getGameDisplayAs(): string
    {
        return $this->gameDisplayAs;
    }

    /**
     * @return string
     */
    public function getThumbNailUrl(): string
    {
        return $this->thumbNailUrl;
    }

    /**
     * @return string
     */
    public function getBelongsOnTab(): string
    {
        return $this->belongsOnTab;
    }

    /**
     * @return int
     */
    public function getOrdering(): int
    {
        return $this->ordering;
    }

    /**
     * @return bool
     */
    public function isUniversal(): bool
    {
        return $this->isUniversal;
    }

    /**
     * @return string
     */
    public function getUniversalGameUrl(): string
    {
        return $this->universalGameUrl;
    }
}
