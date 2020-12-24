<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\ActiveType;
use App\ReadXYZ\Enum\BoolEnumTreatment;
use App\ReadXYZ\Enum\DbVersion;
use App\ReadXYZ\Enum\KeyChecks;
use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Enum\Throwable;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;
use Exception;
use stdClass;

abstract class AbstractData
{
    protected PhonicsDb $db;
    protected string    $tableName;
    protected string    $primaryKey;
    protected array     $booleanFields = [];
    protected array     $jsonFields    = [];
    protected string    $dbName;

    /**
     * AbstractData constructor.
     * @param string $tableName used by deleteOne, updateOne, getCount and truncate
     * @param string $primaryKey used by deleteOne and updateOne
     * @param string $dbVersion specifies using readxyz0_phonics (1 or default) or readxyz0_1 (0)
     * @throws PhonicsException on ill-formed SQL
     */
    public function __construct(string $tableName, string $primaryKey = 'id', string $dbVersion = DbVersion::READXYZ0_PHONICS)
    {
        // header is required because sendResponse fails without it
        if ( ! DbVersion::isValid($dbVersion) || ! Util::startsWith('read', $dbVersion)) {
            throw new PhonicsException("$dbVersion is not a valid database version.");
        }
        $this->db         = new PhonicsDb($dbVersion);
        $this->tableName  = $tableName;
        $this->primaryKey = $primaryKey;
        $this->dbName     = $dbVersion;
    }

// ======================== PUBLIC METHODS =====================

    /**
     * @param stdClass $object
     * @return array|false|int|mixed|stdClass|string|null
     * @throws PhonicsException
     */
    public function addObject(stdClass $object)
    {
        $array       = (array)$object;
        $fields      = [];
        $smartValues = [];
        $assigns     = [];

        foreach ($array as $field => $value) {
            $fields[]      = $field;
            $smartValue    = $this->smartQuotes($value);
            $smartValues[] = $smartValue;
            if ($this->primaryKey != $field) {
                $assigns[] = "$field = $smartValue";
            }
        }
        $query = "INSERT INTO {$this->tableName}(" . join(',', $fields) . ")\nVALUES(";
        $query .= join(',', $smartValues) . ")\nON DUPLICATE KEY UPDATE\n" . join(',', $assigns);
        return $this->throwableQuery($query, QueryType::AFFECTED_COUNT, BoolEnumTreatment::KEEP_AS_Y_N);
    }

    /**
     * @param mixed $value
     * @return string
     * @throws PhonicsException on ill-formed SQL
     */
    public function boolToEnum($value): string
    {
        if (is_null($value)) {
            return 'F';
        }
        if (is_string($value)) {
            if ($value == ActiveType::IS_ACTIVE || $value == ActiveType::IS_INACTIVE) {
                return $value;
            } else {
                throw new PhonicsException("$value is not a valid value for Active");
            }
        } else {
            if (is_bool($value)) {
                return $value ? 'Y' : 'N';
            } else {
                throw new PhonicsException("$value must be 'Y', 'N' , true or false");
            }
        }
    }

    /**
     * @param string|null $json
     * @return mixed|null
     */
    public function decodeJson(?string $json)
    {
        return ($json != null) ? json_decode($json) : null;
    }

