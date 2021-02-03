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

    public function __construct(string $prefix='', string $vowel='', string $suffix='')
    {
        if (empty($prefix) && empty($vowel) && empty($suffix)) return; //caching instance
        $this->prefixList = CSV::listToArray($prefix) ?? [];
        $this->vowel = CSV::listToArray($vowel) ?? [];
        $this->suffixList = CSV::listToArray($suffix) ?? [];
    }

    public static function __set_state($array)
    {
        $spinner = new Spinner();
        $spinner->prefixList = $array['prefixList'];
        $spinner->vowel = $array['vowel'];
        $spinner->suffixList = $array['suffixList'];
        return $spinner;
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
