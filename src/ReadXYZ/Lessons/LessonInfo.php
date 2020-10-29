<?php

namespace App\ReadXYZ\Lessons;

use Exception;
use Peekmo\JsonPath\JsonStore;
use App\ReadXYZ\Helpers\Util;

/**
 * @copyright (c) 2020 ReadXYZ, LLC
 * @author Carl Baker (carlbaker@gmail.com)
 * @license GPL3+
 */

/**
 * Class LessonInfo Handles queries to groupedLessons.json.
 *
 * @package ReadXYZ\Lessons
 */
class LessonInfo
{
    private static LessonInfo $instance;           // Hold a singleton instance of the class

    private JsonStore $lesson_shell;
    private array $lessons;
    private array $groups;
    private array $lessonGroups = [];
    private int $current_lesson_index = -1;

    /**
     * SideNote constructor.
     *
     * @throws Exception if the file side_notes.json is not found in the resources folder
     */
    private function __construct()
    {
        // If we want the structure returned as an associative array, use json_decode($JSON, true);
        $lessons_file = Util::getReadXyzSourcePath('resources/old/groupedLessons.json');
        $str = file_get_contents($lessons_file, false, null);
        $this->lessons = [];
        $this->lesson_shell = new JsonStore($str);
        $this->lessons = $this->lesson_shell->get('$.groups[*].lessons[*]'); // we don't want the outermost level
        $this->groups = $this->lesson_shell->get('$.groups[*]');
        if (!$this->lessons) {
            throw new Exception(json_last_error_msg());
        }
        foreach ($this->groups as $group) {
            $groupName = $group['displayAs'] ?? $group['groupName'];
            foreach ($group['lessons'] as $lesson) {
                $lessonName = $lesson['lessonName'] ?? $lesson['displayAs'];
                $this->lessonGroups[$lessonName] = $groupName;
                // foreach ($lesson['alternateLessonNames'] as $alternate) {
                //     if ($alternate) {
                //         $this->lessonGroups[$alternate] = $groupName;
                //     }
                // }
            }
        }
    }

    /**
     * get the singleton instance of LessonInfo.
     *
     * @return LessonInfo
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new LessonInfo();
        }

        return self::$instance;
    }

    // ============ GETTERS ===========

    /**
     * array layout: groupname-> [mastered->bool, lessons->[ lessonName->name, mastered->bool] ].
     *
     * @return array
     */
    public function getAccordionList(): array
    {
        $accordion = [];
        foreach ($this->groups as $group) {
            $groupName = $group['groupName'];
            $accordion[$groupName] = [];
            foreach ($group['lessons'] as $lesson) {
                $accordion[$groupName][$lesson['lessonName']] = 0;
            }
        }

        return $accordion;
    }

    /**
     * returns all groups and all of the lessons in each group.
     *
     * @return array
     */
    public function getAllGroups(): array
    {
        return $this->groups;
    }

    /**
     * returns all the lessons in an associative array with the lesson name as the key.
     *
     * @return array a multi-dimensional array of all the lessons. Each lesson is an associative array.
     */
    public function getAllLessons(): array
    {
        return $this->lessons;
    }

    public function getAllWordLists(): array
    {
        $lessonNames = $this->getLessonNames();
        $wordLists = [];
        foreach ($lessonNames as $lessonName) {
            $wordLists[$lessonName] = $this->getWordList($lessonName);
        }

        return $wordLists;
    }

    /**
     * @return array an associative array of the current lesson. Returns empty array if not found
     */
    public function getCurrentLesson(): array
    {
        if ($this->current_lesson_index >= 0) {
            return $this->lessons[$this->current_lesson_index];
        } else {
            return [];
        }
    }

    public function getGames(string $lessonName): array
    {
        $games = [];
        $lesson = $this->getLesson($lessonName);
        if ($lesson && array_key_exists('games', $lesson)) {
            $games = $lesson['games'];
        }

        return $games;
    }

