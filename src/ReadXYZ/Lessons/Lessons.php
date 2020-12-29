<?php

namespace App\ReadXYZ\Lessons;

use App\ReadXYZ\Data\GroupData;
use App\ReadXYZ\Data\LessonsData;
use App\ReadXYZ\Data\StudentLessonsData;
use App\ReadXYZ\Enum\MasteryLevel;
use App\ReadXYZ\Helpers\Debug;
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

    /** @var Lesson[]  lessonName => Lesson object */
    private array $lessons = [];

    /** @var stdClass[] lessonName => Lesson stdclass object */
    private array $lessonsData = [];

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

        // $start = Debug::startTimer();
        $groupData = new GroupData();
        $lessonsData = new LessonsData();

        $this->groupInfo = $groupData->getGroupExtendedAssocArray();

        $data = $lessonsData->getLessonsWithGroupFields();
        foreach ($data as $datum) {
            $this->lessonsData[$datum->lessonName] = $datum;
        }


        foreach ($this->lessonsData as $lessonInfo) {
            $lessonCode = $lessonInfo->lessonCode;
            $lessonName = $lessonInfo->lessonName;
            $lessonDisplay = $lessonInfo->lessonDisplayAs;

            //adding every conceivable alias
            $this->alternateNameMap[$lessonCode] = $lessonName;
            $this->alternateNameMap[$lessonName] = $lessonName;
            $this->alternateNameMap[$lessonDisplay] = $lessonName;
            $alternates = $lessonsData->decodeJson($lessonInfo->alternateNames);
            if ($alternates != null) {
                foreach($alternates as $name) {
                    if ($name && !isset($this->alternateNameMap[$name])) $this->alternateNameMap[$name] = $lessonName;
                }

            }

            $this->displayAs[$lessonCode] = $lessonDisplay;
            $this->displayAs[$lessonName] = $lessonDisplay;
            $this->displayAs[$lessonDisplay] = $lessonDisplay;

            $groupName = $lessonInfo->groupName;
            if (! isset($this->accordion[$groupName])) $this->accordion[$groupName] = [];
            $this->accordion[$groupName][$lessonInfo->lessonName] = 0;
            $this->lessonNamesMap[$lessonName] = $lessonCode;

        }
        // Debug::logElapsedTime($start, 'Lessons::__construct');
    }


// ======================== STATIC METHODS =====================

    /**
     * passes back the singleton instance for this class. Refresh the mastery accordion with each student change.
     *
     * @return Lessons
     */
    public static function getInstance()
    {
        if (isset(self::$instance)) return self::$instance;
        self::$instance = new Lessons();
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

    /**
     * @param string $lessonName
     * @return object|null
     * @throws PhonicsException
     */
    public function getLesson(string $lessonName): ?object
    {
        $name = $this->getRealLessonName($lessonName);
        if (empty($name)) return null;
        return new Lesson($this->lessonsData[$name]);
    }

    public function getLessonCode($lessonName): string
    {
        $name = $this->getRealLessonName($lessonName);
        if (empty($name)) return '';
        return $this->lessonNamesMap[$name] ?? '';
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
     * @throws PhonicsException
     */
    public function getNextLessonName(): string
    {
        $session = new Session;
        $currentLessonCode = $session->getCurrentLessonCode();
        if (empty($currentLessonCode)) {
            throw new PhonicsException("We should always be on a lesson when we get here.");
        }
        $group = intval(substr($currentLessonCode, 1,2));
        $lesson = intval(substr($currentLessonCode, 4,2));
        $next = sprintf("G%02dL%02d", $group, $lesson + 1);
        if (key_exists($next, $this->alternateNameMap)) {
            return $this->alternateNameMap[$next];
        }
        $next = sprintf("G%02dL%02d", $group + 1, $lesson);
        if (key_exists($next, $this->alternateNameMap)) {
            return $this->alternateNameMap[$next];
        } else {
            return array_key_first($this->lessonNamesMap);
        }
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

    /**
     * @throws PhonicsException
     */
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
