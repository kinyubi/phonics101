<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\DbVersion;
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
    private string  $quotedStudentCode;
    private string  $quotedLessonCode;
    private string  $whereClause;
    private string  $masteryWhereClause;
    private string  $hasLesson;

    /**
     * StudentLessonsData constructor.
     * This will look to session to provide the studentCode or lessonCode whenever needed.
     * @param string $studentCode   if empty, use Session's current studentCode.
     * @param string $lessonCode    if empty, use Session's current lessonCode
     * @param string $dbVersion     if empty, use readxyz0_phonics
     * @throws PhonicsException on ill-formed SQL
     */
    public function __construct(string $studentCode='', string $lessonCode='', string $dbVersion=DbVersion::READXYZ0_PHONICS)
    {
        parent::__construct('abc_students', 'id', $dbVersion);
        $this->jsonFields = ['fluencyTimes', 'testTimes'];
        if ($studentCode) {
            $this->quotedStudentCode = $this->smartQuotes($studentCode);
        } else {
            if (!Session::hasStudent()) {
                throw new PhonicsException('If student not specified, session needs to have a student.');
            }
            $this->quotedStudentCode = $this->smartQuotes(Session::getStudentCode());
        }
        if ($lessonCode) {
            $this->quotedLessonCode = $this->smartQuotes($lessonCode);
            $this->hasLesson = true;
        } else {
            if (Session::hasLesson()) {
                $this->quotedLessonCode = $this->smartQuotes(Session::getCurrentLessonCode());
                $this->hasLesson = true;
            } else {
                $this->hasLesson = false;
            }

        }
        $this->masteryWhereClause = "studentCode = {$this->quotedStudentCode}";

        if (! $this->hasLesson) {
            $this->whereClause = "lessonCode = 'There is no lesson code'";
        } else {
            $this->whereClause = "studentCode = {$this->quotedStudentCode} AND lessonCode = {$this->quotedLessonCode}";
        }
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
	`studentCode` VARCHAR(32) NULL DEFAULT NULL COMMENT 'If student is deleted, then related records in this table are deleted.',
	`lessonCode` VARCHAR(32) NOT NULL COMMENT 'If lesson is deleted, then related records in this table are deleted.',
	`masteryLevel` ENUM('none','advancing','mastered') NOT NULL DEFAULT 'none' COMMENT '0-none, 1-advancing, 2-mastered',
	`dateMastered` DATE NULL DEFAULT NULL,
	`fluencyTimes` MEDIUMTEXT NULL COMMENT 'each char is a hex value. Array of up to 16 entries',
	`testTimes` MEDIUMTEXT NULL COMMENT '16 hex digit entries',
	`dateLastPresented` DATE NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `lessonCode` (`lessonCode`),
	INDEX `studentCode` (`studentCode`),
	CONSTRAINT `FK_lessonCode_studentLesson__lessons` FOREIGN KEY (`lessonCode`) REFERENCES `abc_lessons` (`lessonCode`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_student_has_lesson` FOREIGN KEY (`studentCode`) REFERENCES `abc_students` (`studentCode`) ON UPDATE CASCADE ON DELETE CASCADE
) COMMENT='Used to track a students progress' COLLATE='utf8_general_ci' ENGINE=InnoDB AUTO_INCREMENT=6 ;
EOT;
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    /**
     * Clears for times for the Fluency or Test timer for the current lesson
     * @param TimerType|string $timerType
     * @throws PhonicsException on ill-formed SQL
     */
    public function clearTimedTest($timerType): void
    {
        if (is_string($timerType)) {$timerType = new TimerType($timerType);}
        $result = $this->updateField($timerType->getSqlFieldName(),$this->encodeJsonQuoted([]));
        if ($result->failed()) throw new PhonicsException($result->getErrorMessage());
    }

    /**
     * fetches the timer times for the specified timer type
     * @param TimerType|string $timerType
     * @return int[]
     * @throws PhonicsException on ill-formed SQL
     */
    public function getTimedTest($timerType): array
    {
        if (is_string($timerType)) {$timerType = new TimerType($timerType);}
        $sqlFieldName = $timerType->getSqlFieldName();
        $this->createStudentLessonAsNeeded();
        return $this->getField($sqlFieldName);
    }

    /**
     * @param mixed $value
     * @return void
     * @throws PhonicsException on ill-formed SQL
     */
    public function updateMastery($value): void
    {
        if (!$this->hasLesson) return; // if no lesson, there's nothing to do

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
        $query = "UPDATE abc_student_lesson SET masteryLevel = '$sqlValue', dateMastered=CURDATE() WHERE $where";
        $this->throwableQuery($query, QueryType::AFFECTED_COUNT);
    }

    /**
     * Get the mastery results for the lessons the student has worked on - array of stdClass objects.
     * @return stdClass[]
     * @throws PhonicsException on ill-formed SQL
     */
    public function getLessonMastery(): array
    {
        // This work whether or not we have a student
        $query = "SELECT * FROM vw_lesson_mastery WHERE {$this->masteryWhereClause}";
        return $this->throwableQuery($query, QueryType::STDCLASS_OBJECTS);
    }

    /**
     * @param TimerType|string $timerType
     * @param int $seconds
     * @param int $timeStamp
     * @return BoolWithMessage
     * @throws PhonicsException on ill-formed SQL
     */
    public function updateTimedTest($timerType, int $seconds, int $timeStamp=0): BoolWithMessage
    {
        if (! $this->hasLesson) {
            throw new PhonicsException('Cannot update timed test without a lesson.');
        }
        if ($timeStamp == 0) $timeStamp = time();
        if (is_string($timerType)) {$timerType = new TimerType($timerType);}
        if ($seconds == 0) {
            return BoolWithMessage::goodResult();
        }
        $seconds = min(99, $seconds);
        $sqlFieldName = $timerType->getSqlFieldName();
        $this->createStudentLessonAsNeeded();

        $times = $this->getField($sqlFieldName);
        $count = count($times);
        if ($count > 7) {
            $times = array_slice($times, -7, 7);
        }
        $last = end($times);
        $times[] = ['timestamp' => $timeStamp, 'seconds' => $seconds];
        if (($last !== false) && ($last->timestamp == $timeStamp)) {
            // do nothing. must have been a form resubmission.
            return BoolWithMessage::goodResult();
        }
        $newField = $this->encodeJsonQuoted($times);
        return $this->updateField($sqlFieldName, $newField);
    }


// ======================== PRIVATE METHODS =====================

    /**
     * This gets run before a sql update to create the record if it doesn't already exist.
     * @throws PhonicsException on ill-formed SQL
     */
    private function createStudentLessonAsNeeded(): void
    {
        if (! $this->hasLesson) {
            throw new PhonicsException('Cannot update timed test without a lesson.');
        }
        $query = "SELECT * FROM abc_student_lesson WHERE {$this->whereClause}";
        $count = $this->throwableQuery($query, QueryType::RECORD_COUNT);
        $student = $this->quotedStudentCode;
        $lesson = $this->quotedLessonCode;
        $emptyArray = $this->encodeJsonQuoted([]);
        $fields = 'studentCode, lessonCode, dateLastPresented,fluencyTimes,testTimes';
        $values = "$student,$lesson,CURDATE(),$emptyArray, $emptyArray";
        if ($count == 0) {
            $query = "INSERT INTO abc_student_lesson($fields) VALUES($values)";
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
        if (! $this->hasLesson) {
            throw new PhonicsException('Cannot update timed test without a lesson.');
        }
        $query = "SELECT $fieldName FROM abc_student_lesson WHERE {$this->whereClause}";
        $result = $this->throwableQuery($query, QueryType::SCALAR);
        if (Util::contains_ci('Times', $fieldName)) {
            if ($result == null) return [];
            return $this->decodeJson($result);
        } else {
            return $result;
        }
    }

    /**
     * @param string $fieldName
     * @param $value
     * @return BoolWithMessage
     * @throws PhonicsException on ill-formed SQL
     */
    private function updateField(string $fieldName, $value): BoolWithMessage
    {
        if (! $this->hasLesson) {
            throw new PhonicsException('Cannot update timed test without a lesson.');
        }
        $smartValue = (Util::contains_ci('Times', $fieldName)) ? $value : $this->smartQuotes($value);
        $query = <<<EOT
        UPDATE abc_student_lesson 
        SET $fieldName = $smartValue, dateLastPresented = CURDATE()  
        WHERE {$this->whereClause}
EOT;

        return $this->query($query, QueryType::STATEMENT)->toBoolWithMessage();
    }
}
