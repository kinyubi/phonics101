<?php

namespace ReadXYZ\Lessons;

use JsonSerializable;
use ReadXYZ\Helpers\Util;

class Game implements JsonSerializable
{
    public string $gameTypeId;
    public string $gameTitle;
    public string $thumbRef;
    public string $tabName;
    public string $url;
    public string $cssClass;
    public int $ordering;

    public function __construct(string $id, string $title, string $thumb, string $tab, string $url, int $order = 0)
    {
        $this->gameTypeId = $id;
        $this->gameTitle = $title;
        $this->thumbRef = $thumb;
        $this->tabName = $tab;
        $this->ordering = $order;
        $this->url = $url;
        if (Util::contains($thumb, 'sound-box')) {
            $this->cssClass = 'sound-box';
        } elseif (Util::contains($thumb, 'tic-tac-toe')) {
            $this->cssClass = 'tic-tac-toe';
        } elseif (Util::contains($thumb, 'advanced-spell')) {
            $this->cssClass = 'advanced-spell';
        } else {
            $this->cssClass = 'games';
        }
    }

    // =========== PROTECTED/PUBLIC METHODS

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'gameTypeId' => $this->gameTypeId,
            'gameTitle' => $this->gameTitle,
            'thumbRef' => $this->thumbRef,
            'tabName' => $this->tabName,
            'url' => $this->url,
            'ordering' => $this->ordering
        ];
    }
}
