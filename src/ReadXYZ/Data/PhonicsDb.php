<?php

namespace App\ReadXYZ\Data;

use App\ReadXYZ\Models\BoolWithMessage;
use mysqli;
use App\ReadXYZ\Secrets\Access;
use RuntimeException;

/**
 * Helper class for testing.
 */
class PhonicsDb
{
    protected mysqli $connection;

    public function __construct(int $version = 1)
    {
        $this->connection = ($version == 1) ? Access::dbConnect() : Access::oldDbConnect();
        if (mysqli_connect_errno()) {
            throw new RuntimeException(mysqli_connect_error());
        }
    }

    public function __destruct()
    {
        $this->connection->close();
    }

    /**
     * @param string $query
     * @return DbResult
     */
    public function queryAndGetCount(string $query): DbResult
    {
        if ($result = $this->connection->query($query)) {
            $count = $result->num_rows;
            $result->close();

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
            $row = $result->fetch_row();
            $returnValue = $row[0] ?? null;
            $result->close();

            return DbResult::goodResult($returnValue);
        } else {
            return DbResult::badResult($this->getErrorMessage());
        }
    }

    /**
     * returns the number of records affected by a delete, update or insert.
     *
     * @param string $query the query
     *
     * @return int if successful, the number of records affected, otherwise -1
     */
    public function queryAndGetAffectedCount(string $query): int
    {
        $count = -1;
        if ($result = $this->connection->query($query)) {
            $count = $this->connection->affected_rows;
        }

        return $count;
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
            $result->close();

            return DbResult::goodResult($result_array);
        } else {
            return DbResult::badResult($this->getErrorMessage());
        }
    }

    /**
     * If successful, return result as a DbResult which will contain an associative array or NULL (no match)
     * @param string $query
     * @return DbResult
     */
    public function queryRecord(string $query): DbResult
    {
        $result = $this->connection->query($query);
        if ($result === false) return DbResult::badResult($this->getErrorMessage());
        $row = $result->fetch_assoc();
        $result->close();
        return DbResult::goodResult($row);
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

    public function getErrorMessage(): string
    {
        return $this->connection->error;
    }

    /**
     * Gets the mysqli object associated with this PhonicsDb instance
     * @return mysqli
     */
    public function getConnection(): mysqli
    {
        return $this->connection;
    }

    public function getPreparedStatement(string $query)
    {
        $statement = $this->connection->prepare($query);
        if ($statement === false) {
            throw new RuntimeException($this->getErrorMessage());
        }
        return $statement;
    }
}
