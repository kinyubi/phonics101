<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Enum\Sql;
use App\ReadXYZ\Enum\Throwable;
use App\ReadXYZ\Enum\TrainerType;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;

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
        parent::__construct('abc_trainers', 'userName');
        $this->booleanFields = ['active'];
    }

// ======================== PUBLIC METHODS =====================

    /**
     * @throws PhonicsException
     */
    public function _create(): void
    {
        $query = <<<EOT
        CREATE TABLE `abc_trainers` (
            `userName` VARCHAR(100) NOT NULL COMMENT 'WordPress email/username',
            `firstName` VARCHAR(50) NOT NULL,
            `lastName` VARCHAR(50) NOT NULL,
            `dateCreated` DATE NOT NULL,
            `dateModified` DATE NOT NULL,
            `dateLastAccessed` DATE NOT NULL,
            `active` ENUM('Y','N') NOT NULL DEFAULT 'Y',
            `trainerType` ENUM('parent','trainer','admin') NULL DEFAULT NULL,
            `membershipValidTo` DATE NULL DEFAULT NULL,
            `hash` VARCHAR(128) NOT NULL,
            `trainerCode` VARCHAR(32) NOT NULL,
            PRIMARY KEY (`userName`),
            UNIQUE INDEX `trainerCode` (`trainerCode`)
        ) COMMENT='Replacement for abc_Users' COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    public function add(
        string $userName,
        string $password='',
        string $firstName='',
        string $lastName='',
        string $type=TrainerType::TRAINER
    ): DbResult
    {
        // generate an extended uniqId prefixed with U
        $trainerCode = uniqid('U', true);
        $trainerCode = str_replace('.', 'Z', $trainerCode);
        $date = $this->smartQuotes(Util::dbDate());
        $hash = $this->makeHash($userName, $password);
        $query = <<<EOT
        INSERT INTO abc_trainers(userName,firstName,lastName,dateCreated,dateModified,dateLastAccessed,trainerType,hash, trainerCode)
        VALUES('$userName', '$firstName', '$lastName', $date, $date, $date, '$type', '$hash', '$trainerCode')
EOT;
        return $this->query($query, QueryType::STATEMENT);
    }

    /**
     * delete will fail if user has students
     * @param string|int $user userName or trainerCode
     * @return int
     * @throws PhonicsException
     */
    public function delete($user): int
    {
        $where = "trainerCode = '$user' OR userName = '$user'";
        $query = "DELETE FROM abc_trainers WHERE $where";
        return $this->throwableQuery($query, QueryType::AFFECTED_COUNT);
    }

    /**
     * Retrieves hash associated with username. Returns empty string if username doesn't exist
     * @param string $userName
     * @return string hash if userName found, otherwise empty
     * @throws PhonicsException
     */
    public function getHash(string $userName): string
    {
        $query = "SELECT hash FROM abc_trainers WHERE userName = '$userName'";
        return $this->throwableQuery($query, QueryType::SCALAR);
    }

    /**
     * Retrieves trainerCode associated with username. Returns empty string if username doesn't exist
     * @param string $user
     * @return string trainerCode if userName found, otherwise empty
     * @throws PhonicsException
     */
    public function getTrainerCode(string $user): string
    {
        $query = "SELECT trainerCode FROM abc_trainers WHERE userName = '$user' OR trainerCode = '$user'";
        return $this->throwableQuery($query, QueryType::SCALAR, Throwable::THROW_ON_NOT_FOUND) ;
    }

    /**
     * @param string $user
     * @return string
     * @throws PhonicsException
     */
    public function getUsername(string $user): string
    {
        if (Util::contains('@', $user)) {return $user;}
        $query = "SELECT userName FROM abc_trainers WHERE trainerCode = '$user'";
        return $this->throwableQuery($query, QueryType::SCALAR, Throwable::THROW_ON_NOT_FOUND);
    }

    /**
     * @param string $trainer
     * @return string
     * @throws PhonicsException
     */
    public function getTrainerType(string $trainer): string
    {
        $query = "SELECT trainerType FROM abc_trainers WHERE trainerCode = '$trainer' OR userName = '$trainer'";
        return $this->throwableQuery($query, QueryType::SCALAR, Throwable::THROW_ON_NOT_FOUND);
    }

    /**
     * @param string $user userName or trainerCode
     * @return bool
     * @throws PhonicsException
     */
    public function hasStudents(string $user): bool
    {
        $where = "(trainerCode = '$user' OR userName = '$user')";
        $query = "SELECT * FROM abc_trainers WHERE $where";
        return $this->throwableQuery($query, QueryType::EXISTS);
    }

    /**
     * @param string $user userName or trainerCode
     * @return bool
     * @throws PhonicsException
     */
    public function isAdmin(string $user): bool
    {
        $where = "(trainerCode = '$user' OR userName = '$user')";
        $query = "SELECT trainerType FROM abc_trainers WHERE trainerType = 'admin' AND $where";
        return $this->throwableQuery($query, QueryType::EXISTS);
    }

    /**
     * @param string $user userName or trainerCode
     * @return bool
     * @throws PhonicsException
     */
    public function isStaff(string $user): bool
    {
        $where = "(trainerCode = '$user' OR userName = '$user')";
        $query = "SELECT trainerType FROM abc_trainers WHERE (trainerType = 'admin' OR trainerType = 'staff') AND $where";
        return $this->throwableQuery($query, QueryType::EXISTS);
    }

    /**
     * Returns true is trainer is in the database and is active.
     * @param $user
     * @return bool
     * @throws PhonicsException
     */
    public function isValid($user): bool
    {
        $where = "trainerCode = '$user' OR userName = '$user' AND active = 'Y'";
        $query = "SELECT trainerType FROM abc_trainers WHERE  $where";
        return $this->throwableQuery($query, QueryType::EXISTS);
    }

    /**
     * @param string|int $user userName or trainerCode
     * @param bool $activeOrNot
     * @throws PhonicsException
     */
    public function updateActive($user, bool $activeOrNot): void
    {
        $where = "trainerCode = '$user' OR userName = '$user'";
        $active = $activeOrNot ? 1 : 0;
        $query = "UPDATE abc_trainers SET active=$active, dateModified=NOW(), dateLastAccessed=NOW() WHERE $where";
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    /**
     * @param string $user userName or trainerCode
     * @param string $firstName
     * @param string $lastName
     * @throws PhonicsException
     */
    public function updateName(string $user, string $firstName, string $lastName): void
    {
        $where = "trainerCode = '$user' OR userName = '$user'";
        $query = "UPDATE abc_trainers SET firstName='$firstName', lastName='$lastName', dateModified=NOW() WHERE $where";
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    /**
     * @param string $user
     * @param string $password
     * @return void
     * @throws PhonicsException
     */
    public function updatePassword(string $user, string $password): void
    {
        $where = "trainerCode = '$user' OR userName = '$user'";
        $userName = (Util::isValidTrainerCode($user)) ? $this->getUsername($user) : $user;
        $hash = $this->makeHash($userName, $password);
        $query = "UPDATE abc_trainers SET hash='$hash', dateModified=NOW(), dateLastAccessed=NOW() WHERE $where";
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    public function verifyPassword(string $userName, string $password): bool
    {
        $calculatedHash = $this->makeHash($userName, $password);
        $actualHash = $this->getHash($userName);
        if (empty($actualHash)) {
            return false;
        }
        return $calculatedHash === $actualHash;
    }

// ======================== PRIVATE METHODS =====================

    /**
     * @param string $user userName or trainerCode
     * @param string $password
     * @return string
     * @throws PhonicsException
     */
    private function makeHash(string $user, string $password): string
    {
        $userName = (Util::isValidTrainerCode($user)) ? $this->getUsername($user) : $user;
        return password_hash($userName . '|' . $password, PASSWORD_BCRYPT, ['cost' => 8]);
    }

}
