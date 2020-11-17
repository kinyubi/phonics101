<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\RecordType;
use App\ReadXYZ\Models\BoolWithMessage;
use RuntimeException;

abstract class AbstractData
{
    protected PhonicsDb $db;
    protected string $tableName;



    public function __construct(string $tableName)
    {
        // header is required because sendResponse fails without it
        $this->db = new PhonicsDb();
        $this->tableName = $tableName;
    }

    public function getCount(): int
    {
        $result = $this->db->queryAndGetCount("SELECT * FROM {$this->tableName}");
        return $result->wasSuccessful() ? $result->getResult() : 0;
    }

    public function truncate(): BoolWithMessage
    {
        if (runningStandalone()) {
            return $this->db->queryStatement("DELETE FROM {$this->tableName}");
        } else {
            throw new RuntimeException('May only be called in standalone environment.');
        }

    }

    /**
     * smart quotes for JSON object
     * @param $object
     * @return string suitable for use as the value of a mysql JSON field
     */
    protected function encodeJsonQuoted($object)
    {
        if ($object == null) return "NULL";
        $encode = json_encode($object, JSON_UNESCAPED_SLASHES);
        $fixed = str_replace("'", "\\'", $encode);
        return "'" . $fixed . "'";
    }

    /**
     * @param int $http_code  the http code we want the response to send
     * @param string $msg     the message we want the response to return (default: OK)
     */
    protected function sendResponse(int $http_code = 200, string $msg = 'OK'): void
    {
        header('Content-Type: application/json');
        http_response_code($http_code);
        echo json_encode(['code' => $http_code, 'msg' => $msg]);
    }

    public function smartQuotes($value): string
    {

        if (!is_numeric($value) || '0' == $value[0]) {
            $value = "'" . $this->db->getConnection()->real_escape_string($value) . "'";
        }

        return $value;
    }

    public function query(string $query, RecordType $recordType): DbResult
    {
        switch ($recordType->getValue()) {
            case RecordType::ASSOCIATIVE_ARRAY:
                return $this->db->queryRows($query);
            case RecordType::STDCLASS_OBJECTS:
                return $this->db->queryObjects($query);
            case RecordType::SCALAR:
                return $this->db->queryAndGetScalar($query);
            case RecordType::RECORD_COUNT:
                return $this->db->queryAndGetCount($query);
            case RecordType::SCALAR_ARRAY:
                return $this->db->queryAndGetScalarArray($query);
            case RecordType::SINGLE_RECORD:
                return $this->db->queryRecord($query);
            case RecordType::SINGLE_OBJECT:
                return $this->db->queryObject($query);
            case RecordType::AFFECTED_COUNT:
                return $this->db->queryAndGetAffectedCount($query);
            case RecordType::STATEMENT:
                $result = $this->db->queryStatement($query);
                if ($result->wasSuccessful()) {
                    return DbResult::goodResult(1);
                } else {
                    return DbResult::badResult($result->getErrorMessage());
                }
            default:
                return DbResult::badResult($recordType->getValue() . ' is not a valid record type.');
        }
    }

    /**
     * executes the query, returns the result or throws if query failed or found no records.
     * @param string $query
     * @param RecordType $recordType
     * @return mixed
     */
    public function throwableQuery(string $query, RecordType $recordType)
    {
        $result = $this->query($query, $recordType);
        if ($result->wasSuccessful()) {
            $goodResult = $result->getResult();
            switch($recordType->getValue()) {
                case RecordType::ASSOCIATIVE_ARRAY:
                case RecordType::STDCLASS_OBJECTS:
                case RecordType::SCALAR_ARRAY:
                    if (count($goodResult) == 0) {
                        throw new RuntimeException('Query found no records.');
                    } else {
                        return $goodResult;
                    }
                case RecordType::SCALAR:
                case RecordType::SINGLE_RECORD:
                case RecordType::SINGLE_OBJECT:
                    if ($goodResult == null) {
                        throw new RuntimeException('Query found no records.');
                    } else {
                        return $goodResult;
                    }

                case RecordType::RECORD_COUNT:
                case RecordType::AFFECTED_COUNT:
                    return $goodResult;
                default:
                    return DbResult::badResult($recordType->getValue() . ' is not a valid record type.');
            }
        } else {
            throw new RuntimeException($result->getErrorMessage());
    }
}

}
