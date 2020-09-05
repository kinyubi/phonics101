<?php

namespace ReadXYZ\Database;

use ReadXYZ\Helpers\Util;
use ReadXYZ\Lessons\LessonInfo;

class LessonMastery
{
    private PhonicsDb $connect;

    public function __construct()
    {
        $this->connect = new PhonicsDb();
    }

    private function writeRecord(array $record): bool
    {
        printf("id:%s key:%s value:%d\n", $record['id'], $record['key'], $record['value']);
        $query = "INSERT INTO `abc_lesson_mastery` VALUES ('{$record['key']}', '{$record['id']}', {$record['value']})";

        return $this->connect->queryStatement($query);
    }

    public function writeTable()
    {
        $info = LessonInfo::getInstance();
        $lessonNames = $info->getLessonNames();
        $lessonKeys = [];
        foreach ($lessonNames as $name) {
            $lessonKeys[] = 'Blending.' . $name;
        }
        sort($lessonKeys);
        $query = 'TRUNCATE TABLE `abc_lesson_mastery`';
        $result = $this->connect->queryStatement($query);
        if (!$result) {
            exit($this->connect->getErrorMessage());
        }
        $badKeys = [];
        $lessonLocations = ['masteredLessons', 'currentLessons'];
        $query = 'SELECT studentid, cargo, StudentName, trainer1 FROM abc_Student';
        $response = $this->connect->queryRows($query);
        if (!$response->wasSuccessful()) {
            exit('Query failed');
        }
        foreach ($response->getResult() as $student) {
            if (!$student['cargo']) {
                continue;
            }
            $cargo = unserialize($student['cargo']);
            foreach ($lessonLocations as $location) {
                if (isset($cargo[$location])) {
                    foreach ($cargo[$location] as $lessonKey => $lesson) {
                        if (isset($lesson['mastery'])) {
                            $value = $lesson['mastery'];
                            if ($value) {
                                $value = ($value > 1) ? 2 : 1;
                                if (!Util::startsWith($lessonKey, 'Blending')) {
                                    continue;
                                }
                                if (!in_array($lessonKey, $lessonKeys)) {
                                    $badKeys[] = sprintf("%s not a valid lesson key\n", $lessonKey);
                                } else {
                                    $key = str_replace(['<sound>', '</sound>', "'"], ['', '', ''], $lessonKey);
                                    $record = ['id' => $student['studentid'], 'key' => $key, 'value' => $value];
                                    $result = $this->writeRecord($record);
                                    if (!$result) {
                                        exit($this->connect->getErrorMessage());
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        echo "\n\n";
        foreach ($badKeys as $badKey) {
            echo $badKey;
        }
    }
}
// Only run this from command line (old Python trick)
if (!count(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))) {
    require dirname(__DIR__) . '/autoload.php';
    $lessonMastery = new LessonMastery();
    $lessonMastery->writeTable();
}
