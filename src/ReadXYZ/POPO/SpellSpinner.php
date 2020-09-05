<?php

namespace ReadXYZ\POPO;

use JsonSerializable;

class SpellSpinner implements JsonSerializable
{
    public string $prefixList = '';
    public string $vowel = '';
    public string $suffixList = '';
    
    public function __construct(string $prefix, string $vowel, string $suffix)
    {
        $this->prefixList = $prefix;
        $this->vowel = $vowel;
        $this->suffixList = $suffix;
    }
    
    public function jsonSerialize()
    {
        return [
            'prefixList' => $this->prefixList,
            'vowel' => $this->vowel,
            'suffixList' => $this->suffixList
        ];
    }
}
