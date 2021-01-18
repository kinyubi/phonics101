<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\ActiveType;
use App\ReadXYZ\Enum\DbVersion;
use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Enum\Regex;
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

    public function __construct(string $dbVersion = DbVersion::READXYZ0_PHONICS)
    {
        parent::__construct('abc_trainers', 'userName', $dbVersion);
        $this->booleanFields = ['active'];
    }

// ======================== PUBLIC METHODS =====================

    /**
     * @throws PhonicsException on ill-formed SQL
     */
    public function _create(): void
    {
        $query = <<<EOT
CREATE TABLE `abc_trainers` (
	`userName` VARCHAR(100) NOT NULL COMMENT 'WordPress email/username',
	`displayName` VARCHAR(50) NOT NULL,
	`dateCreated` DATE NOT NULL,
	`dateModified` DATE NOT NULL,
	`dateLastAccessed` DATE NOT NULL,
	`active` ENUM('Y','N') NOT NULL DEFAULT 'Y',
	`trainerType` ENUM('reserve','parent','trainer','staff','admin') NULL DEFAULT NULL,
	`membershipValidTo` DATE NULL DEFAULT NULL,
	`hash` VARCHAR(128) NOT NULL,
	`trainerCode` VARCHAR(32) NOT NULL,
	PRIMARY KEY (`userName`),
	UNIQUE INDEX `trainerCode` (`trainerCode`)
) COMMENT='Replacement for abc_Users' COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $this->throwableQuery($query, QueryType::STATEMENT);
    }


    /**
     * @param string $userName
     * @param string $displayName
     * @param string $trainerCode
     * @param string $type
     * @param string $active
     * @return DbResult
     * @throws PhonicsException
     */
    public function add(
        string $userName,
        string $displayName = '',
        string $type = TrainerType::TRAINER,
        string $trainerCode = '',
        string $active = ActiveType::IS_ACTIVE
    ): DbResult
    {
        $userName = $this->smartQuotes($userName);
        $displayName = $this->smartQuotes($displayName);
        $type = $this->smartQuotes($type);
        $active = $this->smartQuotes($active);
        // generate an extended uniqId prefixed with U
        $trainerCode = $this->smartQuotes($this->newTrainerCode($trainerCode));
        $date        = $this->smartQuotes(Util::dbDate());
        $query       = <<<EOT
        INSERT INTO abc_trainers(userName,displayName,dateCreated,dateModified,dateLastAccessed,trainerType, trainerCode, active,hash)
        VALUES($userName, $displayName, $date, $date, $date, $type, $trainerCode, $active, '')
        ON DUPLICATE KEY UPDATE
        displayName = $displayName, dateModified = $date, dateLastAccessed = $date,
        trainerType = $type, active = $active
EOT;
        return $this->query($query, QueryType::STATEMENT);
    }


    /**
     * delete will fail if user has students
     * @param string|int $user userName or trainerCode
     * @return int
     * @throws PhonicsException on ill-formed SQL
     */
    public function delete($user): int
    {
        $where = "trainerCode = '$user' OR userName = '$user'";
        $query = "DELETE FROM abc_trainers WHERE $where";
        return $this->throwableQuery($query, QueryType::AFFECTED_COUNT);
    }

    /**
     * Returns true is trainer is in the database and is active.
     * @param $user
     * @return bool
     * @throws PhonicsException on ill-formed SQL
     */
    public function exists($user): bool
    {
        $where = "trainerCode = '$user' OR userName = '$user'";
        $query = "SELECT trainerType FROM abc_trainers WHERE  $where";
        return $this->throwableQuery($query, QueryType::EXISTS);
    }

    /**
     * @param string $user trainerCode or userName
     * @return ?object
     * @throws PhonicsException
     */
    public function get(string $user): ?object
    {
        $query = "SELECT * FROM abc_trainers WHERE userName = '$user' OR trainerCode = '$user'";
        return $this->throwableQuery($query, QueryType::SINGLE_OBJECT, Throwable::THROW_ON_NOT_FOUND);
    }

    /**
     * Retrieves hash associated with username. Returns empty string if username doesn't exist
     * @param string $userName
     * @return string hash if userName found, otherwise empty
     * @throws PhonicsException on ill-formed SQL
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
     * @throws PhonicsException on ill-formed SQL
     */
    public function getTrainerCode(string $user): string
    {
        $query = "SELECT trainerCode FROM abc_trainers WHERE userName = '$user' OR trainerCode = '$user'";
        return $this->throwableQuery($query, QueryType::SCALAR, Throwable::THROW_ON_NOT_FOUND);
    }

    /**
     * @param string $trainer
     * @return string
     * @throws PhonicsException on ill-formed SQL
     */
    public function getTrainerType(string $trainer): string
    {
        $query = "SELECT trainerType FROM abc_trainers WHERE trainerCode = '$trainer' OR userName = '$trainer'";
        return $this->throwableQuery($query, QueryType::SCALAR, Throwable::THROW_ON_NOT_FOUND);
    }

    /**
     * @param string $user
     * @return string
     * @throws PhonicsException on ill-formed SQL
     */
    public function getUsername(string $user): string
    {
        if (Util::contains('@', $user)) {
            return $user;
        }
        $query = "SELECT userName FROM abc_trainers WHERE trainerCode = '$user' OR userName = '$user'";
        return $this->throwableQuery($query, QueryType::SCALAR, Throwable::THROW_ON_NOT_FOUND);
    }

    /**
     * @param string $user userName or trainerCode
     * @return bool
     * @throws PhonicsException on ill-formed SQL
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
     * @throws PhonicsException on ill-formed SQL
     */
    public function isAdmin(string $user): bool
    {
        $where = "(trainerCode = '$user' OR userName = '$user')";
        $query = "SELECT trainerType FROM abc_trainers WHERE trainerType = 'admin' AND $where";
        return $this->throwableQuery($query, QueryType::EXISTS);
    }

    /**
     * true is trainerType is staff or admin
     * @param string $user userName or trainerCode
     * @return bool
     * @throws PhonicsException on ill-formed SQL
     */
    public function isStaff(string $user): bool
    {
        $where = "(trainerCode = '$user' OR userName = '$user')";
        $query = "SELECT trainerType FROM abc_trainers WHERE (trainerType = 'admin' OR trainerType = 'staff') AND $where";
        return $this->throwableQuery($query, QueryType::EXISTS);
    }

    /**
     * true is trainer is in the database and is active.
     * @param $user
     * @return bool
     * @throws PhonicsException on ill-formed SQL
     */
    public function isValid($user): bool
    {
        $where = "trainerCode = '$user' OR userName = '$user' AND active = 'Y'";
        $query = "SELECT trainerType FROM abc_trainers WHERE  $where";
        return $this->throwableQuery($query, QueryType::EXISTS);
    }

    /**
     * set trainer status to active
     * @param string|int $user userName or trainerCode
     * @param bool $activeOrNot
     * @throws PhonicsException on ill-formed SQL
     */
    public function updateActive($user, bool $activeOrNot): void
    {
        $where  = "trainerCode = '$user' OR userName = '$user'";
        $active = $activeOrNot ? 1 : 0;
        $query  = "UPDATE abc_trainers SET active=$active, dateModified=CURDATE(), dateLastAccessed=CURDATE() WHERE $where";
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    /**
     * update trainers display name
     * @param string $user userName or trainerCode
     * @param string $displayName
     * @throws PhonicsException on ill-formed SQL
     */
    public function updateName(string $user, string $displayName): void
    {
        $where = "trainerCode = '$user' OR userName = '$user'";
        $query = "UPDATE abc_trainers SET displayName='$displayName', dateModified=CURDATE() WHERE $where";
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    /**
     * generates a trainer code based on an abc_users.uuid or creates a new code if no uuid specified.
     * @param string $oldCode
     * @return string
     * @throws PhonicsException
     */
    public function newTrainerCode(string $oldCode=''): string
    {
        if (empty($oldCode)) {
            return str_replace('.', 'Z', uniqid('U', true));
        } elseif (Regex::isValidOldTrainerCodePattern($oldCode)) {
            return Util::oldUniqueIdToNew($oldCode);
        } elseif (Regex::isValidTrainerCodePattern($oldCode)) {
            return $oldCode;
        } else {
            throw new PhonicsException("Invalid input. Should be empty or valid old abc_users uuid.");
        }
    }
}
