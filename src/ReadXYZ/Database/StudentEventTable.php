<?php

namespace ReadXYZ\Database;

// holds 'event' stuff in cargo, like DisfiguredWriting or tests

class StudentEventTable extends AbstractData implements IBasicTableFunctions
{
    // Hold a singleton instance of the class
    private static $instance;

    protected function __construct()
    { // private, so can't instantiate from outside class
        parent::__construct();
        $this->tableName = 'abc_StudentEvent';
        $this->uuidPrefix = 'E';
        $this->primaryKey = 'uuid';
        $this->secondaryKeys = ['trainerID', 'studentID'];
    }

    // The singleton method
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new StudentEventTable();
        }

        return self::$instance;
    }

    public function create()
    {
        $createString =
            "CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
              `uuid`      	        varchar(16)  NOT NULL,
              `trainerID`  	        varchar(64)  NOT NULL,
              `studentID`  	        varchar(32)  NOT NULL,
              `cargo` 	                text,
              `project`                 varchar(32),
              `created`                 int(10) unsigned default 0,
              `createdhuman`            varchar(32),
              `lastupdate`              int(10) unsigned default 0,
              `lastbackup`              int(10) unsigned default 0,
              PRIMARY KEY  (`{$this->primaryKey}`)
            ) DEFAULT CHARSET=utf8;";

        return assert($this->createTable($createString), 'StudentEvent table is being created');
    }

    // if you have logged in as a parent or psychologist, you have a number of students.
    public function GetAllEventsbyTeacher($trainerID)
    {
        $query = sprintf(
            "SELECT cargo
                                 FROM $this->tableName
                                 WHERE trainerID=%s
                                 ORDER BY created desc",
            $this->quote_smart($trainerID)
        ); // avoids SQL injection attacks
        $result = $this->query($query);
        $unpack = [];
        foreach ($result as $single) {
            $unpack[] = unserialize($single['cargo']);
        }

        return $unpack;
    }

    // would be safer if this were uncommented
    //function drop(){assert(false,__METHOD__." is not supported.");}
}
