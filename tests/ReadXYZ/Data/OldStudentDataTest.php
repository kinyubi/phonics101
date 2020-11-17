<?php

namespace Tests\ReadXYZ\Data;

use App\ReadXYZ\Data\OldStudentData;
use PHPUnit\Framework\TestCase;

class OldStudentDataTest extends TestCase
{

    public function testGetStudentId()
    {
        $studentData = new OldStudentData();
        $id = $studentData->getStudentId('MickeyMouse', 'Donald');
        $this->assertEmpty($id);
        $annieStudentId = 'S5c9e79a6534ca';
        $id = $studentData->getStudentId('lisamichelle@gmail.com', 'Annie');
        $this->assertEquals($annieStudentId, $id);
    }

    public function testGetStudentName()
    {
        $studentData = new OldStudentData();
        $name = $studentData->getStudentName('S5c9e79a6534ca');
        $this->assertEquals('Annie', $name);
    }


    public function testGetStudents()
    {
        $studentData = new OldStudentData();
        $students = $studentData->getStudents('bob@bob.com');
        $this->assertTrue(is_array($students));
        $this->assertCount(0, $students);
        $students = $studentData->getStudents('lisamichelle@gmail.com');
        $this->assertTrue(is_array($students));
        $this->assertCount(3, $students);
        foreach($students as $student) {
            $this->assertTrue(in_array($student['StudentName'], ['Annie', 'Joe', 'Jim']));
            $this->assertEquals(14, strlen($student['studentid']));
        }
    }

    public function testStudentHasTeacher()
    {
        $studentData = new OldStudentData();
        $annieStudentId = 'S5c9e79a6534ca';
        $lisaUserId = 'U5c9e796dbd8e9';
        $carlUserId = 'U5eb35004bbea9';
        $exists = $studentData->studentHasTeacher($annieStudentId, $lisaUserId);
        $this->assertTrue($exists);
        $exists = $studentData->studentHasTeacher($annieStudentId, $carlUserId);
        $this->assertFalse($exists);
    }

    public function testGetData()
    {
        $studentData = new OldStudentData();
        $georgeStudentId = 'S5eb35006f2a1e';
        $data = $studentData->getData($georgeStudentId);
        $this->assertIsObject($data);
    }

}
