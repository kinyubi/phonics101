<?php


namespace App\ReadXYZ\Data;


use RuntimeException;

class StudentsData extends AbstractData
{
    public function __construct()
    {
        parent::__construct('abc_students');
    }

    public function create()
    {
        $query = <<<EOT
CREATE TABLE `abc_students` (
	`studentId` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`trainerId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`studentName` VARCHAR(50) NOT NULL,
	`avatarFileName` VARCHAR(50) NOT NULL DEFAULT '',
	`createdDate` DATE NOT NULL,
	`lastAccessedDate` DATE NOT NULL,
	`validUntilDate` DATE NULL DEFAULT NULL,
	`active` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
	PRIMARY KEY (`studentId`),
	INDEX `FK_trainerId_students__trainers` (`trainerId`),
	CONSTRAINT `FK_trainerId_students__trainers` FOREIGN KEY (`trainerId`) REFERENCES `abc_trainers` (`trainerId`) ON UPDATE CASCADE ON DELETE SET NULL
) COMMENT='Replaces abc_Student' COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;

        $result = $this->db->queryStatement($query);
        if ($result->failed()) {
            throw new RuntimeException($this->db->getErrorMessage());
        }
    }

    /**
     * Gets the userId associated with a given user name. Returns empty string if not found
     * @param string $username
     * @param string $studentName
     * @return string the userId if found, otherwise the empty string
     */
    public function getStudentId(string $username, string $studentName): string
    {
        $query = "SELECT studentid FROM abc_students WHERE StudentName = '$studentName' AND trainerId = '$username'";
        $result = $this->db->queryAndGetScalar($query);
        if ($result->wasSuccessful()) return $result->getResult() ?? '';
        throw new RuntimeException('Error: ' . $result->getMessage() . '. ' . $query);
    }

    public function doesStudentExist(int $studentId): bool
    {
        $query = "SELECT * FROM abc_students WHERE studentId = $studentId";
        $result = $this->db->queryAndGetCount($query);
        return $result->wasSuccessful() && ($result->getResult() > 0);
    }

    public function getStudentName(string $studentId): string
    {
        $query = "SELECT studentName FROM abc_students WHERE studentId = '$studentId'";
        $result = $this->db->queryAndGetScalar($query);
        if ($result->wasSuccessful()) return $result->getResult() ?? '';
        throw new RuntimeException('Error: ' . $result->getMessage() . '. ' . $query);
    }

    public function getStudentsForUserName(string $username): array
    {
        $query = "SELECT studentId, studentName FROM vw_students_with_username WHERE userName = '$username'";
        $result = $this->db->queryAndGetScalar($query);
        if ($result->wasSuccessful()) return $result->getResult() ?? '';
        throw new RuntimeException('Error: ' . $result->getMessage() . '. ' . $query);
    }

    public function isValidStudentTrainerPair(string $student, string $user): bool
    {
        if (is_numeric($student) && is_numeric($user)) {
            $query = "SELECT * FROM abc_students WHERE studentId = $student AND trainerId = $user";
        } else if (is_string($student) && is_string($user)) {
            $query = "SELECT * FROM vw_students_with_username WHERE userName='$user' AND studentName='$student'";
        } else {
            throw new RuntimeException("Inputs must both be numeric or both be string.");
        }
        $result = $this->db->queryAndGetCount($query);
        if ($result->failed()) {
            throw new RuntimeException($result->getMessage());
        }
        return $result->getResult();
    }

}
