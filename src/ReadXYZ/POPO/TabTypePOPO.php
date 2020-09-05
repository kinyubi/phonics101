<?php

namespace ReadXYZ\POPO;

class TabTypePOPO implements \JsonSerializable
{
    private string $tabTypeId;
    private string $tabDisplayAs;
    private string $tabClassName;
    private int $columns;
    private bool $useFullStyle;
    private bool $isGenerated;
    private bool $reviewLesson;
    private bool $canRefresh;

    public function __construct(string $id, string $display, string $class, bool $full = false, int $columns = 1, bool $generated = false, $review = false, $canRefresh = false)
    {
        $this->tabTypeId = $id;
        $this->tabDisplayAs = $display;
        $this->tabClassName = $class;
        $this->columns = $columns;
        $this->useFullStyle = $full;
        $this->isGenerated = $generated;
        $this->reviewLesson = $review;
        $this->canRefresh = $canRefresh;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'tabTypeId' => $this->tabTypeId,
            'tabDisplayAs' => $this->tabDisplayAs,
            'tabClassName' => $this->tabClassName,
            'columns' => $this->columns,
            'useFullStyle' => $this->useFullStyle,
            'isGenerated' => $this->isGenerated,
            'reviewLesson' => $this->reviewLesson,
            'canRefresh' => $this->canRefresh
        ];
    }
}