    /**
     * @param string $lessonName
     *
     * @return false|string
     */
    public function getGroupName(string $lessonName)
    {
        return $this->lessonGroups[$lessonName] ?? false;
    }

    public function getJsonStoreObject(): JsonStore
    {
        return $this->lesson_shell;
    }

    /**
     * @param string $lessonName the id or display as key of the lesson we want to have returned
     *
     * @return array an associative array representing the specified lesson or empty array if not found
     */
    public function getLesson(string $lessonName): array
    {
        $this->setCurrentLesson(($lessonName));

        return $this->getCurrentLesson();
    }

    public function getLessonGroups(): array
    {
        $lessonGroups = [];
        foreach ($this->groups as $group) {
            foreach ($group['lessons'] as $lesson) {
                $lessonGroups[$lesson['lessonName']] = $group['groupName'];
            }
        }

        return $lessonGroups;
    }

    /**
     * returns the displayAs field for each lesson.
     *
     * @return array
     */
    public function getLessonNames(): array
    {
        return $this->lesson_shell->get('$.groups[*].lessons[*].lessonName');
    }

    public function getSideImage(string $lessonName): string
    {
        $lesson = $this->getLesson($lessonName);

        return $lesson['sideImage'] ?? '';
    }

    public function getSpinner(string $lessonName): array
    {
        $array = ['prefixes' => '', 'vowel' => '', 'suffix' => ''];
        $lesson = $this->getLesson($lessonName);
        if (!$lesson || not(isset($lesson['spinner']))) {
            return $array;
        }

        return $lesson['spinner'] ?? $array;
    }

    public function getWordList(string $lessonName): string
    {
        $list = '';
        $lesson = $this->getLesson($lessonName);
        if ($lesson && array_key_exists('wordList', $lesson)) {
            $list = preg_replace('/\s+/', '', $lesson['wordList']);
        }

        return $list;
    }

    // ============ SETTERS ===========

    /**
     * @param string $lessonName The 'lessonName' key or 'id' key associated with the lesson
     *
     * @return bool true if successfully set, otherwise false
     */
    public function setCurrentLesson(string $lessonName): bool
    {
        $curr = $this->current_lesson_index;

        if ($curr >= 0) {
            $display_set = isset($this->lessons[$curr]['lessonName']);
            $key_set = isset($this->lessons[$curr]['id']);
            if ($display_set && ($this->lessons[$curr]['lessonName'] == $lessonName)) {
                return true;
            }
            if ($key_set && ($this->lessons[$curr]['lessonName'] == $lessonName)) {
                return true;
            }
        }
        $lesson_ct = count($this->lessons);
        for ($i = 0; $i < $lesson_ct; ++$i) {
            if (($this->lessons[$i]['lessonName'] == $lessonName) || ($this->lessons[$i]['id'] == $lessonName)) {
                $this->current_lesson_index = $i;

                return true;
            }
        }
        //not found
        $this->current_lesson_index = -1;

        return false;
    }


    // =========== PROTECTED/PUBLIC METHODS

    /**
     * given a name list, tries to find the lesson. If it finds it, it sets the lesson to current.
     * @param array $nameList known lesson names of the lesson we are searching for
     *
     * @return string
     */
    public function findLessonName(array $nameList)
    {
        $index = 0;
        foreach ($this->lessons as $lesson) {
            foreach ($nameList as $name) {
                if(empty($name)) continue;
                if ($name == ($lesson['lessonName'] ?? $lesson['displayAs'])) {
                    $this->current_lesson_index = $index;

                    return $name;
                }
                // foreach ($lesson['alternateLessonNames'] as $altName) {
                //     if ($altName == $name) {
                //         $this->current_lesson_index = $index;
                //
                //         return $lesson['lessonName'];
                //     }
                // }
            }
            ++$index;
        }
        $this->current_lesson_index = -1;

        return '';
    }

}
