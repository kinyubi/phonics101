<?php

namespace ReadXYZ\Database;

class Projects extends AbstractData implements IBasicTableFunctions
{
    // Hold a singleton instance of the class
    private static $instance;

    protected function __construct()
    { // private, so can't instantiate from outside class
        parent::__construct();
        $this->tableName = 'abc_Projects';
        $this->uuidPrefix = 'P';
        $this->primaryKey = 'uuid';
        $this->secondaryKeys = ['shortName']; // so can sort by date
    }

    // The singleton method
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Projects();
        }

        return self::$instance;
    }

    public function create()
    {
        $createString =
            "CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
              `uuid`      	            varchar(16)  NOT NULL,
              `project`                 varchar(32)  NOT NULL,
              `cargo` 	                text,
              `created`                 int(10) unsigned default 0,
              `createdhuman`            varchar(32),
              `lastupdate`              int(10) unsigned default 0,
              `lastbackup`              int(10) unsigned default 0,
              PRIMARY KEY  (`{$this->primaryKey}`)
            ) DEFAULT CHARSET=utf8;";

        return assert($this->createTable($createString), $this->tableName . ' table is being created');
    }
}
