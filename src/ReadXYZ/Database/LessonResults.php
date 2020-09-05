<?php

namespace ReadXYZ\Database;

// LessonResults holds one record per studentID/lesson, with an array in the cargo
use ReadXYZ\Helpers\Util;
use ReadXYZ\Models\Identity;

class LessonResults extends AbstractData implements IBasicTableFunctions
{
    // Hold a singleton instance of the class
    private static $instance;
    private bool $inSystemLog;

    protected function __construct()
    { // private, so can't instantiate from outside class
        parent::__construct();
        $this->tableName = 'abc_LessonResults';
        $this->uuidPrefix = 'R';
        $this->primaryKey = 'studentLesson';
        $this->secondaryKeys = ['studentID', 'lessonKey', 'studentID'];
        $this->inSystemLog = false;
    }

    // The singleton method
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new LessonResults();
        }

        return self::$instance;
    }

    public function create()
    {
        $createString =
            "CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
              `lessonKey`      	        varchar(64)  NOT NULL,
              `studentID`  	            varchar(32)  NOT NULL,
              `studentLesson`  	        varchar(96)  NOT NULL,
              `project`                 varchar(32),
              `cargo` 	                text,
              `created`                 int(10) unsigned default 0,
              `createdhuman`            varchar(32),
              `lastupdate`              int(10) unsigned default 0,
              `lastbackup`              int(10) unsigned default 0,
              PRIMARY KEY  (`{$this->primaryKey}`)
            ) DEFAULT CHARSET=utf8;";
        $this->createTable($createString);
        return true;
    }

    public function write($newLesson)
    { // lessonResult is the POST array
        // there is only one record per lesson, so if it exists then we READ it
        //    and append $lessonResult to the cargo

        assert(is_array($newLesson));

        $identity = Identity::getInstance();
        $newLesson['userName'] = $identity->getUserName();
        $newLesson['sessionID'] = $identity->getSessionId();
        $newLesson['created'] = time();
        $newLesson['createdhuman'] = Util::getHumanReadableDateTime();

        // lets us find records quickly
        $key = $this->createLessonResultsKey($newLesson['lessonKey'], $identity->getStudentId());

        $resultSet = $this->query("select cargo from {$this->tableName} where studentLesson = '$key'");
        assert(count($resultSet) < 2, "Found more than one LessonResult for '$key'");

        if (0 == count($resultSet)) { // didn't find the key, so add a new record
            $cargo = [];
            // need the three primary keys in the cargo
            $cargo['project'] = $identity->getProject();
            $cargo['studentID'] = $identity->getStudentId();
            $cargo['lessonKey'] = $newLesson['lessonKey'];
            $cargo['studentlesson'] = $key;
            $cargo['lessons'] = [];
            $cargo['lessons'][] = $newLesson;

            $aArray = [];
            $aArray['studentID'] = $identity->getStudentId();
            $aArray['lessonKey'] = $newLesson['lessonKey'];
            $aArray['studentlesson'] = $key;
            $aArray['project'] = $identity->getProject();
            $aArray['created'] = time();
            $aArray['createdhuman'] = Util::getHumanReadableDateTime();
            $aArray['cargo'] = serialize($cargo);
            $this->insertArray($aArray); // and write.
        } else { // already have this record, just add the cargo
            $result = $resultSet[0];
            $cargo = unserialize($result['cargo']);
            $cargo['lessons'][] = $newLesson;

            $this->updateByKey($key, $cargo); // not serialized here
        }
    }

    /**
     * we need to keep the namespace out of the lessonKey. Only works for Blending currently.
     */
    private function createLessonResultsKey(string $studentId, string $lessonKey): string
    {
        $pos = strpos($lessonKey, 'Blending');
        $lesson = substr($lessonKey, $pos);
        $key = $studentId . $lesson;
        $key = substr($key, 0, 64); // may truncate

        return str_replace("'", '*', $key); // can't have quotes in $key
    }

    public function getLessonRecords($studentID, $lessonKey = '')
    { // returns ONE record
        $key = $this->createLessonResultsKey($lessonKey, $studentID);
        $resultSet = $this->query("select cargo from {$this->tableName} where studentlesson = '$key'");

        $result = [];
        foreach ($resultSet as $r) { // unpack the results into a single array
            foreach ($r as $s) {
                $result[] = unserialize($s);
                //echo "returns resultSet ".$s.'<br><br>';
            }
        }

        return $result;
    }
}
