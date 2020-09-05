<?php

namespace ReadXYZ\Database;

use ReadXYZ\Helpers\Debug;
use ReadXYZ\Models\Identity;
use ReadXYZ\Models\Student;

class StudentTable extends AbstractData implements IBasicTableFunctions
{
    // describe a single student

    // Hold a singleton instance of the class.
    private static $instance;

    private function __construct()
    { // private, so can't instantiate from outside class
        parent::__construct();
        $this->tableName = 'abc_Student';
        $this->uuidPrefix = 'S';
        $this->primaryKey = 'studentID';
        $this->secondaryKeys = ['StudentName', 'trainer1', 'trainer2', 'trainer3', 'project'];
    }

    // The singleton method
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new StudentTable();
        }

        return self::$instance;
    }

    public function create()
    {
        $createString =
            "CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
              `studentid`  	            varchar(32)  NOT NULL,
              `cargo` 	                text,
              `StudentName`             varchar(32),
              `project`                 varchar(32),
              `trainer1`                varchar(64),
              `trainer2`                varchar(64),
              `trainer3`                varchar(64),
              `created`                 int(10) unsigned default 0,
              `createdhuman`            varchar(32),
              `lastupdate`              int(10) unsigned default 0,
              `lastbackup`              int(10) unsigned default 0,
              PRIMARY KEY  (`{$this->primaryKey}`)
            ) DEFAULT CHARSET=utf8;";

        $this->createTable($createString);
    }

    // customm UPDATE BY KEY moves student name from cargo-enrollment to just cargo
    // check for special updates in each class, this is just a basic update
    public function updateByKey($key, $cargo)
    {
        assert(!empty($key), 'Need a key for updateByKey');

        if (isset($cargo['enrollForm']) and isset($cargo['enrollForm']['StudentName'])) {
            $cargo['StudentName'] = $cargo['enrollForm']['StudentName'];
        }

        return parent::updateByKey($key, $cargo);
    }

    /**
     * get all student cargos that have the given user id as a trainer.
     * @param string $userID
     *
     * @return array
     */
    public function GetAllStudents($userID = '')
    {
        // this can look up anyone's students, but by default it looks up the logged-in user's students
        if (empty($ID)) {
            $identity = Identity::getInstance();
            $userID = $identity->getUserName();
        }

        $where = sprintf(
            '(trainer1=%s or trainer2=%s or trainer3=%s)',
            $this->quote_smart($userID),
            $this->quote_smart($userID),
            $this->quote_smart($userID)
        ); // avoids SQL injection attacks

        $order = 'created';

        return $this->getAllCargoByWhere($where, $order);
    }

    public function getAllStudentsGlobal($order = 'lastupdate DESC')
    {
        // all students, sorted by parameter
        //        $result = $this->getAllCargoByWhere('',$order);
        //        return($result);

        $query = <<<EOT
            SELECT t1.studentid, t1.StudentName, t1.trainer1, t1.lastupdate, t1.cargo, t2.uuid, t2.EMail, t2.nextdate 
            FROM {$this->tableName} t1 left outer join `abc_CRM` t2 on (t1.trainer1 = t2.EMail)
            ORDER BY t1.lastupdate DESC
EOT;

        $resultSet = $this->query($query);
        if (!is_array($resultSet) /*or !isset($resultSet['cargo'])*/) {
            return [];
        }
        // special case of no results, we always return an array

        // before returning, we need to unserialize every cargo element back into an array
        $simpleArray = [];
        foreach ($resultSet as $result) {
            $cargo = unserialize($result['cargo']);
            $simpleArray[] = array_merge(
                $cargo,
                ['uuid' => $result['uuid']],
                ['EMail' => $result['EMail']],
                ['NextDate' => $result['nextdate']],
                ['StudentName' => $cargo['enrollForm']['StudentName']]
            );
        }
        return $simpleArray;
    }

    // this plugs in the current user as Trainer1
    public function insertNewStudent(Student $student, array $enrollForm)
    {
        // it's not a valid cargo unless it has all of the following fields
        $cargo = [
            'studentID' => $student->studentID,
            'currentLesson' => '',
            'currentLessons' => [],
            'masteredLessons' => [],
            'blockedLessons' => [],
            'preferences' => [],
            'enrollForm' => $enrollForm, // contains firstName, lastName, etc
            'StudentName' => $enrollForm['StudentName'],
            'created' => time(),
        ];

        $identity = Identity::getInstance();
        $cargo['trainer1'] = $identity->getUserName();
        $cargo['createdBy'] = $identity->getName();
        $cargo['trainer2'] = '';
        $cargo['trainer3'] = '';
        $cargo['project'] = $identity->getProject();

        return parent::insert($cargo, false); // returns the primaryKey or false
    }

    // would be safer if this were uncommented
    //function drop(){assert(false,__METHOD__." is not supported.");}

    public function getAllStudentsByProject($project)
    {
        $where = sprintf('project=%s', $this->quote_smart($project));

        return $this->getAllCargoByWhere($where);
    }
}
