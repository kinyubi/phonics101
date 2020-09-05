<?php

namespace ReadXYZ\Lessons;

use ReadXYZ\Helpers\Util;

class Spinner implements \JsonSerializable
{
    // make this public for easier twig access
    public ?array $prefixList;
    public ?array $vowel;
    public ?array $suffixList;

    public function __construct(string $prefix, string $vowel, string $suffix)
    {
        $this->prefixList = Util::csvStringToArray($prefix);
        $this->vowel = Util::csvStringToArray($vowel);
        $this->suffixList = Util::csvStringToArray($suffix);
    }

    /**
     * @return string[]
     */
    public function getPrefixList(): array
    {
        return $this->prefixList;
    }

    /**
     * @return string[]
     */
    public function getVowel(): array
    {
        return $this->vowel;
    }

    /**
     * @return string[]
     */
    public function getSuffixList(): array
    {
        return $this->suffixList;
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
