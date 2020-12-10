<?php

namespace App\ReadXYZ\Lessons;

use App\ReadXYZ\Data\GroupData;
use App\ReadXYZ\Data\LessonsData;
use App\ReadXYZ\Data\StudentLessonsData;
use App\ReadXYZ\Enum\MasteryLevel;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\Session;

use stdClass;

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

    /** @var stdClass[] fields: groupCode, groupName, groupDisplayAs, fileName (keychain), friendlyName */
    private array $groupInfo;
    private array $groupDisplayAs;
    /** @var array structure is accordion[groupName][lessonName] => masteryLevel (0-none, 1-advancing, 2-mastered) */
    private array $accordion = []; // used as a starting point for mastery which is applied in student lesson
    private array $alternateNameMap = [];
    private array $lessonNamesMap = []; // lessonName => lessonCode
    private array $displayAs = [];

    /**
     * Lessons constructor.
     * @throws PhonicsException
     */
    private function __construct()
    {
        $groupData = new GroupData();
        $lessonsData = new LessonsData();

        $this->groupInfo = $groupData->getGroupExtendedAssocArray();

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
            $this->alternateNameMap[$lessonCode] = $lessonName;
            $this->alternateNameMap[$lessonName] = $lessonName;
            $this->alternateNameMap[$lessonDisplay] = $lessonName;
            foreach($lesson->alternateNames as $name) {
                if (!isset($this->alternateNameMap[$name])) $this->alternateNameMap[$name] = $lessonName;
            }

            $this->displayAs[$lessonCode] = $lessonDisplay;
            $this->displayAs[$lessonName] = $lessonDisplay;
            $this->displayAs[$lessonDisplay] = $lessonDisplay;
            foreach($lesson->alternateNames as $name) {
                if (!isset($this->alternateNameMap[$name])) $this->alternateNameMap[$name] = $lessonDisplay;
            }

            $groupName = $lessonInfo->groupName;
            if (! isset($this->accordion[$groupName])) $this->accordion[$groupName] = [];
            $this->accordion[$groupName][$lesson->lessonName] = 0;
            $this->lessonNamesMap[$lessonName] = $lessonCode;
        }

    }

// ======================== STATIC METHODS =====================

    /**
     * passes back the singleton instance for this class. Refresh the mastery accordion with each student change.
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

        $this->setAccordion();
        return $this->accordion;
    }

    /**
     * associative array of groupName => vw_groups_with_keychain objects
     * fields include groupCode, groupName, groupDisplayAs, fileName (keychain), friendlyName
     * @return stdClass[]
     */
    public function getGroupDisplayAs(): array
    {
        return $this->groupDisplayAs;
    }

    public function getLessonNamesMap(): array
    {
        return $this->lessonNamesMap;
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
        $currentLessonCode = (new Session)->getCurrentLessonCode();
        if ($currentLessonCode) {
            $ref = &$this->lessons[$currentLessonCode];
        } else {
            $ref = &$this->lessons[array_key_first($this->lessons)];
        }

        return $ref;
    }

    public function getCurrentLessonName(): string
    {
        return (new Session())->getCurrentLessonName();
    }

    public function getCurrentLessonCode(): string
    {
        return (new Session())->getCurrentLessonCode();
    }

    /**
     * returns an assoc array of possibleLessonName => lessonDisplayAs
     * @return array
     */
    public function getLessonDisplayAs(): array
    {
        return $this->displayAs;
    }

    public function getGroupName(string $groupName): string
    {
        return $this->groupInfo[$groupName] ?? $groupName;
    }

    /**
     * @return string the next lesson. If we were on the last lesson, loop around
     */
    public function getNextLessonName(): string
    {
        $session = new Session;
        $currentLessonName = $this->getRealLessonName($session->getCurrentLessonName());
        $next = $this->lessons[$currentLessonName]->ordering + 1;
        if ($next >= count($this->lessons)) $next = 0;
        return $this->getRealLessonName($this->lessonNames[$next]);

    }

    /**
     * using alternate names and lessonCode and lessonDisplayAs as possibilities, return the "official" lesson name.
     * returns empty if not found
     * @param string $oldLessonName
     * @return string if found returns the "official" lesson name, otherwise returns empty string
     */
    public function getRealLessonName(string $oldLessonName): string
    {
        $oldName = Util::convertLessonKeyToLessonName($oldLessonName);
        return $this->alternateNameMap[$oldName] ?? '';
    }

    public function lessonExists(string $lessonName): bool
    {
        return array_key_exists($lessonName, $this->alternateNameMap);
    }

    public function setAccordion()
    {
        $mastery = (new StudentLessonsData())->getLessonMastery();
        foreach($mastery as $lesson) {
            if (isset($this->accordion[$lesson->groupName][$lesson->lessonName])) {
                $this->accordion[$lesson->groupName][$lesson->lessonName] = MasteryLevel::toIntegral($lesson->masteryLevel);
            }
        }
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
