<?php

namespace Tests\ReadXYZ\Helpers;

use PHPUnit\Framework\TestCase;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Secrets\Access;

class UtilTest extends TestCase
{

    public function testArrayToList()
    {
        $array = ['dog', 'cat', 'mouse'];
        $this->assertEquals("'dog','cat','mouse'", Util::arrayToList($array));
    }

    public function testContains()
    {
        $this->assertTrue(Util::contains('obin', 'Robin Hood'));
        $this->assertFalse(Util::contains('rob', 'Robin Hood'));
        $this->assertTrue(Util::contains(['bin', 'x'], 'Robin Hood'));
        $this->assertFalse(Util::contains(['x', 'y'], 'Robin Hood'));
    }

    public function testContains_ci()
    {
        $this->assertTrue(Util::contains_ci('obin', 'Robin Hood'));
        $this->assertTrue(Util::contains_ci('rob', 'Robin Hood'));
        $this->assertTrue(Util::contains_ci(['x', 'O'], 'Robin Hood'));
        $this->assertFalse(Util::contains_ci(['x', 'y'], 'Robin Hood'));
    }

    public function testConvertCamelToSnakeCase()
    {
        $this->assertEquals('dog_gone', Util::convertCamelToSnakeCase('dogGone'));
        $this->assertEquals('dog_gone', Util::convertCamelToSnakeCase('DogGone'));
        $this->assertEquals('dog_gone', Util::convertCamelToSnakeCase('dog_gone'));
    }

    public function testConvertLessonKeyToLessonName()
    {
        $this->assertEquals('/a/', Util::convertLessonKeyToLessonName('Blending./a/'));
    }

    public function testConvertLessonNameToLessonKey()
    {
        $this->assertEquals('Blending./a/', Util::convertLessonNameToLessonKey('/a/'));
    }

    public function testDbConnect()
    {
        $sqlObj = Access::dbConnect();
        $this->assertTrue(is_a($sqlObj, 'mysqli'));
    }

    public function testGetDateStamp()
    {
    }

    public function testGetHumanReadableDate()
    {
    }

    public function testGetHumanReadableDateTime()
    {
    }

    public function testGetInput()
    {
    }

    public function testGetPhonicsUrl()
    {
    }

    public function testGetProjectPath()
    {
    }

    public function testGetPublicPath()
    {
    }

    public function testGetReadXyzSourcePath()
    {
    }

    public function testIsLocal()
    {
    }

    public function testLeft()
    {
    }

    public function testQuoteList()
    {
    }

    public function testReslash()
    {
    }

    public function testSessionContinue()
    {
    }

    public function testStartsWith()
    {
    }

    public function testStartsWith_ci()
    {
    }

    public function testStretchListToArray()
    {
    }

    public function testStripExtraSlashes()
    {
    }

    public function testStripNameSpace()
    {
    }

    public function testTestingInProgress()
    {
    }
}
