<?php

namespace App\ReadXYZ\Lessons;

use InvalidArgumentException;

use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\Session;

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

    /** @var string[] */
    private array $groupNames = [];
    /** @var array structure is accordion[groupName][lessonName] => masteryLevel (0-none, 1-advancing, 2-mastered) */
    private array $accordion = [];
    private string $currentLessonName;
    private array $alternateNameMap;
    private array $lessonNames = [];
    private int $currentLessonNameIndex;

    private array $maxLengths;

    private function __construct()
    {
        $inputFile = Util::getReadXyzSourcePath('resources/unifiedLessons.json');
        $json = file_get_contents($inputFile);
        $all = json_decode($json);
        foreach ($all->groups as $group) {
            $this->groupNames[$group->groupName] = $group->displayAs;
        }
        foreach ($this->groupNames as $index => $group) {
            $this->accordion[$group] = [];
        }
        $this->maxLengths = ['wordlist' => 0, 'supplemental' => 0, 'contrast' => 0, 'stretch' =>0];
        foreach ($all->lessons->blending as $key => $lessonArray) {
            $lesson = new Lesson($lessonArray);
            $this->blendingLessons[$key] = $lesson;
            $groupName = $this->getGroupName($lesson->getGroupName());
            $this->accordion[$groupName][$lesson->getLessonName()] = 0;
            $this->lessonNames[] = $key;
            $lengths = $lesson->getLengths();
            foreach (array_keys($this->maxLengths) as $key) {
                if($lengths[$key] > $this->maxLengths[$key]) $this->maxLengths[$key] = $lengths[$key];
            }

        }

        $this->nullGuard = null;
        $session = new Session();
        $currentLessonName = $session->getCurrentLessonName();
        if (empty($currentLessonName) || not($this->lessonExists($currentLessonName))) {
            $currentLessonName = $this->lessonNames[0];
            $this->setCurrentLesson($currentLessonName);
        }
        $this->createAlternateNameMap();
    }

    public function getMaxLengths(): array
    {
        return $this->maxLengths;
    }

    public function lessonExists(string $lessonName): bool
    {
        return in_array($lessonName, $this->lessonNames);
    }

    /**
     * @return string[] an associative array of groupName => displayAs
     */
    public function getAllGroups(): array
    {
        return $this->groupNames;
    }

    public function getGroupName(string $groupName): string
    {
        return $this->groupNames[$groupName] ?? $groupName;
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
            $session = new Session();
            if ($session->isValid()) {
                $session->updateLesson($lessonName);
            }
        } else {
            throw new InvalidArgumentException("$lessonName is not a valid lesson name.");
        }
    }

    public function getRealLessonName(string $oldLessonName): string
    {
        $oldName = Util::convertLessonKeyToLessonName($oldLessonName);
        return $this->alternateNameMap[$oldName] ?? '';
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

    public function getAllLessons(): array
    {
        return $this->blendingLessons;
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
