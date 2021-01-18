<?php

namespace App\ReadXYZ\Data;

use App\ReadXYZ\Enum\DbVersion;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Models\BoolWithMessage;
use App\ReadXYZ\Secrets\Access;
use mysqli;
use mysqli_stmt;

/**
 * Helper class for testing.
 */
class PhonicsDb
{
    protected mysqli $connection;

    /**
     * PhonicsDb constructor.
     * @param string $dbName
     * @throws PhonicsException
     */
    public function __construct(string $dbName = DbVersion::READXYZ0_PHONICS)
    {
        switch ($dbName) {
            case DbVersion::READXYZ0_PHONICS:
                $this->connection = Access::dbConnect();
                break;
            case DbVersion::READXYZ1_1:
                $this->connection = Access::dbConnect11();
                break;
            case DbVersion::READXYZ0_1:
                $this->connection = Access::oldDbConnect();
                break;
            default:
                throw new PhonicsException("$dbName is not a valid database name.");
        }
        if (mysqli_connect_errno()) {
            throw new PhonicsException(mysqli_connect_error());
        }
    }

// ======================== PUBLIC METHODS =====================
    public function __destruct()
    {
        $this->connection->close();
    }

    /**
     * Gets the mysqli object associated with this PhonicsDb instance
     * @return mysqli
     */
    public function getConnection(): mysqli
    {
        return $this->connection;
    }

    public function getErrorMessage(): string
    {
        return $this->connection->error;
    }

    /**
     * @param string $query
     * @return mysqli_stmt
     * @throws PhonicsException
     */
    public function getPreparedStatement(string $query)
    {
        $statement = $this->connection->prepare($query);
        if ($statement === false) {
            throw new PhonicsException($this->getErrorMessage());
        }
        return $statement;
    }

    public function queryAndGetAffectedCount(string $query): DbResult
    {
        if ($result = $this->connection->query($query)) {
            $count = $this->connection->affected_rows;
            return DbResult::goodResult($count);
        }

        return DbResult::badResult($this->getErrorMessage());
    }

    /**
     * @param string $query
     * @return DbResult
     */
    public function queryAndGetCount(string $query): DbResult
    {
        if ($result = $this->connection->query($query)) {
            $count = $result->num_rows;

            return DbResult::goodResult($count);
        } else {
            return DbResult::badResult($this->getErrorMessage());
        }
    }

    /**
     * Handles a query that returns a single scalar value. Returns null ::goodResult if not found.
     * @param string $query
     * @return DbResult
     */
    public function queryAndGetScalar(string $query): DbResult
    {
        $returnValue = null;
        if ($result = $this->connection->query($query)) {
            $row         = $result->fetch_row();
            $returnValue = $row[0] ?? null;

            return DbResult::goodResult($returnValue);
        } else {
            return DbResult::badResult($this->getErrorMessage());
        }
    }

    /**
     * Handles queries returning a single field from one or more records in a simple array.
     * @param string $query
     * @return DbResult
     */
    public function queryAndGetScalarArray(string $query): DbResult
    {
        $result_array = [];
        if ($result = $this->connection->query($query)) {
            while ($row = $result->fetch_array()) {
                $result_array[] = $row[0];
            }

            return DbResult::goodResult($result_array);
        } else {
            return DbResult::badResult($this->getErrorMessage());
        }
    }

    public function queryObject(string $query): DbResult
    {
        $result = $this->queryRecord($query);
        if ($result->failed()) {
            return $result;
        }
        $record = $result->getResult();
        return DbResult::goodResult((object)$record);
    }

    public function queryObjects(string $query): DbResult
    {
        $result = $this->queryRows($query);
        if ($result->failed()) {
            return $result;
        }
        $objects = [];
        $records = $result->getResult();
        foreach ($records as $record) {
            $objects[] = (object)$record;
        }
        return DbResult::goodResult($objects);
    }

    /**
     * If successful, return result as a DbResult which will contain an associative array or NULL (no match)
     * @param string $query
     * @return DbResult
     */
    public function queryRecord(string $query): DbResult
    {
        $result = $this->connection->query($query);
        if ($result === false) {
            return DbResult::badResult($this->getErrorMessage());
        }
        $row = $result->fetch_assoc();
        return DbResult::goodResult($row);
    }

    /**
     * Given a query, returns an array of rows. Each row is an associative array of fieldName=>value
     *
     * @param string $query
     *
     * @return DbResult
     */
    public function queryRows(string $query): DbResult
    {
        $result_array = [];
        if ($result = $this->connection->query($query)) {
            while ($row = $result->fetch_assoc()) {
                $result_array[] = $row;
            }

            return DbResult::goodResult($result_array);
        } else {
            return DbResult::badResult($this->getErrorMessage());
        }
    }

    /**
     * Handles queries that add, update or delete.
     * @param string $query
     * @return BoolWithMessage
     */
    public function queryStatement(string $query): BoolWithMessage
    {
        $result = $this->connection->query($query);
        if ($result === false) {
            return BoolWithMessage::badResult($this->getErrorMessage());
        } else {
            return BoolWithMessage::goodResult();
        }
    }
}
