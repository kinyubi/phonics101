<?php

namespace ReadXYZ\Database;

use ReadXYZ\Helpers\Util;

class AbstractData extends FactoryData
{
    public function __construct()
    { // constructor
        parent::__construct();
    }

    public function drop()
    {
        return assert($this->dropTable(), '{$this->tableName} is being dropped');
    }

    public function deleteByKey($key)
    {
        assert(is_string($key), 'Should be a string: ' . serialize($key));
        $ret = $this->statement("delete from {$this->tableName} where {$this->primaryKey} = '$key'");
    }

    public function getCargoByKey($key)
    {
        assert(!empty($key), 'Need a key for getCargoByKey');

        $resultSet = $this->query("select cargo from {$this->tableName} where {$this->primaryKey} = '{$key}';");
        //echo "<br>select cargo from `{$this->tableName}` where {$this->primaryKey} = '{$key}',<br>";
        //echo serialize($resultSet);

        if (0 == count($resultSet)) {
            return false;
        }
        // didn't find the key

        $result = $resultSet[0];
        $cargo = unserialize($result['cargo']);

        return $cargo;
    }

    // check for special updates in each class, this is just a basic update
    public function updateByKey($key, $cargo)
    {
        assert(!empty($key), 'Need a key for updateByKey');

        $aArray = [];
        $cargo['lastupdate'] = Util::getHumanReadableDateTime();
        $aArray['cargo'] = serialize($cargo);

        // add or update the secondary keys
        foreach ($this->secondaryKeys as $sKey) {
            if (!isset($cargo[$sKey])) {
                assert(false, "Required secondary key '$sKey' is not set in cargo");
                $cargo[$sKey] = $this->uuid(); // jam in something unique
            }
            $aArray[$sKey] = strval($cargo[$sKey]);
        }

        $this->updateArray($aArray, "$this->primaryKey = '$key'");
    }

    // the key is optional, if missing we generate a UUID
    public function insert($cargo, $key = false)
    {
        if (!$key) {
            $key = $this->uuid();
            $cargo[$this->primaryKey] = $key; // make sure cargo knows about it...
        }

        $cargo['lastupdate'] = Util::getHumanReadableDateTime();

        $aArray = [];
        $aArray[$this->primaryKey] = $key;
        $aArray['created'] = time();
        $aArray['createdhuman'] = Util::getHumanReadableDateTime();
        $aArray['cargo'] = serialize($cargo);

        // add the secondary keys
        foreach ($this->secondaryKeys as $sKey) {
            assert(isset($cargo[$sKey]), "insert() requires secondary key '$sKey' to be set in cargo");
            $aArray[$sKey] = $cargo[$sKey];
        }

        $this->insertArray($aArray);

        return $key;
    }

    public function getAllCargoByWhere($where = '', $order = '')
    {
        $query = "select cargo from `{$this->tableName}`" . (empty($where) ? '' : "WHERE $where")
            . (empty($order) ? '' : " ORDER BY $order");

        $resultSet = $this->query($query);
        //echo "resultSet",serialize($resultSet),"<br><br>";
        if (!is_array($resultSet) /*or !isset($resultSet['cargo'])*/) {
            return [];
        }
        // special case of no results, we always return an array

        // before returning, we need to unserialize every cargo element back into an array
        $simpleArray = [];
        foreach ($resultSet as $result) {
            $simpleArray[] = unserialize($result['cargo']);
        }

        return $simpleArray;
    }

    // delete all records in a table for a project (usally the test project)
    public function deleteProject($project)
    {
        $safe = $this->quote_smart($project); // don't like injections
        $resultSet = $this->statement("delete from {$this->tableName} where project = $safe");
    }
}
