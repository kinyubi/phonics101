<?php


namespace App\ReadXYZ\POPO;


use App\ReadXYZ\Helpers\Util;

class Warmup
{

    public string $lessonName;
    public string $instructions;

    /**
     * @var WarmupItem[]
     */
    public array $warmupItems = [];

    /**
     * Warmup constructor.
     * @param string $lessonName
     * @param string $instructions
     * @param WarmupItem[] $items
     */
    public function __construct(string $lessonName, string $instructions, array $items)
    {
        $this->lessonName = $lessonName;
        $this->instructions = $instructions;
        $this->warmupItems = $items;
    }
}
