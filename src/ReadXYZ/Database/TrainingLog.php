<?php

namespace ReadXYZ\Database;

// TrainingLog holds details of all training sessions
use ReadXYZ\Helpers\Util;
use ReadXYZ\Models\Identity;

class TrainingLog extends AbstractData implements IBasicTableFunctions
{
    // Hold a singleton instance of the class
    private static $instance;

    protected function __construct()
    { // private, so can't instantiate from outside class
        parent::__construct();
        $this->tableName = 'abc_TrainingLog';
        $this->uuidPrefix = 'T';
        $this->primaryKey = 'uuid';
        $this->secondaryKeys = [
            'created',
            'studentID',
            'action',
            'project',
            'trainerID',
            'rule',
            'result',
        ]; // so can sort by date
    }

    // The singleton method
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new TrainingLog();
        }

        return self::$instance;
    }

    public function create()
    {
        $createString =
            "CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
              `sessionID`  	        varchar(16)  NOT NULL,
              `trainerID`  	        varchar(64)  NOT NULL,
              `studentID`  	        varchar(32)  NOT NULL,
              `action`  	        varchar(32)  NOT NULL,
              `project`                 varchar(32),
              `rule`                    varchar(64),
              `result`                  varchar(64),
              `comment`                 varchar(256),
              `JoomlaName`              varchar(32),
              `remoteAddr`              varchar(32),
              `created`                 int(10) unsigned default 0,
              `createdhuman`            varchar(32),
              `lastupdate`              int(10) unsigned default 0,
              `lastbackup`              int(10) unsigned default 0
            ) DEFAULT CHARSET=utf8;";

        return assert($this->createTable($createString), $this->tableName . ' table is being created');
    }

    public function insert($cargo, $key = '')
    {
        assert(false, 'Use insertLOG() instead of insert() for the log file');
    }

    // we use the TrainingSession singleton to get trainerID, studentID, and project
    public function insertLog($action, $rule = '', $result = '', $comment = '')
    {
        $identity = Identity::getInstance();

        $aArray = [];
        $aArray['sessionID'] = $identity->getSessionId();
        $aArray['trainerID'] = $identity->getUserName();
        $aArray['studentID'] = $identity->getStudentId();
        $aArray['JoomlaName'] = $identity->getName();
        $aArray['project'] = $identity->getProject();
        $aArray['created'] = time();
        $aArray['createdhuman'] = Util::getHumanReadableDateTime();
        $aArray['action'] = $action;
        $aArray['rule'] = $rule;
        $aArray['result'] = $result;
        $aArray['comment'] = $comment;
        $aArray['remoteAddr'] = $_SERVER['REMOTE_ADDR'] ?? '';

        $this->insertArray($aArray);
    }

    public function getHistoryByStudent($studentID)
    {
        $query = sprintf(
            "select trainerID,action,rule,createdhuman,project,sessionID from $this->tableName where studentID=%s order by created",
            $this->quote_smart($studentID)
        );
        $resultSet = $this->query($query);

        return $resultSet;
    }

    public function getRecentHistory($userOnly = '')
    {
        $limit = '';
        if (!empty($userOnly)) {
            $limit = sprintf(
                'where trainerID = %s ',
                $this->quote_smart($userOnly)
            );
        }

        $query = "select *, count(sessionid) as count from {$this->tableName} $limit group by sessionid order by created desc limit 100";

        return $this->query($query);
    }

    public function getTrainingSession($sessionID)
    {
        $query = "select * from {$this->tableName} where sessionid = '$sessionID' order by created desc";

        return $this->query($query);
    }

    // public function getAssessment($studentID, $program = 'Assessment')
    // {
    //     // have to break the next stmt into two because the % in the like clause confuses sprintf
    //     $query = sprintf(
    //                  "select * from {$this->tableName} where studentID=%s ",
    //                  $this->quote_smart($studentID)
    //              ) . "and rule like '{$program}%' order by created";
    //
    //     return $this->query($query);
    // }
}
