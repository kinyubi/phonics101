<?php

namespace App\ReadXYZ\Lessons;

use App\ReadXYZ\CSV\CSV;
use JsonSerializable;

class Spinner implements JsonSerializable
{
    // make this public for easier twig access
    public array $prefixList;
    public array $vowel;
    public array $suffixList;

    public function __construct(string $prefix, string $vowel, string $suffix)
    {
        $this->prefixList = CSV::listToArray($prefix) ?? [];
        $this->vowel = CSV::listToArray($vowel) ?? [];
        $this->suffixList = CSV::listToArray($suffix) ?? [];
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
