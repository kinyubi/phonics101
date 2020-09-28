<?php


namespace ReadXYZ\POPO;


use ReadXYZ\Helpers\Util;

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
        $this->lessonName = Util::addSoundClass($lessonName);
        $this->instructions = Util::addSoundClass($instructions);
        $this->warmupItems = $items;
    }
}
