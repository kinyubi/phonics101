<?php

namespace Tests\ReadXYZ\Enum;

use App\ReadXYZ\Enum\Regex;
use PHPUnit\Framework\TestCase;

class RegexTest extends TestCase
{

    public function testExtractSqlFieldLength()
    {
        $this->assertEquals(15, Regex::extractSqlFieldLength('varchar(15)'));
    }

    public function testIsMatch()
    {
        $this->assertTrue(Regex::isMatch(Regex::CAMEL_CASE_TRANSITION, 'badDog'));
    }

    public function testIsValidEmail()
    {
        $this->assertTrue(Regex::isValidEmail('carlbaker@gmail.com-nate'));
        $this->assertFalse(Regex::isValidEmail('carlbaker@idaho'));
    }

    public function testIsValidGroupCodePattern()
    {
        $this->assertTrue(Regex::isValidGroupCodePattern('G32'));
        $this->assertFalse(Regex::isValidGroupCodePattern('G101'));
    }

    public function testIsValidLessonCodePattern()
    {
        $this->assertTrue(Regex::isValidLessonCodePattern('G32L15'));
        $this->assertFalse(Regex::isValidLessonCodePattern('G01L4'));
    }

    public function testIsValidOldStudentCodePattern()
    {
        $this->assertTrue(Regex::isValidOldStudentCodePattern('S5ce36e1c38b4b'));
        $this->assertFalse(Regex::isValidOldStudentCodePattern('S5fb85708ef0aa5Z36254424'));
    }

    public function testIsValidStudentCodePattern()
    {
        $this->assertTrue(Regex::isValidStudentCodePattern('S5fb85708ef0aa5Z36254424'));
        $this->assertFalse(Regex::isValidStudentCodePattern('S5ce36e1c38b4b'));
    }

    public function testIsValidTrainerCodePattern()
    {
        $this->assertTrue(Regex::isValidTrainerCodePattern('U5fb85708ef0ba5Z29066783'));
        $this->assertFalse(Regex::isValidTrainerCodePattern('U5ce36e1c38b4b'));
    }

    public function testParseCompositeEmail()
    {
        $object1 = Regex::parseCompositeEmail('carlbaker@gmail.com');
        $object2 = Regex::parseCompositeEmail('carlbaker@gmail.com-nate');
        $object3 = Regex::parseCompositeEmail('carlb');
        $object4 = Regex::parseCompositeEmail('carlbaker@gmail.com-nate12');
        $this->assertTrue($object1->success);
        $this->assertEquals('carlbaker@gmail.com', $object1->email);

        $this->assertTrue($object2->success);
        $this->assertEquals('carlbaker@gmail.com', $object2->email);
        $this->assertEquals('nate', $object2->student);

        $this->assertFalse($object3->success);

        $this->assertTrue($object4->success);
        $this->assertEquals('nate12', $object4->student);
    }
}
