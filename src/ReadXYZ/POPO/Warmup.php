<?php


namespace App\ReadXYZ\POPO;


use App\ReadXYZ\JSON\LessonsJson;

/**
 * Class Warmup fields(lessonCode, lessonName, instructions)
 * @package App\ReadXYZ\POPO
 */
class Warmup
{

    public string $lessonCode;
    public string $lessonName;
    public string $instructions;

    /**
     * @var WarmupItem[]
     */
    public array $warmupItems = [];

    /**
     * Warmup constructor.
     * @param string $lesson
     * @param string $instructions
     * @param WarmupItem[] $items
     */
    public function __construct(string $lesson, string $instructions, array $items)
    {
        $lessons            = LessonsJson::getInstance();
        $lessonName         = $lessons->getLessonName($lesson);
        $this->lessonCode   = $lessons->getLessonCode($lessonName);
        $this->lessonName   = $lessonName;
        $this->instructions = $instructions;
        $this->warmupItems  = $items;
    }
}
