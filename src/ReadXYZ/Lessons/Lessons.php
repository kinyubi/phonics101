<?php

namespace ReadXYZ\Lessons;

use InvalidArgumentException;
use Peekmo\JsonPath\JsonStore;
use ReadXYZ\Helpers\Util;
use ReadXYZ\Models\Cookie;
use ReadXYZ\Models\Student;
use Throwable;

/**
 * Class Lessons
 * This lesson will render lessons from unifiedLessons.json.
 *
 * @package ReadXYZ\Lessons
 */
class Lessons
{
    private static Lessons $instance;

    private ?Lesson $nullGuard;
    /** @var Lesson[] */
    private array $blendingLessons = [];
    private JsonStore $store;
    /** @var string[] */
    private array $groupNames = [];
    /** @var array structure is accordion[groupId][lessonName] => masteryLevel (0-none, 1-advancing, 2-mastered) */
    private array $accordion = [];
    private string $currentLessonName;
    private array $alternateNameMap;
    private array $lessonNames = [];
    private int $currentLessonNameIndex;

    private function __construct()
    {
        $inputFile = Util::getReadXyzSourcePath('resources/unifiedLessons.json');
        $json = file_get_contents($inputFile);
        $this->store = new JsonStore($json);
        $all = json_decode($json);
        foreach ($all->groups as $group) {
            $this->groupNames[$group->groupId] = $group->displayAs;
        }
        foreach ($this->groupNames as $index => $group) {
            $this->accordion[$group] = [];
        }

        foreach ($all->lessons->blending as $key => $lessonArray) {
            $lesson = new Lesson($lessonArray);
            $this->blendingLessons[$key] = $lesson;
            $groupName = $this->getGroupName($lesson->getGroupId());
            $this->accordion[$groupName][$lesson->getLessonName()] = 0;
            $this->lessonNames[] = $key;
        }

        $cookie = new Cookie();
        $this->nullGuard = null;
        $currentLessonName = $cookie->getCurrentLesson();
        if (not($this->lessonExists($currentLessonName))) {
            $currentLessonName = $this->lessonNames[0];
            $this->setCurrentLesson($currentLessonName);
        }
        $this->createAlternateNameMap();
        try {
            $this->updateStudentMastery();
        } catch (Throwable $exception) {
            trigger_error('Unable to update student mastery at initial invocation of Lessons class.');
        }
    }

    public function lessonExists(string $lessonName): bool
    {
        return in_array($lessonName, $this->lessonNames);
    }

    public function getGroupName(string $groupId): string
    {
        return $this->groupNames[$groupId] ?? $groupId;
    }

    /**
     * @param string $lessonName
     *
     * @throws InvalidArgumentException when passed a nonexistent lesson name
     */
    public function setCurrentLesson(string $lessonName): void
    {
        $lessonName = Util::convertLessonKeyToLessonName($lessonName);
        if (key_exists($lessonName, $this->blendingLessons)) {
            $this->currentLessonName = $lessonName;
            $this->currentLessonNameIndex = array_search($lessonName, $this->lessonNames);
            (new Cookie())->setCurrentLesson($lessonName);
        } else {
            throw new InvalidArgumentException("$lessonName is not a valid lesson name.");
        }
    }

    /**
     * build a lookup map of alternate lesson names to map to the current actual lesson name.
     */
    private function createAlternateNameMap(): void
    {
        foreach ($this->blendingLessons as $key => $lesson) {
            foreach ($lesson->getAlternateNames() as $altName) {
                $this->alternateNameMap[$altName] = $key;
            }
        }
    }


    /**
     * for the current student, we determine which lessons have been mastered.
     */
    private function updateStudentMastery(): void
    {
        $student = Student::getInstance();
        $cargo = $student->cargo;
        $lessonLocations = ['masteredLessons', 'currentLessons'];
        foreach ($lessonLocations as $location) {
            foreach ($cargo[$location] as $key => $item) {
                if (isset($item['mastery'])) {
                    // if it's not one of the current lessons, don't include it
                    $value = $item['mastery'] > 1 ? 2 : $item['mastery'];
                    $lessonName = Util::convertLessonKeyToLessonName($key);
                    $realName = $this->alternateNameMap[$lessonName] ?? '';
                    if ($realName) {
                        $groupId = $this->blendingLessons[$realName]->getGroupId() ?? '';
                        $groupName = $this->getGroupName($groupId);
                        if ($groupName && key_exists($realName, $this->accordion[$groupName])) {
                            $this->accordion[$groupName][$realName] = $value;
                        }
                    }
                }
            }
        }
    }

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

    public function getAccordionList(): array
    {
        $this->updateStudentMastery();

        return $this->accordion;
    }

    public function getCurrentLessonName(): string
    {
        return $this->currentLessonName;
    }

    public function validateLessonName(string $lessonName): bool
    {
        if ( ! $lessonName) {
            $lessonName = $this->currentLessonName;
        }

        return ($lessonName) && key_exists($lessonName, $this->blendingLessons);
    }

    /**
     * @return Lesson|null a reference to the currentLesson (or null if current lesson not set)
     */
    public function &getCurrentLesson(): ?Lesson
    {
        if ($this->currentLessonName) {
            $ref = &$this->blendingLessons[$this->currentLessonName];
        } else {
            $ref = &$this->blendingLessons[$this->lessonNames[0]];
        }

        return $ref;
    }

    public function writeAllLessons(string $fileName): void
    {
        $fullLessons = ['lessons' => []];
        foreach ($this->blendingLessons as $key => $lesson) {
            $fullLessons['lessons'][$key] = $lesson;
        }

        $data = json_encode($fullLessons, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($fileName, $data);
    }

    public function getAllLessonNames(): array
    {
        return $this->lessonNames;
    }

    /**
     * @param string $LessonName if specified, overrides the actual current lesson
     *
     * @return string the next lesson. If we were on the last lesson, loop around
     */
    public function getNextLessonName(string $LessonName = ''): string
    {
        $lastLesson = end($this->lessonNames);
        if (empty($LessonName) && empty($this->currentLessonName)) {
            // handle the case where we don't have a current lesson
            $this->currentLessonNameIndex = 0;
        } elseif (($this->currentLessonName == $lastLesson) || ($LessonName == $lastLesson)) {
            // handle when it wants the lesson after the last lesson
            $this->currentLessonNameIndex = 0;
        } elseif (empty($lessonName)) {
            ++$this->currentLessonNameIndex;
        } else { // by process of elimination, we have specified a lesson name and we want the next lesson
            $index = array_search($lessonName, $this->lessonNames);
            if (false === $index) {
                $this->currentLessonNameIndex = 0;
            } else {
                $this->currentLessonNameIndex = $index + 1;
            }
        }
        $this->currentLessonName = $this->lessonNames[$this->currentLessonNameIndex];

        return $this->currentLessonName;
    }
}
