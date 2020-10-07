<?php

namespace ReadXYZ\Lessons;

use stdClass;

class TabType
{
    public string $tabTypeId;
    public string $tabDisplayAs;
    public string $tabClassName;
    public int $columns;
    public bool $useFullStyle;
    public bool $isGenerated;
    public bool $reviewLesson;
    public string $script;
    public string $imageFile;
    public string $html;

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
        $this->script = $other->script;
        $this->imageFile = $other->imageFile;
        $this->html = '';
    }

}
