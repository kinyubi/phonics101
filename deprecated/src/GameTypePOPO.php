<?php

namespace App\ReadXYZ\POPO;

use JsonSerializable;
use App\ReadXYZ\Helpers\Location;

class GameTypePOPO implements JsonSerializable
{
    public string $gameTypeId;
    public string $gameDisplayAs;
    public string $thumbNailUrl;
    public string $belongsOnTab;
    public int $ordering;
    public bool $isUniversal;
    public string $universalGameUrl;

    public function __construct(string $id, string $display, string $tab, int $order, bool $builtIn = false)
    {
        $this->gameTypeId = $id;
        $this->gameDisplayAs = $display;
        $this->belongsOnTab = $tab;
        $this->isUniversal = $builtIn;
        $this->ordering = $order;
        $this->universalGameUrl = '';
        $this->thumbNailUrl = "/images/sidebar/$id.jpg";
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'gameTypeId' => $this->gameTypeId,
            'gameDisplayAs' => $this->gameDisplayAs,
            'thumbNailUrl' => $this->thumbNailUrl,
            'belongsOnTab' => $this->belongsOnTab,
            'isUniversal' => $this->isUniversal,
            'ordering' => $this->ordering,
            'universalGameUrl' => $this->universalGameUrl
        ];
    }
}
