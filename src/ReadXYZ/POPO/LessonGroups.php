<?php

namespace ReadXYZ\POPO;

use JsonMapper\JsonMapper;
use JsonSerializable;
use ReadXYZ\Helpers\Util;

class LessonGroups implements JsonSerializable
{
    public string $groupId = '';
    public string $groupDisplayAs = '';
    public int $ordering = 0;
    public string $script = '';
    /**
     * @var LessonPOPO[]
     */
    public array $lessons;

    public function setLessons(): void
    {
        $decodedJson = json_decode(file_get_contents(Util::getReadXyzSourcePath('resources/lessonGroups.json')));
        $mapper = new JsonMapper();
        $allLessons = [];
        $this->lessons = $mapper->mapArray($decodedJson, $allLessons, 'Lesson');
        foreach ($allLessons as $lesson) {
            if (($this->groupId == $lesson->groupId) || ($this->groupDisplayAs == $lesson->groupId)) {
                $this->lessons[] = $lesson;
            }
        }
    }

    public function jsonSerialize()
    {
        return [
            'groupId' => $this->groupId,
            'groupDisplayAs' => $this->groupDisplayAs,
            'ordering' => $this->ordering,
            'script' => $this->script,
            'lessons' => $this->lessons
        ];
    }
}
