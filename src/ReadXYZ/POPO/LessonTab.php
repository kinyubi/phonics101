<?php

namespace ReadXYZ\POPO;

use JsonSerializable;

class LessonTab implements JsonSerializable
{
    public string $pageTypeId = '';
    public int $ordering = 0;
    public string $customDisplayAs = '';
    public ?HTMLPage $html = null;
    public string $resource = ''; // may be a url, CSV list, json string, word, phoneme, or word list
    public array $resourceList = []; // right now, for list of fluency sentences
    
    public function jsonSerialize()
    {
        return [
            'pageTypeId' => $this->pageTypeId,
            'ordering' => $this->ordering,
            'customDisplayAs' => $this->customDisplayAs,
            'html' => $this->html,
            'resource' => $this->resource,
            'resourceList' => $this->resourceList
        ];
    }
}
