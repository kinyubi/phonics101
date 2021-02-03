<?php


namespace App\ReadXYZ\POPO;


use JsonSerializable;

/**
 * Class Warmup fields(lessonCode, lessonName, instructions)
 * @package App\ReadXYZ\POPO
 */
class Warmup implements JsonSerializable
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
    public function __construct(string $lessonId='', string $instructions='', array $items=[])
    {
        if (empty($lessonId)) return; //for caching
        $this->lessonId  = $lessonId;
        $this->instructions = $instructions;
        $this->warmupItems  = $items;
    }

    public function jsonSerialize()
    {
        return [
            'lessonId' => $this->lessonId,
            'instructions' => $this->instructions,
            'warmupItems' => $this->warmupItems
        ];
    }

    public static function __set_state($array)
    {
        $warmup = new Warmup();
        $warmup->lessonId = $array['lessonId'];
        $warmup->instructions = $array['instructions'];
        $warmup->warmupItems = $array['warmupItems'];
        return $warmup;
    }
}
