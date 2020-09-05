<?php

namespace ReadXYZ\POPO;

use JsonSerializable;

class HtmlPage implements JsonSerializable
{
    public string $tabDisplayAs = '';
    /**
     * @var string[]
     */
    public array $html = [];
    public int $ordering = 0;
    
    public function jsonSerialize()
    {
        return [
            'tabDisplayAs' => $this->tabDisplayAs,
            'html' => $this->html,
            'ordering' => $this->ordering
        ];
    }
}
