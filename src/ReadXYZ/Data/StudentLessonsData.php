<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\MasteryLevel;
use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Enum\TimerType;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\BoolWithMessage;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Helpers\PhonicsException;
use stdClass;

class StudentLessonsData extends AbstractData
{

    private Session $session;
    private string  $quotedStudentCode;
    private string  $quotedLessonCode;
    private string  $whereClause;
    private string  $masteryWhereClause;

    /**
     * StudentLessonsData constructor.
     * This will look to session to provide the studentCode or lessonCode whenever needed.
     * @throws PhonicsException on ill-formed SQL
     */
    public function __construct()
    {
        parent::__construct('abc_students', 'id');
        $this->session = new Session();
        $this->quotedStudentCode = $this->smartQuotes($this->session->getStudentCode());
        $this->quotedLessonCode = $this->smartQuotes($this->session->getCurrentLessonCode());
        $this->whereClause = "studentCode = {$this->quotedStudentCode} AND lessonCode = {$this->quotedLessonCode}";
        $this->masteryWhereClause = "studentCode = {$this->quotedStudentCode}";
    }


// ======================== PUBLIC METHODS =====================

    /**
     * @throws PhonicsException on ill-formed SQL
     */
    public function _create()
    {
        $query = <<<EOT
        CREATE TABLE `abc_student_lesson` (
            `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `studentCode` VARCHAR(32) NULL DEFAULT NULL,
            `lessonCode` VARCHAR(32) NOT NULL,
            `timePresented` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
            `masteryLevel` ENUM('none','advancing','mastered') NOT NULL DEFAULT 'none' COMMENT '0-none, 1-advancing, 2-mastered',
            `dateMastered` DATE NULL DEFAULT NULL,
            `fluencyTimes` VARCHAR(16) NOT NULL DEFAULT '' COMMENT 'each char is a hex value. Array of up to 16 entries',
            `testTimes` VARCHAR(16) NOT NULL DEFAULT '' COMMENT '16 hex digit entries',
            `dateLastPresented` DATE NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            INDEX `lessonCode` (`lessonCode`),
            INDEX `studentCode` (`studentCode`),
            CONSTRAINT `FK_lessonCode_studentLesson__lessons` FOREIGN KEY (`lessonCode`) 
                REFERENCES `abc_lessons` (`lessonCode`) ON UPDATE CASCADE ON DELETE CASCADE,
            CONSTRAINT `FK_student_has_lesson` FOREIGN KEY (`studentCode`) 
                REFERENCES `abc_students` (`studentCode`) ON UPDATE CASCADE ON DELETE SET NULL
        ) COMMENT='Used to track a students progress in a lesson' COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    /**
     * Clears for times for the Fluency or Test timer for the current lesson
     * @param TimerType $timerType
     * @throws PhonicsException on ill-formed SQL
     */
    public function clearTimedTest(TimerType $timerType): void
    {
        if ( ! $this->session->hasLesson()) {
            throw new PhonicsException('Attempt to update test time without a current lesson.');
        }
        $result = $this->updateField($timerType->getSqlFieldName(),'');
        if ($result->failed()) throw new PhonicsException($result->getErrorMessage());
    }

    /**
     * fetches the timer times for the specified timer type
     * @param TimerType $timerType
     * @return int[]
     * @throws PhonicsException on ill-formed SQL
     */
    public function getTimedTest(TimerType $timerType): array
    {
        $sqlFieldName = $timerType->getSqlFieldName();
        $this->createStudentLessonAsNeeded();
        return $this->getTimedArray($this->getField($sqlFieldName));
    }

    /**
     * @param mixed $value
     * @return void
     * @throws PhonicsException on ill-formed SQL
     */
    public function updateMastery($value): void
    {
        if ( ! $this->session->hasLesson()) {
            throw new PhonicsException('Attempt to update test time without a current lesson.');
        }
        $this->createStudentLessonAsNeeded();

        if (is_integer($value)) {
            $sqlValue = MasteryLevel::toSqlValue($value);
        } else if ($value instanceof MasteryLevel) {
            $sqlValue = $value->getValue();
        } else if (is_string($value) && MasteryLevel::isValid($value)) {
            $sqlValue = $value;
        } else {
            $sqlValue = 'none';
        }
        $where = $this->whereClause;
        $query = "UPDATE abc_student_lesson SET masteryLevel = '$sqlValue', dateMastered=NOW() WHERE $where";
        $this->throwableQuery($query, QueryType::AFFECTED_COUNT);
    }

    /**
     * Get the mastery results for the lessons the student has worked on - array of stdClass objects.
     * @return stdClass[]
     * @throws PhonicsException on ill-formed SQL
     */
    public function getLessonMastery(): array
    {
        if (! $this->session->hasStudent()) {
            return [];
        }
        $query = "SELECT *  FROM vw_lesson_mastery WHERE {$this->masteryWhereClause}";
        return $this->throwableQuery($query, QueryType::STDCLASS_OBJECTS);
    }

    /**
     * @param TimerType $timerType
     * @param int $seconds
     * @return BoolWithMessage
     * @throws PhonicsException on ill-formed SQL
     */
    public function updateTimedTest(TimerType $timerType, int $seconds): BoolWithMessage
    {
        if ($seconds == 0) {
            return BoolWithMessage::goodResult();
        }
        if ( ! $this->session->hasLesson()) {
            throw new PhonicsException('Attempt to update test time without a current lesson.');
        }

        $sqlFieldName = $timerType->getSqlFieldName();
        $this->createStudentLessonAsNeeded();

        $times = $this->getTimedArray($this->getField($sqlFieldName));
        $seconds = min($seconds, 99);
        $count = count($times);
        if ($count > 7) {
            $times = array_slice($times, -7, 7);
        }
        $times[] = $seconds;
        $newField = $this->setTimedField($times);
        return $this->updateField($sqlFieldName, $newField);
    }

// ======================== PRIVATE METHODS =====================

    /**
     * This gets run before a sql update to create the record if it doesn't already exist.
     * @throws PhonicsException on ill-formed SQL
     */
    private function createStudentLessonAsNeeded(): void
    {
        $query = "SELECT * FROM abc_student_lesson WHERE {$this->whereClause}";
        $count = $this->throwableQuery($query, QueryType::RECORD_COUNT);
        $student = $this->quotedStudentCode;
        $lesson = $this->quotedLessonCode;
        if ($count == 0) {
            $query = "INSERT INTO abc_student_lesson(studentCode, lessoncode) VALUES($student,$lesson)";
            $this->throwableQuery($query, QueryType::STATEMENT);
        }
    }

    /**
     * get the field value for the current student/lesson.
     * @param string $fieldName
     * @return mixed the value of the queried field.
     * @throws PhonicsException on ill-formed SQL
     */
    private function getField(string $fieldName)
    {
        $query = "SELECT $fieldName FROM abc_student_lesson WHERE {$this->whereClause}";
        return $this->throwableQuery($query, QueryType::SCALAR);
    }

    /**
     * convert the VARCHAR(16) timer field to an array of up to 8 times with a range of 1..99
     * @param string $fieldValuesString
     * @return array
     */
    private function getTimedArray(string $fieldValuesString): array
    {
        $result = [];
        for ($i = 0; $i < strlen($fieldValuesString); $i += 2) {
            $result[] = intval(substr($fieldValuesString, $i, 2));
        }
        return $result;
    }

    /**
     * convert an array of times to a string with each time 2 characters in length values '01'..'99'
     * @param array $times
     * @return string
     */
    private function setTimedField(array $times): string
    {
        $result = '';
        foreach ($times as $time) {
            $result .= Util::paddedNumber('', $time);
        }
        return $result;
    }

    /**
     * @param string $sqlFieldName
     * @param $value
     * @return BoolWithMessage
     * @throws PhonicsException on ill-formed SQL
     */
    private function updateField(string $sqlFieldName, $value): BoolWithMessage
    {
        $smartValue = $this->smartQuotes($value);
        $query = "UPDATE abc_student_lesson SET $sqlFieldName = $smartValue  WHERE {$this->whereClause}";
        $this->throwableQuery($query, QueryType::STATEMENT);
    }
}
