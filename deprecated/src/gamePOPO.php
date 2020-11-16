<?php

namespace App\ReadXYZ\POPO;

use JsonSerializable;

class gamePOPO implements JsonSerializable
{
    public string $gameTypeId = '';
    public string $url = '';

    public function __construct(string $gameTypeId, string $url)
    {
        $this->gameTypeId = $gameTypeId;
        $this->url = $url;
    }

    public function jsonSerialize()
    {
        return [
            'gameTypeId' => $this->gameTypeId,
            'url' => $this->url
        ];
    }
}
