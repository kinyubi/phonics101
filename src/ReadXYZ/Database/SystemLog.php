<?php

namespace ReadXYZ\Database;

// SystemLog holds error messages and exceptions
use ReadXYZ\Helpers\Util;
use ReadXYZ\Models\Identity;

class SystemLog extends AbstractData implements IBasicTableFunctions
{
    // Hold a singleton instance of the class
    private static $instance;

    // Prevent logging events if we are already writing one (usually DB errors)
    public $inSystemLog;

    private function __construct()
    { // private, so can't instantiate from outside class
        parent::__construct();
        $this->tableName = 'abc_SystemLog';
        $this->uuidPrefix = 'L';
        $this->primaryKey = 'uuid';
        $this->secondaryKeys = ['joomlaName', 'created', 'action']; // so can sort by date
        $this->inSystemLog = false;
    }

    // The singleton method
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new SystemLog();
        }

        return self::$instance;
    }

    public function create()
    {
        $createString =
            "CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
              `uuid`        	        varchar(16)  NOT NULL,
              `joomlaName`  	        varchar(64)  NOT NULL,
              `action`  	            varchar(32)  NOT NULL,
              `project`                 varchar(32),
              `cargo` 	                text,
              `created`                 int(10) unsigned default 0,
              `createdhuman`            varchar(32),
              `lastupdate`              int(10) unsigned default 0,
              `lastbackup`              int(10) unsigned default 0,
              PRIMARY KEY  (`{$this->primaryKey}`)
            ) DEFAULT CHARSET=utf8;";

        return assert($this->createTable($createString), $this->tableName . ' table is being created');
    }

    public function write($action, $comment)
    {
        $humanReadableTime = Util::getHumanReadableDateTime();
        if (!isset($GLOBALS['Errors'])) {
            $GLOBALS['Errors'] = [];
        }
        $GLOBALS['Errors'][] = "[$humanReadableTime] $action: $comment";
        $identity = Identity::getInstance();

        $aArray = [];
        $aArray['uuid'] = $this->uuid();
        if ($identity->isValidUser()) {
            $aArray['JoomlaName'] = $identity->getUserName();
            $aArray['project'] = $identity->getProject();
        } else {
            $aArray['JoomlaName'] = 'Not logged in';
            $aArray['project'] = '';
        }
        $aArray['created'] = time();
        $aArray['createdhuman'] = $humanReadableTime;
        $aArray['action'] = $action;
        $aArray['cargo'] = $comment;

        if (true == $this->inSystemLog) {
            //Recursive call to SystemLog while logging another message (usually a DB error)
            return;
        } else {
            $this->inSystemLog = true; // now if a logging event happens, we
            $this->insertArray($aArray); //      don't write a second time
        }

        $this->inSystemLog = false;
    }

    public function getLast20()
    {
        $ret = $this->query("select * from {$this->tableName} order by created desc LIMIT 20");
        $return = [];
        foreach ($ret as $element) {
            // this is the only place we use urldecode instead of rawurldecode
            $return[] = "<b>{$element['action']}  <i>{$element['joomlaName']}</i>  {$element['createdhuman']}</b><br/>" .
                        urldecode(htmlspecialchars_decode(html_entity_decode(html_entity_decode($element['cargo']))));
        }

        return $return;
    }
}
