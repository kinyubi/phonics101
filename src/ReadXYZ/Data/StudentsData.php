<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\ActiveType;
use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Enum\DbVersion;
use App\ReadXYZ\Enum\Regex;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Helpers\PhonicsException;

class StudentsData extends AbstractData
{
    public function __construct(string $dbVersion=DbVersion::READXYZ0_PHONICS)
    {
        parent::__construct('abc_students', 'studentCode', $dbVersion);
        $this->booleanFields = ['active'];
    }

// ======================== PUBLIC METHODS =====================

    /**
     * @throws PhonicsException on ill-formed SQL
     */
    public function _create()
    {
        $query = <<<EOT
CREATE TABLE `abc_students` (
	`studentCode` VARCHAR(32) NOT NULL,
	`userName` VARCHAR(100) NULL DEFAULT NULL COMMENT 'If the trainer is deleted, then username in this table is set to null. If the trainerCode is chainged in abc_trainer, then it will be updated here as well.',
	`studentName` VARCHAR(50) NOT NULL,
	`compositeCode` VARCHAR(100) NOT NULL,
	`avatarFileName` VARCHAR(50) NOT NULL DEFAULT '',
	`dateCreated` DATE NOT NULL,
	`dateLastAccessed` DATE NOT NULL,
	`validUntilDate` DATE NULL DEFAULT NULL,
	`active` ENUM('Y','N') NOT NULL DEFAULT 'Y',
	PRIMARY KEY (`studentCode`),
	INDEX `fk_student__trainer` (`userName`),
	CONSTRAINT `fk_student__trainer` FOREIGN KEY (`userName`) REFERENCES `abc_trainers` (`userName`) ON UPDATE CASCADE ON DELETE SET NULL
) COMMENT='Replaces abc_Student' COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    /**
     * inserts student record returning student code on good result
     * @param string $studentName
     * @param string $userName
     * @param string $studentCode optional, if not specified, it will generate one
     * @return DbResult good result contains studentCode of inserted record
     * @throws PhonicsException on ill-formed SQL
     */
    public function add(string $studentName, string $userName, string $studentCode=''): DbResult
    {
        $studentCode = $this->smartQuotes($this->newStudentCode($studentCode));
        $compositeCode = $this->smartQuotes($this->createCompositeCode($studentName, $userName));
        $query = "INSERT INTO abc_students(studentCode, userName, studentName, compositeCode, dateCreated , dateLastAccessed) VALUES($studentCode, '$userName', $compositeCode, '$studentName', CURDATE(), CURDATE())";
        $result = $this->query($query, QueryType::STATEMENT);
        if ($result->wasSuccessful()) {
            $studentCode = $this->throwableQuery("SELECT LAST_INSERT_ID()", QueryType::SCALAR);
            return DbResult::goodResult($studentCode);
        } else {
            return $result;
        }
    }

    /**
     * @param string $studentTag
     * @return bool
     * @throws PhonicsException on ill-formed SQL
     */
    public function doesStudentExist(string $studentTag): bool
    {
        $active = ActiveType::IS_ACTIVE;
        $where = "(studentCode = '$studentTag' OR compositeCode = '$studentTag') AND active = '$active'";
        $query = "SELECT * FROM abc_students WHERE $where";
        return $this->throwableQuery($query, QueryType::EXISTS);
    }

    /**
     * Gets the studentCode if the studentCode, compositeCode or
     * @param string $studentTag
     * @return string the studentCode if found, otherwise 0
     * @throws PhonicsException on ill-formed SQL
     */
    public function getStudentCode(string $studentTag): string
    {
        $where = "studentCode = '$studentTag' OR compositeCode = '$studentTag'";
        $query = "SELECT studentCode FROM abc_students WHERE $where ";
        return $this->throwableQuery($query, QueryType::SCALAR);
    }

    /**
     * @param string $studentTag
     * @return string
     * @throws PhonicsException on ill-formed SQL
     */
    public function getStudentName(string $studentTag): string
    {
        $where = "studentCode = '$studentTag' OR compositeCode = '$studentTag'";
        $query = "SELECT studentName FROM abc_students WHERE $where";
        return $this->throwableQuery($query, QueryType::SCALAR);
    }

    /**
     * generates a trainer code based on an abc_users.uuid or creates a new code if no uuid specified.
     * @param string $oldCode
     * @return string
     * @throws PhonicsException
     */
    public function newStudentCode(string $oldCode=''): string
    {
        if (empty($oldCode)) {
            return str_replace('.', 'Z', uniqid('S', true));
        } elseif (Regex::isValidOldStudentCodePattern($oldCode)) {
            return Util::oldUniqueIdToNew($oldCode);
        } elseif (Regex::isValidStudentCodePattern($oldCode)) {
            return $oldCode;
        } else {
            throw new PhonicsException("Invalid input. Should be empty or valid old abc_Student studentid.");
        }
    }

    private function createCompositeCode(string $studentName, string $userName): string
    {
        return $userName . '-' . $studentName;
    }
}
