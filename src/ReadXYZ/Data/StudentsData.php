<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\ActiveType;
use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Enum\DbVersion;
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
            `userName` VARCHAR(100) NULL DEFAULT NULL,
            `studentName` VARCHAR(50) NOT NULL,
            `avatarFileName` VARCHAR(50) NOT NULL DEFAULT '',
            `dateCreated` DATE NOT NULL,
            `dateLastAccessed` DATE NOT NULL,
            `validUntilDate` DATE NULL DEFAULT NULL,
            `active` ENUM('Y','N') NOT NULL DEFAULT 'Y',
            PRIMARY KEY (`studentCode`),
            INDEX `fk_student__trainer` (`userName`),
            CONSTRAINT `fk_student__trainer` FOREIGN KEY (`userName`) 
                REFERENCES `abc_trainers` (`userName`) ON UPDATE CASCADE ON DELETE SET NULL
        ) COMMENT='Replaces abc_Student' COLLATE='utf8_general_ci' ENGINE=InnoDB;
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
        if (empty($studentCode)) $studentCode = uniqid('S', true);
        $studentCode = $this->smartQuotes($studentCode);
        $studentCode = str_replace('.', 'Z', $studentCode);
        $query = "INSERT INTO abc_students(studentCode, userName, studentName,  dateCreated , dateLastAccessed) VALUES($studentCode, '$userName', '$studentName', CURDATE(), CURDATE())";
        $result = $this->query($query, QueryType::STATEMENT);
        if ($result->wasSuccessful()) {
            $studentCode = $this->throwableQuery("SELECT LAST_INSERT_ID()", QueryType::SCALAR);
            return DbResult::goodResult($studentCode);
        } else {
            return $result;
        }
    }

    /**
     * @param string $studentCode
     * @return bool
     * @throws PhonicsException on ill-formed SQL
     */
    public function doesStudentExist(string $studentCode): bool
    {
        $active = ActiveType::IS_ACTIVE;
        $query = "SELECT * FROM abc_students WHERE studentCode = '$studentCode' AND active = '$active'";
        return $this->throwableQuery($query, QueryType::EXISTS);
    }

    /**
     * Gets the userName associated with a given user name. Returns empty string if not found
     * @param string $username
     * @param string $studentName
     * @return string the studentCode if found, otherwise 0
     * @throws PhonicsException on ill-formed SQL
     */
    public function getStudentCode(string $username, string $studentName): string
    {
        $query = "SELECT studentCode FROM abc_students WHERE StudentName = '$studentName' AND (username = '$username' ";
        return $this->throwableQuery($query, QueryType::SCALAR);
    }

    /**
     * @param string $studentCode
     * @return string
     * @throws PhonicsException on ill-formed SQL
     */
    public function getStudentName(string $studentCode): string
    {
        $query = "SELECT studentName FROM abc_students WHERE studentCode = '$studentCode'";
        return $this->throwableQuery($query, QueryType::SCALAR);
    }

    /**
     * @param string $user trainerCode or userName
     * @return string[] an array of studentNames
     * @throws PhonicsException on ill-formed SQL
     */
    public function getStudentNamesForUser(string $user): array
    {
        // We user the user of the current session if no user specified.
        if (empty($user)) {
            $session = new Session();
            $user = $session->getTrainerCode();
            if ( ! $user) {
                throw new PhonicsException("User cannot be empty when no user present in session.");
            }
        }
        $where = "(active = 'Y') AND (userName = '$user' OR trainerCode = '$user')";
        $query = "SELECT studentName FROM vw_students_with_username WHERE $where";
        return $this->throwableQuery($query, QueryType::SCALAR_ARRAY);
    }

    /**
     * @param string $user trainerCode or userName
     * @return array an associative array of studentName => studentCode
     * @throws PhonicsException on ill-formed SQL
     */
    public function getMapOfStudentsForUser($user = ''): array
    {
        // We user the user of the current session if no user specified.
        if (empty($user)) {
            $session = new Session();
            $user = $session->getTrainerCode();
            if ( ! $user) {
                throw new PhonicsException("User cannot be empty when no user present in session.");
            }
        }
        $active = ActiveType::IS_ACTIVE;
        $where = "(active = '$active') AND (userName = '$user' OR trainerCode = '$user')";
        $query = "SELECT studentCode, studentName FROM vw_students_with_username WHERE $where";
        $students = $this->throwableQuery($query, QueryType::STDCLASS_OBJECTS);
        $studentMap = [];
        foreach ($students as $student) {$studentMap[$student->studentName] = $student->studentCode;}
        return $studentMap;
    }

    /**
     * @param string $student
     * @param string $user
     * @return bool
     * @throws PhonicsException on ill-formed SQL
     */
    public function isValidStudentTrainerPair(string $student, string $user): bool
    {
        $active = ActiveType::IS_ACTIVE;
        $where = "studentCode = '$student' AND active = '$active' AND (userName = '$user' OR trainerCode = '$user')";
        $query = "SELECT * FROM vw_students_with_username WHERE $where";
        return $this->throwableQuery($query, QueryType::EXISTS);
    }

}
