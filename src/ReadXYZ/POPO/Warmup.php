<?php


namespace App\ReadXYZ\POPO;


/**
 * Class Warmup fields(lessonCode, lessonName, instructions)
 * @package App\ReadXYZ\POPO
 */
class Warmup
{

    public string $lessonId;
    public string $instructions;

    /**
     * @var WarmupItem[]
     */
    public array $warmupItems = [];

    /**
     * Warmup constructor.
     * @param string $lessonId
     * @param string $instructions
     * @param WarmupItem[] $items
     */
    public function __construct(string $lessonId, string $instructions, array $items)
    {
        $this->lessonId  = $lessonId;
        $this->instructions = $instructions;
        $this->warmupItems  = $items;
    }
}
