<?php

namespace ReadXYZ\Database;

class CRM extends AbstractData implements IBasicTableFunctions
{
    // Hold a singleton instance of the class
    private static $instance;

    protected function __construct()
    { // private, so can't instantiate from outside class
        parent::__construct();
        $this->tableName = 'abc_CRM';
        $this->uuidPrefix = 'Z';
        $this->primaryKey = 'uuid';
        $this->secondaryKeys = [
            'Name',
            'EMail',
            'EMail2',
            'Phone',
            'Keywords',
            'Project',
            'LastDate',
            'NextDate',
            'Role',
        ];
    }

    // The singleton method
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new CRM();
        }

        return self::$instance;
    }

    public function create()
    {
        $createString =
            "CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
              `uuid`      	            varchar(16)  NOT NULL,
              `Name`                    varchar(64)  NOT NULL,
              `EMail`                   varchar(64)  NOT NULL,
              `EMail2`                  varchar(64)  NOT NULL,
              `Phone`                   varchar(16)  NOT NULL,
              `Keywords`                varchar(64)  NOT NULL,
              `LastDate`                int(10) unsigned default 0,
              `NextDate`                int(10) unsigned default 0,
              `Project`                 varchar(32),
              `Role`                    varchar(20),
              `cargo` 	                text,
              `created`                 int(10) unsigned default 0,
              `createdhuman`            varchar(32),
              `lastupdate`              int(10) unsigned default 0,
              `lastbackup`              int(10) unsigned default 0,
              PRIMARY KEY  (`{$this->primaryKey}`)
            ) DEFAULT CHARSET=utf8;";

        return assert($this->createTable($createString), $this->tableName . ' table is being created');
    }

    public function insertCRM($cargo)
    {
        // do not allow if the email already exists
        if ($this->getCRMbyEmail($cargo['EMail'])) {
            return false;
        }

        return $this->insert($cargo);
    }

    public function getCRMbyEmail($email)
    {
        $query = sprintf(
            "select * from {$this->tableName} where EMail=%s or EMail2=%s",
            $this->quote_smart($email),
            $this->quote_smart($email)
        );
        $resultSet = $this->query($query);

        if (0 == count($resultSet)) {
            return false;
        }

        $result = current($resultSet);

        return unserialize($result['cargo']);
    }

    public function searchCRM($search)
    {
        // if there are spaces, the break them into multiple searches
        //    (probably don't have to worry about attackes...)
        $searchArray = explode(' ', $search);
        $results = [];
        foreach ($searchArray as $value) {
            $query = "select uuid,name,email,phone,keywords,role from {$this->tableName} where
                                  Name like '%$search%' or Email like '%$search%' or EMail2 like '%$search%'
                                        or Phone like '%$search%' or Keywords like '%$search%' order by NextDate";
            $result = $this->query($query);

            foreach ($result as $single) {
                $results[$single['uuid']] = $single;
            }
            // array of arrays, and lose duplicates
        }

        return $results;
    }

    public function next200actions()
    {
        $resultSet = $this->query("select cargo from {$this->tableName} order by nextdate desc LIMIT 200");
        // before returning, we need to unserialize every cargo element back into an array
        $simpleArray = [];
        foreach ($resultSet as $result) {
            $simpleArray[] = unserialize($result['cargo']);
        }

        return $simpleArray;
    }
}
