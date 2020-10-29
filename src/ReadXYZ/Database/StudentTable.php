<?php

namespace App\ReadXYZ\Database;

use App\ReadXYZ\Helpers\Debug;
use App\ReadXYZ\Models\Identity;
use App\ReadXYZ\Models\Student;

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

}
