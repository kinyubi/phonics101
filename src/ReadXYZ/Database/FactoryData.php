<?php

namespace App\ReadXYZ\Database;

use Exception;
use RuntimeException;

abstract class FactoryData
{ // all data tables inherit from this class
    public string $tableName;
    public string $primaryKey;
    protected array $secondaryKeys;
    public string  $uuidPrefix;

    protected DbConnect $dbo;

    public function __construct()
    {
        $this->dbo = new DbConnect();
        if (mysqli_connect_errno()) {
            throw new RuntimeException('MySQL connect failure. ' . mysqli_connect_error());
        }
    }

    /**
     * execute a query that does not return a data set such as DELETE, INSERT or UPDATE.
     *
     * @param string $query a valid SQL query
     *
     * @return bool
     *
     * @throws Exception
     */
    public function statement($query): bool
    {
        return false !== $this->dbo->query($query);
    }

    /**
     * Execute a SELECT, SHOW, DESCRIBE or EXPLAIN query.
     * @param $query
     *
     * @return array an associative array of results
     * @throws Exception when DbConnect query intercepts an error
     */
    public function query($query): array
    {
        return $this->dbo->query($query);
    }

    public function uuid()
    {
        assert(!empty($this->uuidPrefix), 'Checking for non-empty UUID prefix');

        return uniqid($this->uuidPrefix);
    }

    public function getfield($array, $field)
    { // really just a 'safe' read from an array
        if (isset($array[$field])) {
            return $array[$field];
        } else {
            assert(false, "Cargo did not have field '$field'.");
        }
    }

    /**
     * Convert an array of key/value pairs into an INSERT INTO query and execute the query.
     *
     * @param array $aArray an array of key/value pairs
     *
     * @return string
     *
     * @throws Exception
     */
    public function insertArray($aArray): bool
    { // $aArray is a set of field-value pairs
        $cFields = '';
        $cValues = '';

        // if we don't have LastUpdated in our list (won't unless we are copying), then add it.  (needed for merging)
        if (!array_key_exists('LastUpdate', $aArray)) {
            $aArray['LastUpdate'] = time();
        }
        // seconds are good enough

        foreach ($aArray as $key => $value) {
            if ('' != $cFields) { // for second and subsequent fields, we need comma separators
                $cFields .= ', ';
                $cValues .= ', ';
            }

            $cFields .= $key; //  no checks against field names, but we have to be more careful with value fields

            switch (gettype($value)) {
                case 'boolean':
                    $cValues .= $value ? '1' : '0';
                    break;
                case 'integer':
                    $cValues .= strval($value);
                    break;
                case 'double':
                    assert(false, "don't have a DOUBLE handler for insert " . serialize($aArray));
                    break;
                case 'string':
                    $cValues .= $this->quote_string($value); // clean up, prevent injection
                    break;
                case 'array':
                    assert(false, "don't have an ARRAY handler for inserts of $key " . serialize($aArray));
                    break;
                case 'object':
                    assert(false, "don't have an OBJECT handler for inserts of $key " . serialize($aArray));
                    break;
                case 'resource':
                    assert(false, "don't have a RESOURCE handler for inserts of $key " . serialize($aArray));
                    break;
                case 'NULL':
                    // we decided to try to convert to empty string, because we don't have a schema
                    $cValues .= $this->quote_string('');
                    break;
                default:
                    assert(false, 'Did not expect a type ' . gettype($value) . " in INSERT() on field $key " . serialize($aArray));
            }
        }
        $insertString = 'INSERT INTO ' . $this->tableName . ' (' . $cFields . ') VALUES (' . $cValues . ')';

        return $this->statement($insertString);
    }

    public function updateArray($aArray, $where)
    {
        $updates = '';

//      // if we don't have LastUpdated in our list (won't unless we are copying), then update it.
        //      //    ie: a copy operation retains the old update time
        //      if (!array_key_exists('LastUpdate',$aArray))
        //            $aArray['LastUpdate'] = time();        // seconds are good enough

        if (isset($aArray['cargo'])) { // lots of work to update lastUpdated
            $cargo = unserialize($aArray['cargo']);
            $cargo['lastupdate'] = time();
            $aArray['cargo'] = serialize($cargo);
        }

        foreach ($aArray as $key => $value) {
            if ('' != $updates) { // for second and subsequent fields, we need comma separators
                $updates .= ',';
            }

            $updates .= $key; //  no checks against field names, but we have to be more careful with value fields
            $updates .= '=';

            switch (gettype($value)) {
                case 'boolean':
                    $updates .= $value ? '1' : '0';
                    break;
                case 'integer':
                    $updates .= strval($value);
                    break;
                case 'resource':
                case 'object':
                case 'array':
                case 'double':
                    break;
                case 'string':
                    $updates .= $this->quote_string($value); // never put a raw string in a query...
                    break;
                case 'NULL':
                    $updates .= ''; // treat NuLL as an empty string
                    break;
            }
        }

        // everyone gets the 'lastupdate' so that we can replicate one day
        $updates .= ', lastupdate=' . time(); // it's a number, doesn't need quotes

        $UpdateString = "Update $this->tableName set $updates where $where";
        $this->statement($UpdateString);
    }

    private function quote_string($dangerous)
    { // clean up, prevent injection
        $safe = $this->dbo->dbConnector->real_escape_string($dangerous);

        return "'" . $safe . "'";
    }

    public function quote_smart($value)
    {
        // Quote if not integer or starts with a leading zero ie zip code
        if (!is_numeric($value) || '0' == $value[0]) {
            $value = "'" . $this->dbo->dbConnector->real_escape_string($value) . "'";
        }

        return $value;
    }

}
