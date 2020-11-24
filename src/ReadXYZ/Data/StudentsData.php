<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Models\Session;
use RuntimeException;

class StudentsData extends AbstractData
{
    public function __construct()
    {
        parent::__construct('abc_students', 'studentCode');
        $this->booleanFields = ['active'];
    }

// ======================== PUBLIC METHODS =====================
    public function _create()
    {
        $query = <<<EOT
        CREATE TABLE `abc_students` (
            `studentCode` VARCHAR(32) NOT NULL,
            `userName` VARCHAR(100) NULL DEFAULT NULL,
            `studentName` VARCHAR(50) NOT NULL,
            `avatarFileName` VARCHAR(50) NOT NULL DEFAULT '',
            `createdDate` DATE NOT NULL,
            `lastAccessedDate` DATE NOT NULL,
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

    public function add(string $studentName, string $userName): DbResult
    {
        $studentCode = $this->smartQuotes(uniqid('S', true));
        $query = "INSERT INTO abc_students(studentCode, userName, studentName,  createdDate, lastAccessedDate) VALUES($studentCode, '$userName', '$studentName', NOW(), NOW())";
        return $this->query($query, QueryType::STATEMENT);
    }

    public function doesStudentExist(string $studentCode): bool
    {
        $query = "SELECT * FROM abc_students WHERE studentCode = '$studentCode' AND active = 'y'";
        return $this->throwableQuery($query, QueryType::EXISTS);
    }

    /**
     * Gets the userName associated with a given user name. Returns empty string if not found
     * @param string $username
     * @param string $studentName
     * @return string the studentCode if found, otherwise 0
     */
    public function getStudentCode(string $username, string $studentName): string
    {
        $query = "SELECT studentCode FROM abc_students WHERE StudentName = '$studentName' AND (username = '$username' ";
        return $this->throwableQuery($query, QueryType::SCALAR);
    }

    public function getStudentName(string $studentCode): string
    {
        $query = "SELECT studentName FROM abc_students WHERE studentCode = '$studentCode'";
        return $this->throwableQuery($query, QueryType::SCALAR);
    }

    /**
     * @param string $user trainerCode or userName
     * @return string[] an associative array of studentCode, studentName
     */
    public function getStudentNamesForUser(string $user): array
    {
        // We user the user of the current session if no user specified.
        if (empty($user)) {
            $session = new Session();
            $user = $session->getTrainerCode();
            if ( ! $user) {
                throw new RuntimeException("User cannot be empty when no user present in session.");
            }
        }
        $where = "(active = 'Y') AND (userName = '$user' OR trainerCode = '$user')";
        $query = "SELECT studentName FROM vw_students_with_username WHERE $where";
        return $this->throwableQuery($query, QueryType::SCALAR_ARRAY);
    }

    /**
     * @param string|int $user trainerCode or userName
     * @return array an associative array of studentCode, studentName
     */
    public function getStudentsForUser($user = 0): array
    {
        // We user the user of the current session if no user specified.
        if (empty($user)) {
            $session = new Session();
            $user = $session->getTrainerCode();
            if ( ! $user) {
                throw new RuntimeException("User cannot be empty when no user present in session.");
            }
        }
        $where = "(active = 'Y') AND (userName = '$user' OR trainerCode = '$user')";
        $query = "SELECT studentCode, studentName FROM vw_students_with_username WHERE $where";
        return $this->throwableQuery($query, QueryType::ASSOCIATIVE_ARRAY);
    }

    public function isValidStudentTrainerPair(string $student, string $user): bool
    {
        $where = "studentCode = '$student' AND active = 'y' AND (userName = '$user' OR trainerCode = '$user')";
        $query = "SELECT * FROM abc_students WHERE $where";
        return $this->throwableQuery($query, QueryType::EXISTS);
    }

}
