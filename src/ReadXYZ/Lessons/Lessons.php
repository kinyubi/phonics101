<?php

namespace App\ReadXYZ\Lessons;

use App\ReadXYZ\Data\GroupData;
use App\ReadXYZ\Data\LessonsData;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\POPO\Lesson;
use InvalidArgumentException;

/**
 * Class Lessons
 * This lesson will render lessons from unifiedLessons.json.
 *
 * @package ReadXYZ\Lessons
 */
class Lessons
{
    private static Lessons $instance;

    /** @var Lesson[] */
    private array $lessons = [];

    /** @var string[] */
    private array $groupNames;
    /** @var array structure is accordion[groupName][lessonName] => masteryLevel (0-none, 1-advancing, 2-mastered) */
    private array $accordionTemplate = []; // used as a starting point for mastery which is applied in student lesson
    private array $alternateNameMap = [];
    private array $lessonNames = [];
    private array $displayAs = [];


    private array $maxLengths;

    private function __construct()
    {
        $groupData = new GroupData();
        $lessonsData = new LessonsData();

        $this->groupNames = $groupData->getAllActiveGroups();
        foreach ($this->groupNames as $groupName => $groupDisplayAs) {
            $this->accordionTemplate[$groupName] = [];
            $this->displayAs[$groupName] = $groupDisplayAs;
        }
        $lessonsWithGroupInfo = $lessonsData->getLessonsWithGroupFields();

        $ordinal = 0;
        foreach ($lessonsWithGroupInfo as $lessonInfo) {
            $lessonCode = $lessonInfo->lessonCode;
            $lessonName = $lessonInfo->lessonName;
            $lessonDisplay = $lessonInfo->lessonDisplayAs;
            $lesson = $lessonsData->get($lessonName);
            $lesson->ordering = $ordinal++;
            $this->lessons[$lessonCode] = $lesson;

            //adding every conceivable alias
            $this->alternateNameMap[$lessonCode] = $lessonCode;
            $this->alternateNameMap[$lessonName] = $lessonCode;
            $this->alternateNameMap[$lessonDisplay] = $lessonCode;

            $this->displayAs[$lessonCode] = $lessonDisplay;
            $this->displayAs[$lessonName] = $lessonDisplay;
            $this->displayAs[$lessonDisplay] = $lessonDisplay;

            $groupName = $this->getGroupName($lesson->groupName);
            $this->accordionTemplate[$groupName][$lesson->lessonName] = 0;
            $this->lessonNames[] = $lessonName;


        }

    }

// ======================== STATIC METHODS =====================

    /**
     * passes back the singleton instance for this class.
     *
     * @return Lessons
     */
    public static function getInstance()
    {
        if ( ! isset(self::$instance)) {
            self::$instance = new Lessons();
        }

        return self::$instance;
    }

// ======================== PUBLIC METHODS =====================
    public function getAccordionList(): array
    {
        return $this->accordionTemplate;
    }

    /**
     * @return string[] an associative array of groupName => displayAs
     */
    public function getAllGroups(): array
    {
        return $this->groupNames;
    }

    public function getAllLessonNames(): array
    {
        return $this->lessonNames;
    }

    public function getAllLessons(): array
    {
        return $this->lessons;
    }

    /**
     * @return Lesson|null a reference to the currentLesson (or null if current lesson not set)
     */
    public function &getCurrentLesson(): ?Lesson
    {
        $currentLessonName = (new Session)->getCurrentLessonName();
        if ($currentLessonName) {
            $ref = &$this->lessons[$this->getRealLessonName($currentLessonName)];
        } else {
            $ref = &$this->lessons[$this->lessonNames[0]];
        }

        return $ref;
    }

    public function getCurrentLessonName(): string
    {
        return (new Session)->getCurrentLessonName();
    }

    public function getGroupName(string $groupName): string
    {
        return $this->groupNames[$groupName] ?? $groupName;
    }

    public function getMaxLengths(): array
    {
        return $this->maxLengths;
    }

    /**
     * @param string $LessonName if specified, overrides the actual current lesson
     *
     * @return string the next lesson. If we were on the last lesson, loop around
     */
    public function getNextLessonName(string $LessonName = ''): string
    {
        $session = new Session;
        $currentLessonName = $this->getRealLessonName($session->getCurrentLessonName());
        $next = $this->lessons[$currentLessonName]->ordering + 1;
        if ($next >= count($this->lessons)) $next = 0;
        return $this->getRealLessonName($this->lessonNames[$next]);

    }

    public function getRealLessonName(string $oldLessonName): string
    {
        $oldName = Util::convertLessonKeyToLessonName($oldLessonName);
        return $this->alternateNameMap[$oldName] ?? '';
    }

    public function lessonExists(string $lessonName): bool
    {
        return array_key_exists($lessonName, $this->alternateNameMap);
    }

    public function writeAllLessons(string $fileName): void
    {
        $fullLessons = ['lessons' => []];
        foreach ($this->lessons as $key => $lesson) {
            $fullLessons['lessons'][$key] = $lesson;
        }

        $data = json_encode($fullLessons, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($fileName, $data);
    }
}