    /**
     * Delete a single record whose primary key matches the specified value
     * @param mixed $keyValue
     * @throws PhonicsException on ill-formed SQL
     */
    public function deleteOne($keyValue): void
    {
        $smartValue = $this->smartQuotes($keyValue);
        $query      = "DELETE FROM {$this->tableName} WHERE {$this->primaryKey} = $smartValue";
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    /**
     * We expect $enum to be ActiveType::IS_ACTIVE or ActiveType::IS_INACTIVE but we'll accept a bool value as well
     * @param $enum
     * @return bool
     * @throws PhonicsException on ill-formed SQL
     */
    public function enumToBool($enum): bool
    {
        if (is_null($enum)) {
            return false;
        }
        if (is_string($enum)) {
            if ($enum == ActiveType::IS_ACTIVE || $enum == ActiveType::IS_INACTIVE) {
                return $enum == 'Y';
            } else {
                throw new PhonicsException("$enum is not a valid string value for Active");
            }
        } else {
            if (is_bool($enum)) {
                return $enum;
            } else {
                throw new PhonicsException("$enum must be 'Y', 'N' , true or false");
            }
        }
    }

    /**
     * @return int the number of records in the table.
     * @throws PhonicsException on ill-formed SQL
     */
    public function getCount(): int
    {
        $result = $this->query("SELECT * FROM {$this->tableName}", QueryType::RECORD_COUNT);
        return $result->wasSuccessful() ? $result->getResult() : 0;
    }

    /**
     * returns an array of stdClass objects based on a SELECT ALL of the where clause.
     * Array is empty if no matches.
     * @param string $where
     * @param string $boolEnumTreatment
     * @return stdClass[]
     * @throws PhonicsException
     */
    public function getWhere(string $where = '', string $boolEnumTreatment = BoolEnumTreatment::KEEP_AS_Y_N)
    {
        if (not(empty($where)) && ! Util::startsWith_ci('WHERE', trim($where))) {
            $where = 'WHERE ' . $where;
        }
        $query = "SELECT * FROM {$this->tableName} $where";
        return $this->throwableQuery($query, QueryType::STDCLASS_OBJECTS, $boolEnumTreatment);
    }

    /**
     * @param string $query
     * @param QueryType|string $queryType
     * @param string $boolEnumTreatment BoolEnumTreatment::CONVERT_TO_BOOL or KEEP_AS_Y_N
     * @return DbResult
     * @throws PhonicsException on ill-formed SQL
     */
    public function query(string $query, $queryType, string $boolEnumTreatment = BoolEnumTreatment::CONVERT_TO_BOOL): DbResult
    {
        if ( ! BoolEnumTreatment::isValid($boolEnumTreatment)) {
            throw new PhonicsException("$boolEnumTreatment is not a valid BoolEnumTreatment value");
        }
        if ($queryType instanceof QueryType) {
            $type = $queryType->getValue();
        } else {
            if (QueryType::isValid($queryType)) {
                $type = $queryType;
            } else {
                throw new PhonicsException("$queryType not a valid query type.");
            }
        }
        switch ($type) {
            case QueryType::ASSOCIATIVE_ARRAY:
                return $this->db->queryRows($query);
            case QueryType::STDCLASS_OBJECTS:
                $result = $this->db->queryObjects($query);
                if ($result->wasSuccessful()) {
                    $newResult = [];
                    $objects   = $result->getResult();
                    foreach ($objects as $object) {
                        $newResult[] = $this->fixObject($object, $boolEnumTreatment);
                    }
                    return DbResult::goodResult($newResult);
                } else {
                    return $result;
                }
            case QueryType::SCALAR:
                return $this->db->queryAndGetScalar($query);
            case QueryType::RECORD_COUNT:
                return $this->db->queryAndGetCount($query);
            case QueryType::EXISTS:
                $result = $this->db->queryAndGetCount($query);
                return $result->wasSuccessful() ? DbResult::goodResult($result->getResult() > 0) : $result;
            case QueryType::SCALAR_ARRAY:
                return $this->db->queryAndGetScalarArray($query);
            case QueryType::SINGLE_RECORD:
                return $this->db->queryRecord($query);
            case QueryType::SINGLE_OBJECT:
                $result = $this->db->queryObject($query);
                if ($result->wasSuccessful()) {
                    return DbResult::goodResult($this->fixObject($result->getResult(), $boolEnumTreatment));
                } else {
                    return $result;
                }

            case QueryType::AFFECTED_COUNT:
                return $this->db->queryAndGetAffectedCount($query);
            case QueryType::STATEMENT:
                $result = $this->db->queryStatement($query);
                if ($result->wasSuccessful()) {
                    return DbResult::goodResult(1);
                } else {
                    return DbResult::badResult($result->getErrorMessage());
                }
            default:
                return DbResult::badResult($queryType->getValue() . ' is not a valid record type.');
        }
    }

    /**
     *
     * @param mixed $value
     * @return mixed
     */
    public function smartQuotes($value): string
    {
        if (is_null($value)) {
            return 'null';
        }
        if ( ! is_numeric($value) || '0' == $value[0]) {
            $value = "'" . $this->db->getConnection()->real_escape_string($value) . "'";
        }

        return $value;
    }

    /**
     * executes the query, returns the result or throws if query failed.
     * If $throwIfNotFound is true then throw if no records match, otherwise return NULL.
     * Two optional string parameters containing valid BoolEnumTreatment or Throwable values
     * @param string $query
     * @param string $queryType
     * @param string ...$params must be valid Throwable value or BoolEnumTreatment value
     * @return mixed
     * @throws PhonicsException on ill-formed SQL
     */
    public function throwableQuery(string $query, string $queryType, ...$params)
    {
        $throwOnNotFound   = $this->checkThrowable($params);
        $boolEnumTreatment = $this->checkBoolEnumTreatment($params);

        $queryType = $this->getQueryTypeValue($queryType);
        $result    = $this->query($query, $queryType, $boolEnumTreatment);
        if ($result->wasSuccessful()) {
            $goodResult = $result->getResult();
            switch ($queryType) {
                case QueryType::ASSOCIATIVE_ARRAY:
                case QueryType::SCALAR_ARRAY:
                    if ((count($goodResult) == 0) && ($throwOnNotFound === Throwable::THROW_ON_NOT_FOUND)) {
                        throw new PhonicsException('Query found no records.');
                    } else {
                        return $goodResult ?? [];
                    }
                case QueryType::STDCLASS_OBJECTS:
                    $newResult = [];
                    $objects   = $goodResult;
                    foreach ($objects as $object) {
                        $newResult[] = $this->fixObject($object, $boolEnumTreatment);
                    }
                    return $newResult;
                case QueryType::SCALAR:
                case QueryType::SINGLE_RECORD:
                    if ($goodResult == null) {
                        if ($throwOnNotFound === Throwable::THROW_ON_NOT_FOUND) {
                            throw new PhonicsException('Query found no records.');
                        } else {
                            return ($queryType == QueryType::SCALAR) ? '' : null;
                        }
                    } else {
                        return $goodResult;
                    }
                case QueryType::SINGLE_OBJECT:
                    if (($goodResult == null) && ($throwOnNotFound === Throwable::THROW_ON_NOT_FOUND)) {
                        throw new PhonicsException('Query found no records.');
                    } else {
                        return $this->fixObject($goodResult, $boolEnumTreatment);
                    }
                case QueryType::RECORD_COUNT:
                case QueryType::AFFECTED_COUNT:
                    return $goodResult ?? 0;

                case QueryType::EXISTS:
                    return $goodResult ?? false;
                default:
                    return DbResult::badResult($queryType . ' is not a valid query type.');
            }
        } else {
            throw new PhonicsException($result->getErrorMessage());
        }
    }

    /**
     * Deletes all the records in a table (Not reversible)
     * @return int
     * @throws PhonicsException on ill-formed SQL
     */
    public function truncate(): int
    {
        return $this->baseDelete('', KeyChecks::OFF);
    }

    /**
     * @param mixed $keyValue the value of the Primary Key we want.
     * @param string $fieldName the name of the field to be updated.
     * @param mixed $newValue the new value for the field.
     * @throws PhonicsException on ill-formed SQL
     */
    public function updateOne($keyValue, string $fieldName, $newValue): void
    {
        $smartValue = $this->smartQuotes($newValue);
        $smartKey   = $this->smartQuotes($keyValue);
        $query      = "UPDATE {$this->tableName} SET $fieldName = $smartValue WHERE {$this->primaryKey} = $smartKey";
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

// ======================== PROTECTED METHODS =====================
    /**
     * Performs a SQL DELETE. You must be in standalone mode to have an empty where clause or
     * to turn foreign key checks off.
     * @param string $whereClause WHERE clause
     * @param int $foreignKeyChecks KeyChecks::ON or KeyChecks::OFF
     * @return int  the number of records affected
     * @throws PhonicsException on ill-formed SQL
     */
    protected function baseDelete(string $whereClause, int $foreignKeyChecks = KeyChecks::ON): int
    {
        // if ( ! runningStandalone()) {
        //     if (empty($whereClause) || ($foreignKeyChecks == KeyChecks::OFF)) {
        //         throw new PhonicsException('Empty where clause and/or key check off only allowed in standalone environment');
        //     }
        // }
        if (not(empty($whereClause)) && not(Util::startsWith_ci('WHERE', ltrim($whereClause)))) {
            $whereClause = 'WHERE ' . $whereClause;
        }
        if ($foreignKeyChecks == KeyChecks::OFF) {
            $this->throwableQuery("SET FOREIGN_KEY_CHECKS = 0", QueryType::STATEMENT);
        }
        $query = "DELETE FROM {$this->tableName} $whereClause";
        $count = $this->throwableQuery($query, QueryType::AFFECTED_COUNT);
        if ($foreignKeyChecks == KeyChecks::OFF) {
            $this->throwableQuery("SET FOREIGN_KEY_CHECKS = 1", QueryType::STATEMENT);
        }
        return $count;
    }

    protected function checkBoolEnumTreatment(...$params): string
    {
        foreach ($params as $param) {
            if (BoolEnumTreatment::isValid($param)) {
                return $param;
            }
        }
        return BoolEnumTreatment::CONVERT_TO_BOOL; // default
    }

    protected function checkThrowable(...$params): string
    {
        foreach ($params as $param) {
            if (Throwable::isValid($param)) {
                return ($param == Throwable::THROW_ON_NOT_FOUND);
            }
        }
        return Throwable::NOT_FOUND_IS_VALID; // default
    }

    /**
     * smart quotes for JSON object
     * @param $object
     * @return string suitable for use as the value of a mysql JSON field
     */
    protected function encodeJsonQuoted($object)
    {
        if ($object == null) {
            return "NULL";
        }
        $encode = json_encode($object, JSON_UNESCAPED_SLASHES);
        $fixed  = str_replace("'", "\\'", $encode);
        return "'" . $fixed . "'";
    }

    /**
     * @param stdClass|null $object $object
     * @param string $boolTreatment
     * @return ?stdClass
     * @throws PhonicsException on ill-formed SQL
     */
    protected function fixObject(?stdClass $object, string $boolTreatment = BoolEnumTreatment::CONVERT_TO_BOOL): ?stdClass
    {
        if (is_null($object) || ($object == (object)[])) {
            return null;
        }
        $newObject = $object;
        if ( ! BoolEnumTreatment::isValid($boolTreatment)) {
            throw new PhonicsException("$boolTreatment is not a valid BoolEnumTreatment value");
        }
        if ($boolTreatment == BoolEnumTreatment::CONVERT_TO_BOOL) {
            foreach ($this->booleanFields as $field) {
                try {
                    if (isset($newObject->$field) && isset($object->$field)) {
                        $newObject->$field = $this->enumToBool($object->$field);
                    }
                } catch (Exception $ex) {
                    throw new PhonicsException("We should never get here. " . $ex->getMessage());
                }
            }
        }

        foreach ($this->jsonFields as $field) {
            if (isset($newObject->$field) && isset($object->$field) && is_string($object > $field)) {
                try {
                    $newObject->$field = is_null($object->$field) ? NULL : json_decode($object->$field);
                } catch (Exception $ex) {
                    throw new PhonicsException("We should never get here. " . $ex->getMessage());
                }
            }
        }
        return $newObject;
    }

    protected function getDeleteBase(): string
    {
        return "DELETE FROM {$this->tableName} WHERE ";
    }

    /**
     * @param string|QueryType $queryType
     * @return string
     * @throws PhonicsException on ill-formed SQL
     */
    protected function getQueryTypeValue($queryType): string
    {
        if ($queryType instanceof QueryType) {
            return $queryType->getValue();
        }
        if (QueryType::isValid($queryType)) {
            return $queryType;
        }
        throw new PhonicsException("$queryType not a valid query type.");
    }


}
