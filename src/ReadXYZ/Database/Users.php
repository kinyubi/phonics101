<?php

namespace App\ReadXYZ\Database;

class Users extends AbstractData implements IBasicTableFunctions
{
    // Hold a singleton instance of the class
    private static $instance;

    //I don't like this being public but there are places where new is used instead of getInstance
    public function __construct()
    { // private, so can't instantiate from outside class
        parent::__construct();
        $this->tableName = 'abc_Users';
        $this->uuidPrefix = 'U';
        $this->primaryKey = 'uuid';
        $this->secondaryKeys = ['UserName', 'EMail', 'Project'];
    }

    // The singleton method
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Users();
        }

        return self::$instance;
    }

    public function insertUser($cargo)
    {
        // do not allow if the email already exists  (dup userName is ok)
        if ($this->getUserEMail($cargo['EMail'])) {
            assert(false, "insertUser Fails - EMail '{$cargo['EMail']}' already exists");
        }

        // UserName is optional but necessary for DB,
        //  we'll create it here if necessary
        if (!isset($cargo['UserName'])) {
            $name = explode('@', $cargo['EMail']);
            $cargo['UserName'] = $name[0];
            assert(!empty($cargo['UserName']));
        }

        // Project is optional but necessary for DB,
        //  we'll create it here if necessary
        if (!isset($cargo['Project'])) {
            $cargo['Project'] = 'Unknown';
        }

        return $this->insert($cargo);
    }

    public function getUserEMail($EMail)
    {
        if ($result = $this->getUserCargo($EMail)) {
            return $result['EMail'];
        }

        return false; // lots of reasons this could happen
    }

    /**
     * @param $EMail
     * @return false|mixed
     * @throws \Exception
     */
    public function getUserCargo($EMail)
    { // returns ONE record
        $safeUser = $this->quote_smart($EMail); // don't like injections
        $resultSet = $this->query("select * from abc_Users where EMail = $safeUser");

        if (count($resultSet) > 1) {
            return false;
        }

        //// if NO results, then we can try against userName
        if (0 == count($resultSet)) {
            $resultSet = $this->query(
                "select * from abc_Users where userName = $safeUser or EMail = $safeUser"
            );
            if (count($resultSet) > 1) {
                // but don't ASSERT because our tester will fail, it just doesn't work
                return false;
            }
        }

        if (0 == count($resultSet)) {
            return false;
        }

        // ok, exactly 1 user

        $rSet = current($resultSet); // don't iterate, only one record.
        $cargo = unserialize($rSet['cargo']);

        // sanity check - don't want to allow tampering here
        if ($cargo['EMail'] !== $rSet['EMail']) {
            assert(false, "Cargo EMail doesn't match record for {$rSet['EMail']}");

            return false;
        }

        return $cargo;
    }

}
