<?php

namespace Tests\ReadXYZ\Data;

use App\ReadXYZ\Data\OldStudentData;
use App\ReadXYZ\Data\OldUserData;
use App\ReadXYZ\Enum\QueryType;
use PHPUnit\Framework\TestCase;
use stdClass;

class OldUserDataTest extends TestCase
{

    public function testGetAll()
    {
        $data = new OldUserData();
        $users = $data->getAll();
        $this->assertIsArray($users);
        $this->assertNotCount(0, $users);
        $this->assertTrue($users[0] instanceof stdClass);
    }

    public function testGetCount()
    {
        $data = new OldUserData();
        $users = $data->getAll();
        $count = $data->getCount();
        $this->assertTrue($count > 0);
        $this->assertEquals($count, count($users));
    }

    public function testGetUsersWithoutStudents()
    {
        $userData = new OldUserData();
        $studentData = new OldStudentData();
        $orphans = $userData->getUsersWithoutStudents();
        $orphanCount = count($orphans);
        $this->assertTrue($orphanCount > 0);
        foreach($orphans as $userName) {
            $query = "SELECT * FROM abc_student WHERE trainer1 = '$userName'";
            $exists = $studentData->throwableQuery($query, QueryType::EXISTS);
            $this->assertFalse($exists);
        }
    }

    public function testGetWhere()
    {
        $userData = new OldUserData();
        $where = 'UserName NOT IN (SELECT trainer1 FROM abc_student) ';
        $array = $userData->getWhere($where);
        $count = count($array);
        $orphans = $userData->getUsersWithoutStudents();
        $orphanCount = count($orphans);
        $this->assertEquals($count, $orphanCount);
    }
}
