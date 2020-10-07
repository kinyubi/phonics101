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
    private string $script;
    private string $imageFile;

    public function __construct(string $id, string $display, string $class, bool $full = false, int $columns = 1, bool $generated = false, $review = false)
    {
        $this->tabTypeId = $id;
        $this->tabDisplayAs = $display;
        $this->tabClassName = $class;
        $this->columns = $columns;
        $this->useFullStyle = $full;
        $this->isGenerated = $generated;
        $this->reviewLesson = $review;
        $this->script = '';
        $this->imageFile = "/images/tabs/$id.png";
    }

    public function setScript(string $script): void
    {
        $this->script = $script;
    }

    public function setImage(string $filename): void
    {
        $this->imageFile = $filename;
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
            'script' => $this->script,
            'imageFile' => $this->imageFile
        ];
    }
}
