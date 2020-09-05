<?php

namespace ReadXYZ\Lessons;

use stdClass;

class TabType
{
    private string $tabTypeId;
    private string $tabDisplayAs;
    private string $tabClassName;
    private int $columns;
    private bool $useFullStyle;
    private bool $isGenerated;
    private bool $reviewLesson;
    private bool $canRefresh;

    // stdClass fields are from GameTypePOPO:
    //      gameTypeId, gameDisplayAs, thumbNailUrl, belongsOnTab, isUniversal,ordering, universalGameUrl
    public function __construct(stdClass $other)
    {
        $this->tabTypeId = $other->tabTypeId;
        $this->tabDisplayAs = $other->tabDisplayAs;
        $this->tabClassName = $other->tabClassName;
        $this->columns = $other->columns;
        $this->useFullStyle = $other->useFullStyle;
        $this->isGenerated = $other->isGenerated;
        $this->reviewLesson = $other->reviewLesson;
        $this->canRefresh = $other->canRefresh;
    }

    // ============ GETTERS ===========

    /**
     * @return int
     */
    public function getColumns(): int
    {
        return $this->columns;
    }

    /**
     * @return string
     */
    public function getTabClassName(): string
    {
        return $this->tabClassName;
    }

    /**
     * @return string
     */
    public function getTabDisplayAs(): string
    {
        return $this->tabDisplayAs;
    }

    // =========== PROTECTED/PUBLIC METHODS

    /**
     * @return bool
     */
    public function UsesFullStyle(): bool
    {
        return $this->useFullStyle;
    }

    public function canRefresh(): bool
    {
        return $this->canRefresh;
    }

    /**
     * @return bool
     */
    public function isGenerated(): bool
    {
        return $this->isGenerated;
    }

    /**
     * @return bool
     */
    public function isReviewLesson(): bool
    {
        return $this->reviewLesson;
    }
}
