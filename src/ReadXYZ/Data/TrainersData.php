<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\BoolWithMessage;
use RuntimeException;

class TrainersData extends AbstractData
{
    // from AbstractData:
    //   PhonicsDb  $db
    //   string     $tableName
    //
    //   __construct(string $tableName)
    //   getCount(): int
    //   truncate()
    //   smartQuotes($value): string
    //   sendResponse(int $httpCode, string $msg)

    public function __construct()
    {
        parent::__construct('abc_trainers');
    }

    public function create(): void
    {
        $query = <<<EOT
CREATE TABLE `abc_trainers` (
	`trainerId` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`userName` VARCHAR(100) NOT NULL COMMENT 'WordPress email/username',
	`firstName` VARCHAR(50) NOT NULL,
	`lastName` VARCHAR(50) NOT NULL,
	`dateCreated` DATE NOT NULL,
	`dateModified` DATE NOT NULL,
	`dateLastAccessed` DATE NOT NULL,
	`active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`trainerType` ENUM('parent','trainer','admin') NULL DEFAULT NULL,
	`membershipValidTo` DATE NULL DEFAULT NULL,
	`hash` VARCHAR(128) NOT NULL,
	PRIMARY KEY (`trainerId`),
	UNIQUE INDEX `userName` (`userName`)
) COMMENT='Replacement for abc_Users' COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $result = $this->db->queryStatement($query);
        if ($result->failed()) {
            throw new RuntimeException($this->db->getErrorMessage());
        }

    }

    /**
     * Retrieves trainerId associated with username. Returns empty string if username doesn't exist
     * @param string $userName
     * @return int trainerId if userName found, otherwise empty
     */
    public function getTrainerId(string $userName): int
    {
        $query = "SELECT trainerId FROM abc_trainers WHERE userName = '$userName'";
        $result = $this->db->queryAndGetScalar($query);
        return $result->wasSuccessful() ? (int) $result->getResult() : 0;

    }

    public function getUsername(int $trainerId): string
    {
        $query = "SELECT userName FROM abc_trainers WHERE trainerId = $trainerId";
        $result = $this->db->queryAndGetScalar($query);
        return $result->wasSuccessful() ? (int) $result->getResult() : 0;
    }

    /**
     * Retrieves hash associated with username. Returns empty string if username doesn't exist
     * @param string $userName
     * @return string hash if userName found, otherwise empty
     */
    public function getHash(string $userName): string
    {
        $query = "SELECT hash FROM abc_trainers WHERE userName = '$userName'";
        $result = $this->db->queryAndGetScalar($query);
        return $result->wasSuccessful() ? $result->getResult() ?? '' : '';

    }

    public function verifyPassword(string $userName, string $password): bool
    {
        $calculatedHash = $this->makeHash($userName, $password);
        $actualHash =  $this->getHash($userName);
        if (empty($actualHash)) return false;
        return $calculatedHash === $actualHash;
    }

    public function add(string $userName, string $firstName, string $lastName, string $type, string $password): BoolWithMessage
    {
        // carlbaker@gmail.com pwd=readxyz
        $date = Util::dbDate();
        $trainerId = $this->getTrainerId($userName);
        if ($trainerId == 0) {
            return BoolWithMessage::badResult("$userName already exists");
        }
        $validType = in_array($type, ['parent', 'trainer', 'admin']);
        if (not($validType)) {
            return BoolWithMessage::badResult("'$type' is not a valid trainer type.");
        }
        $hash = $this->makeHash($userName, $password);

        $query = 'INSERT INTO abc_trainers(userName,firstName,lastName,dateCreated,dateModified,dateLastAccessed,trainerType,hash) VALUES(?,?,?,?,?,?,?,?)';
        $statement = $this->db->getPreparedStatement($query);
        $statement->bind_param('ssssssss', $userName, $$firstName, $lastName, $date, $date, $date, $type, $hash);
        $success = $statement->execute();
        $result = $success ? BoolWithMessage::goodResult() : BoolWithMessage::badResult($statement->error);
        $statement->close();
        return $result;

    }

    public function updatePassword(string $userName, string $password): BoolWithMessage
    {
        $date = Util::dbDate();
        $trainerId = $this->getTrainerId($userName);
        $hash = $this->makeHash($userName, $password);
        $query = 'UPDATE abc_trainers SET hash=?, dateModified=? WHERE trainerId=?';
        $statement = $this->db->getPreparedStatement($query);
        $statement->bind_param('ssi', $hash, $date, $trainerId);
        $success = $statement->execute();
        return $success ? BoolWithMessage::goodResult() : BoolWithMessage::badResult($statement->error);
    }

    public function updateName(string $userName, string $firstName, string $lastName): BoolWithMessage
    {
        $date = Util::dbDate();
        $trainerId = $this->getTrainerId($userName);
        $query = 'UPDATE abc_trainers SET firstName=?, lastName=?, dateModified=? WHERE trainerId=?';
        $statement = $this->db->getPreparedStatement($query);
        $statement->bind_param('sssi', $firstName, $lastName, $date, $trainerId);
        $success = $statement->execute();
        return $success ? BoolWithMessage::goodResult() : BoolWithMessage::badResult($statement->error);
    }

    /**
     * delete will fail if user has students
     * @param string $userName
     * @return BoolWithMessage
     */
    public function delete(string $userName): BoolWithMessage
    {
        $trainerId = $this->getTrainerId($userName);
        $query = 'DELETE FROM abc_trainers WHERE trainerId=?';
        $statement = $this->db->getPreparedStatement($query);
        $statement->bind_param('i', $trainerId);
        $success = $statement->execute();
        return $success ? BoolWithMessage::goodResult() : BoolWithMessage::badResult($statement->error);
    }

    public function hasStudents($userName): DbResult
    {
        $trainerId = $this->getTrainerId($userName);
        if ($trainerId == 0) return DbResult::badResult("Username '$userName' does not exist.");

        $result = $this->db->queryAndGetCount("SELECT * FROM abc_trainers WHERE trainerId=$trainerId");
        if ($result->failed()) {
            return DbResult::badResult($result->getMessage());
        } else {
            return DbResult::goodResult($result->getResult() > 0);
        }
    }

    public function updateActive(string $userName, bool $activeOrNot): BoolWithMessage
    {
        $date = Util::dbDate();
        $isActive = $activeOrNot ? 1 : 0;
        $trainerId = $this->getTrainerId($userName);
        $query = 'UPDATE abc_trainers SET active=? WHERE trainerId=?';
        $statement = $this->db->getPreparedStatement($query);
        $statement->bind_param('isi', $isActive,  $date, $trainerId);
        $success = $statement->execute();
        return $success ? BoolWithMessage::goodResult() : BoolWithMessage::badResult($statement->error);
    }

    private function makeHash(string $userName, string $password): string
    {
        return password_hash($userName . '|' . $password, PASSWORD_BCRYPT, ['cost' => 8]);
    }

}
