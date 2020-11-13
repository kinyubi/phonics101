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

    /**
     * @param string|int $user
     * @param string $password
     * @return BoolWithMessage
     */
    public function updatePassword($user, string $password): BoolWithMessage
    {
        $where = is_numeric($user) ? "trainerId = $user" : "userName = '$user'";
        $userName = is_numeric($user) ? $this->getUsername($user) : $user;
        $hash = $this->makeHash($userName, $password);
        $query = "UPDATE abc_trainers SET hash='$hash', dateModified=NOW(), dateLastAccessed=NOW() WHERE $where";
        return $this->db->queryStatement($query);
    }

    /**
     * @param string|int $user userName or trainerId
     * @param string $firstName
     * @param string $lastName
     * @return BoolWithMessage
     */
    public function updateName($user, string $firstName, string $lastName): BoolWithMessage
    {
        $where = is_numeric($user) ? "trainerId = $user" : "userName = '$user'";
        $query = "UPDATE abc_trainers SET firstName='$firstName', lastName='$lastName', dateModified=NOW() WHERE $where";
        $statement = $this->db->getPreparedStatement($query);
        $statement->bind_param('sssi', $firstName, $lastName, $date, $trainerId);
        $success = $statement->execute();
        return $success ? BoolWithMessage::goodResult() : BoolWithMessage::badResult($statement->error);
    }

    /**
     * delete will fail if user has students
     * @param string|int $user userName or trainerId
     * @return BoolWithMessage
     */
    public function delete($user): BoolWithMessage
    {
        $where = is_numeric($user) ? "trainerId = $user" : "userName = '$user'";
        $query = "DELETE FROM abc_trainers WHERE $where";
        return $this->db->queryStatement($query);
    }

    /**
     * @param string|int $user userName or trainerId
     * @return DbResult
     */
    public function hasStudents($user): DbResult
    {
        $where = is_numeric($user) ? "trainerId = $user" : "userName = '$user'";
        $query = "SELECT * FROM abc_trainers WHERE $where";
        $result = $this->db->queryAndGetCount($query);
        if ($result->failed()) {
            return DbResult::badResult($result->getMessage());
        } else {
            return DbResult::goodResult($result->getResult() > 0);
        }
    }

    /**
     * @param string|int $user userName or trainerId
     * @param bool $activeOrNot
     * @return BoolWithMessage
     */
    public function updateActive($user, bool $activeOrNot): BoolWithMessage
    {
        $where = is_numeric($user) ? "trainerId = $user" : "userName = '$user'";
        $active = $activeOrNot ? 1 : 0;
        $query = "UPDATE abc_trainers SET active=$active, dateModified=NOW(), dateLastAccessed=NOW() WHERE $where";
        return $this->db->queryStatement($query);
    }

    /**
     * @param string $userName
     * @param string $password
     * @return string
     */
    private function makeHash(string $userName, string $password): string
    {
        return password_hash($userName . '|' . $password, PASSWORD_BCRYPT, ['cost' => 8]);
    }

    /**
     * @param string|int $user userName or trainerId
     * @return bool
     */
    public function isAdmin($user)
    {
        $where = is_numeric($user) ? "trainerId = $user" : "userName = '$user'";
        $query = "SELECT trainerType FROM abc_trainers WHERE $where";
        $result = $this->db->queryAndGetScalar($query);
        return $result->wasSuccessful() && ($result->getResult() == 'admin');
    }

}
