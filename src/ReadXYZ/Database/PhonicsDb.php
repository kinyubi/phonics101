<?php

namespace ReadXYZ\Database;

use mysqli;
use ReadXYZ\Helpers\Util;

/**
 * Helper class for testing.
 */
class PhonicsDb
{
    protected mysqli $connection;

    public function __construct()
    {
        $this->connection = Util::dbConnect();
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

            return DbResult::GoodResult($count);
        } else {
            return DbResult::BadResult($this->connection);
        }
    }

    public function queryAndGetScalar(string $query): DbResult
    {
        $returnValue = null;
        if ($result = $this->connection->query($query)) {
            $row = $result->fetch_row();
            $returnValue = $row[0] ?? null;
            $result->close();

            return DbResult::GoodResult($returnValue);
        } else {
            return DbResult::BadResult($this->connection);
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
     * Given a query, returns an array of rows.
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

            return DbResult::GoodResult($result_array);
        } else {
            return DbResult::BadResult($this->connection);
        }
    }

    public function queryAndGetScalarArray(string $query): DbResult
    {
        $result_array = [];
        if ($result = $this->connection->query($query)) {
            while ($row = $result->fetch_array()) {
                $result_array[] = $row[0];
            }

            return DbResult::GoodResult($result_array);
        } else {
            return DbResult::BadResult($this->connection);
        }
    }

    public function queryStatement(string $query): bool
    {
        return false !== $this->connection->query($query);
    }

    public function getErrorMessage(): string
    {
        return $this->connection->error;
    }
}
