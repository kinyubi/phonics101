<?php

namespace ReadXYZ\POPO;

use JsonSerializable;

class LessonPOPO implements JsonSerializable
{
    public string $lessonId = '';
    public string $lessonName = '';
    public string $lessonKey = '';     // hopefully computed value: script.lessonName
    public string $script = '';        // hopefully computed value
    public array $alternateNames; //names this lesson has been known by in the past
    public string $groupId = '';
    public string $lessonDisplayAs = '';
    public string $wordList = '';
    public string $supplementalWordList = '';
    public string $contrastList = '';
    public string $stretchList = '';
    /** @var string[] */
    public array $fluencySentences = [];
    /** @var gamePOPO[] */
    public array $games = [];
    /** @var string[] */
    public array  $tabs = [];
    public ?SpellSpinner $spinner = null;
    public string $pronounceImage = '';
    /** @var string[] */
    public array $contrastImages = [];
    public int $ordering = 0;
    public bool $visible = false;
    public string $book ='';

    public function jsonSerialize()
    {
        return [
            'lessonId' => $this->lessonId,
            'lessonName' => $this->lessonName,
            'lessonKey' => $this->lessonKey,
            'script' => $this->script,
            'alternateNames' => $this->alternateNames,
            'groupId' => $this->groupId,
            'lessonDisplayAs' => $this->lessonDisplayAs,
            'wordList' => $this->wordList,
            'supplementalWordList' => $this->supplementalWordList,
            'contrastList' => $this->contrastList,
            'stretchList' => $this->stretchList,
            'fluencySentences' => $this->fluencySentences,
            'games' => $this->games,
            'tabs' => $this->tabs,
            'spinner' => $this->spinner,
            'pronounceImage' => $this->pronounceImage,
            'contrastImage' => $this->contrastImages,
            'ordering' => $this->ordering,
            'visible' => $this->visible,
            'book' => $this->book
        ];
    }
}
