<?php

namespace App\ReadXYZ\Lessons;

use Peekmo\JsonPath\JsonStore;
use App\ReadXYZ\Helpers\Util;

if (not(defined('CLASS'))) {
    define('CLASS', 0);
    define('LAYOUT', 1);
    define('STYLE', 2);
    define('TAB_NAME', 3);
    define('METHOD', 4);
    define('DATA', 5);
    define('NOTE', 6);
}

class OldBlendingInfo
{
    private static OldBlendingInfo $instance;

    private array $blending_shell = [];
    private array $groups = []; //lesson keys arranged by group
    private array $lessons = []; //lessons by lessonKey key
    private array $lessonsGroup = [];
    private array $accordion = [];

    private function __construct()
    {
        $blending_file = Util::getReadXyzSourcePath('resources/old/blendingLessons.json');
        $str = file_get_contents($blending_file, false, null);
        $this->blending_shell = json_decode($str, true);
        foreach ($this->blending_shell as $item) {
            $groupName = $item['group'];
            $lessonKey = $item['lessonKey'];
            $lessonName = Util::convertLessonKeyToLessonName($lessonKey);
            $this->groups[$groupName][] = $lessonKey;
            $this->lessons[$lessonKey] = $item;
            $this->lessonsGroup[$lessonKey] = $item['group'];
            $this->accordion[$groupName][$lessonName] = 0;
        }
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new OldBlendingInfo();
        }

        return self::$instance;
    }

    // ============ GETTERS ===========
    public function getAccordionList(): array
    {
        $this->accordion = [];
        foreach ($this->groups as $groupName => $lessonList) {
            $this->accordion[$groupName] = [];
            foreach ($lessonList as $lessonKey) {
                $lessonName = str_replace('Blending.', '', $lessonKey);
                $this->accordion[$groupName][$lessonName] = 0;
            }
        }

        return $this->accordion;
    }

    public function getAllLessonGroups(): array
    {
        return $this->lessonsGroup;
    }

    /**
     * returns an associative array of all lessons - lessonKey : lesson array of data.
     *
     * @return array an associative array with lessonKey as key for each lesson
     */
    public function getAllLessons(): array
    {
        return $this->lessons;
    }

    /**
     * @param string $groupName
     *
     * @return false|string[]
     */
    public function getGroupLessonKeys(string $groupName)
    {
        return $this->groups[$groupName] ?? false;
    }

    /**
     * @param string|false $lessonKey
     *
     * @return bool|string
     */
    public function getGroupName(string $lessonKey)
    {
        return $this->lessons[$lessonKey]['group'] ?? false;
    }

    public function getGroupNames(): array
    {
        return array_keys($this->groups);
    }

    public function getJsonStoreObject(): JsonStore
    {
        $blending_file = Util::getReadXyzSourcePath('resources/old/blendingLessons.json');
        $json = file_get_contents($blending_file, false, null);

        return new JsonStore($json);
    }

    /**
     * @param string $lessonKey the lesson key
     *
     * @return false|array the lesson array if it exists, otherwise false
     */
    public function getLesson(string $lessonKey)
    {
        return $this->lessons[$lessonKey] ?? false;
    }


    /**
     * @param string $lessonKey
     *
     * @return string[]|array
     */
    public function getSpinner(string $lessonKey): array
    {
        $array = ['prefixes' => '', 'vowel' => '', 'suffix' => ''];
        $lesson = $this->getLesson($lessonKey);
        if (not(isset($lesson['pages']))) {
            return [];
        } else {
            foreach ($lesson['pages'] as $page) {
                if ('Spinner' == $page[TAB_NAME]) {
                    $array['prefixes'] = $page[DATA][0] ?? '';
                    $array['vowel'] = $page[DATA][1] ?? '';
                    $array['suffix'] = $page[DATA][2] ?? '';
                    break;
                }
            }
        }
        return ($array['prefixes']) ? $array : [];
    }


    // =========== PROTECTED/PUBLIC METHODS

    /**
     * @param array $nameList an array of possible names for the lesson
     *
     * @return array|false if a lesson is found, the "blending" array is returned, otherwise false
     */
    public function findLesson(array $nameList)
    {
        foreach ($nameList as $name) {
            if (empty($name)) continue;
            $lesson = $this->getLesson("Blending.$name");
            if (false !== $lesson) {
                return $lesson;
            }
        }

        return false;
    }
}

// Only run this from command line (old Python trick)
if (!count(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))) {
    require 'c:/laragon/www/bopp/public/phonics/202/autoload.php';
    $blendingInfo = OldBlendingInfo::getInstance();
    $groupNames = $blendingInfo->getGroupNames();
    print_r($groupNames);
    $fatCatSatLessons = $blendingInfo->getGroupLessonKeys('Fat Cat Sat');
    print_r($fatCatSatLessons);
    $oneGroup = $blendingInfo->getGroupName('Blending.Big Dig Fig');
    printf("Bat + Bit group: %s\n", $oneGroup);
}
