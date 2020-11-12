<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Models\BoolWithMessage;
use App\ReadXYZ\Models\Session;
use phpDocumentor\Reflection\Types\Mixed_;
use RuntimeException;

class StudentLessonsData extends AbstractData
{

    private Session $session;

    public function __construct()
    {
        parent::__construct('abc_students');
        $this->session = new Session();
    }

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
	`fluencyTimes` VARCHAR(16) NOT NULL DEFAULT '0' COMMENT 'each char is a hex value. Array of up to 16 entries',
	`testTimes` VARCHAR(16) NOT NULL DEFAULT '0' COMMENT '16 hex digit entries',
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

    public function updateTimedTest(string $type, int $seconds): BoolWithMessage
    {
        if (!$this->session->hasLesson()) {
            throw new RuntimeException('Attempt to update test time without a current lesson.');
        }

        $field = ($type == 'fluency') ? 'fluencyTimes' : 'testTimes';
        $studentId = $this->session->getStudentId();
        $lessonCode =  $this->smartQuotes($this->session->getCurrentLessonCode());
        $whereClause = "WHERE studentId = $studentId AND lessonCode = $lessonCode";
        $this->createStudentLessonAsNeeded($whereClause);

        $times = $this->getTimedArray( $this->getField($field, $whereClause));
        $seconds = min($seconds, 99);
        $count = count($times);
        if ($count > 7) {
            $times = array_slice($times, -7, 7);
        }
        $times[] = $seconds;
        $newField = $this->setTimedField($times);
        return $this->updateField($field, $newField, $whereClause);

    }

    public function updateMastery($masteryValue): BoolWithMessage
    {
        if (!$this->session->hasLesson()) {
            throw new RuntimeException('Attempt to update test time without a current lesson.');
        }
        $studentId = $this->session->getStudentId();
        $lessonCode =  $this->smartQuotes($this->session->getCurrentLessonCode());
        $whereClause = "WHERE studentId = $studentId AND lessonCode = $lessonCode";
        $this->createStudentLessonAsNeeded($whereClause);

        $masteryIndex = max(0, min(2, $masteryValue));
        $enumArray = ['none', 'advancing', 'mastered'];
        $enumValue = $enumArray[$masteryIndex];
        $query = "UPDATE abc_student_lesson SET masteryLevel = '$enumValue', masteryDate=NOW() $whereClause";
        return $this->db->queryStatement($query);
    }

    public function createStudentLessonAsNeeded(string $whereClause): void
    {

        $query = "SELECT * FROM abc_student_lesson $whereClause";
        $result = $this->db->queryAndGetCount($query);
        if ($result->failed()) {
            throw new RuntimeException($result->getMessage() . '.  ' . $query);
        }
        if ($result->getResult() > 0) return;
    }

    public function getField(string $fieldName, string $whereClause)
    {
        $query = "SELECT $fieldName FROM abc_student_lesson $whereClause";
        $result = $this->db->queryAndGetScalar($query);
        if ($result->failed()) {
            throw new RuntimeException($result->getMessage() . '.  ' . $query);
        }
        return $result->getResult();
    }

    public function getTimedArray(string $field): array
    {
        $result = [];
        for ($i = 0; $i<strlen($field); $i += 2) {
            $result[] = intval(substr($field, $i, 2));
        }
        return $result;
    }

    public function setTimedField(array $times): string
    {
        $result = '';
        foreach($times as $time) {
            $result .= str_pad(strval($time), 2, '0', STR_PAD_LEFT);
        }
        return $result;
    }

    private function updateField(string $fieldName, $value, string $whereClause): BoolWithMessage
    {
        $smartValue = $this->smartQuotes($value);
        $query = "UPDATE abc_student_lesson SET $fieldName = $smartValue $whereClause";
        return $this->db->queryStatement($query);
    }
}
