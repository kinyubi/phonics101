<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\MasteryLevel;
use App\ReadXYZ\Enum\TimerType;
use App\ReadXYZ\Models\BoolWithMessage;
use App\ReadXYZ\Models\Session;
use RuntimeException;

class StudentLessonsData extends AbstractData
{

    private Session $session;
    private int     $studentId;
    private string  $quotedLessonCode;
    private string  $whereClause;

    /**
     * StudentLessonsData constructor.
     * This will look to session to provide the studentId or lessonCode whenever needed.
     */
    public function __construct()
    {
        parent::__construct('abc_students');
        $this->session = new Session();
        $this->studentId = $this->session->getStudentId();
        $this->quotedLessonCode = $this->smartQuotes($this->session->getCurrentLessonCode());
        $this->whereClause = "studentId = {$this->studentId} AND lessonCode = {$this->quotedLessonCode}";
    }


// ======================== PUBLIC METHODS =====================
    public function create()
    {
        $query = <<<EOT
CREATE TABLE `abc_student_lesson` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`studentId` INT(10) UNSIGNED NOT NULL,
	`lessonCode` VARCHAR(32) NOT NULL,
	`timePresented` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	`masteryLevel` ENUM('none','advancing','mastered') NOT NULL DEFAULT 'none' COMMENT '0-none, 1-advancing, 2-mastered',
	`masteryDate` DATE NULL DEFAULT NULL,
	`fluencyTimes` VARCHAR(16) NOT NULL DEFAULT '' COMMENT 'each 2 char is a decimal value 0-99. Array of up to 8 entries',
	`testTimes` VARCHAR(16) NOT NULL DEFAULT '' COMMENT 'each 2 char is a decimal value 0-99. Array of up to 8 entries',
	`lastPresentedDate` DATE NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `studentId` (`studentId`),
	INDEX `FK_lessonId_student_lesson_lessons` (`lessonCode`),
	CONSTRAINT `FK_lessonCode_studentLesson__lessons` FOREIGN KEY (`lessonCode`) REFERENCES `abc_lessons` (`lessonCode`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_studentId_studentLesson__students` FOREIGN KEY (`studentId`) REFERENCES `abc_students` (`studentId`) ON UPDATE CASCADE ON DELETE CASCADE
) COMMENT='Used to track a students progress in a lesson' COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $result = $this->db->queryStatement($query);
        if ($result->failed()) {
            throw new RuntimeException($this->db->getErrorMessage());
        }
    }

    /**
     * Clears for times for the Fluency or Test timer for the current lesson
     * @param TimerType $timerType
     */
    public function clearTimedTest(TimerType $timerType): void
    {
        if ( ! $this->session->hasLesson()) {
            throw new RuntimeException('Attempt to update test time without a current lesson.');
        }
        $result = $this->updateField($timerType->getSqlFieldName(),'');
        if ($result->failed()) throw new RuntimeException($result->getErrorMessage());
    }

    /**
     * fetches the timer times for the specified timer type
     * @param TimerType $timerType
     * @return int[]
     */
    public function getTimedTest(TimerType $timerType): array
    {
        $sqlFieldName = $timerType->getSqlFieldName();
        $this->createStudentLessonAsNeeded();
        return $this->getTimedArray($this->getField($sqlFieldName));
    }

    /**
     * @param MasteryLevel $masteryLevel 'none'|0, 'advancing'|1, 'mastered'|2
     * @return BoolWithMessage
     */
    public function updateMastery(MasteryLevel $masteryLevel): BoolWithMessage
    {
        if ( ! $this->session->hasLesson()) {
            throw new RuntimeException('Attempt to update test time without a current lesson.');
        }
        $this->createStudentLessonAsNeeded();

        $sqlEnumValue = $masteryLevel->getSqlValue();
        $where = $this->whereClause;
        $query = "UPDATE abc_student_lesson SET masteryLevel = '$sqlEnumValue', masteryDate=NOW() WHERE $where";
        return $this->db->queryStatement($query);
    }

    /**
     * @param TimerType $timerType
     * @param int $seconds
     * @return BoolWithMessage
     */
    public function updateTimedTest(TimerType $timerType, int $seconds): BoolWithMessage
    {
        if ($seconds == 0) {
            return BoolWithMessage::goodResult();
        }
        if ( ! $this->session->hasLesson()) {
            throw new RuntimeException('Attempt to update test time without a current lesson.');
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
     */
    private function createStudentLessonAsNeeded(): void
    {
        $query = "SELECT * FROM abc_student_lesson WHERE {$this->whereClause}";
        $result = $this->db->queryAndGetCount($query);
        if ($result->failed()) {
            throw new RuntimeException($result->getMessage() . '.  ' . $query);
        }
        if ($result->getResult() > 0) {
            return;
        }
    }

    private function getField(string $fieldName)
    {
        $query = "SELECT $fieldName FROM abc_student_lesson WHERE {$this->whereClause}";
        $result = $this->db->queryAndGetScalar($query);
        if ($result->failed()) {
            throw new RuntimeException($result->getMessage() . '.  ' . $query);
        }
        return $result->getResult();
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
            $result .= str_pad(strval($time), 2, '0', STR_PAD_LEFT);
        }
        return $result;
    }

    private function updateField(string $sqlFieldName, $value): BoolWithMessage
    {
        $smartValue = $this->smartQuotes($value);
        $query = "UPDATE abc_student_lesson SET $sqlFieldName = $smartValue  WHERE {$this->whereClause}";
        return $this->db->queryStatement($query);
    }
}
