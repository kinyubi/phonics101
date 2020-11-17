<?php

namespace App\ReadXYZ\Lessons;

use App\ReadXYZ\Helpers\Util;
use JsonSerializable;

class Spinner implements JsonSerializable
{
    // make this public for easier twig access
    public array $prefixList;
    public array $vowel;
    public array $suffixList;

    public function __construct(string $prefix, string $vowel, string $suffix)
    {
        $this->prefixList = Util::csvStringToArray($prefix) ?? [];
        $this->vowel = Util::csvStringToArray($vowel) ?? [];
        $this->suffixList = Util::csvStringToArray($suffix) ?? [];
    }


    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return [
            'prefixList' => $this->prefixList,
            'vowel' => $this->vowel,
            'suffixList' => $this->suffixList
        ];
    }
}
