<?php

namespace App\ReadXYZ\Data;

use App\ReadXYZ\Enum\Sql;
use App\ReadXYZ\Models\BoolWithMessage;
use mysqli;
use App\ReadXYZ\Secrets\Access;
use App\ReadXYZ\Helpers\PhonicsException;

/**
 * Helper class for testing.
 */
class PhonicsDb
{
    protected mysqli $connection;

    public function __construct(string $dbName=Sql::READXYZ1_1)
    {
        $this->connection = ($dbName == Sql::READXYZ1_1) ? Access::dbConnect() : Access::oldDbConnect();
        if (mysqli_connect_errno()) {
            throw new PhonicsException(mysqli_connect_error());
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


    public function queryAndGetAffectedCount(string $query): DbResult
    {
        if ($result = $this->connection->query($query)) {
            $count = $this->connection->affected_rows;
            $result->close();
            return DbResult::goodResult($count);
        }

        return DbResult::badResult($this->getErrorMessage());
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

    public function queryObjects(string $query): DbResult
    {
        $result = $this->queryRows($query);
        if ($result->failed()) return $result;
        $objects = [];
        $records = $result->getResult();
        foreach($records as $record) $objects[] = (object) $record;
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
        if ($result === false) return DbResult::badResult($this->getErrorMessage());
        $row = $result->fetch_assoc();
        $result->close();
        return DbResult::goodResult($row);
    }

    public function queryObject(string $query): DbResult
    {
        $result = $this->queryRecord($query);
        if ($result->failed()) return $result;
        $record = $result->getResult();
        return DbResult::goodResult((object) $record);
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
            throw new PhonicsException($this->getErrorMessage());
        }
        return $statement;
    }
}
