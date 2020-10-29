<?php

namespace App\ReadXYZ\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use App\ReadXYZ\Helpers\Util;

class UtilTest extends TestCase
{

    public function testAddSoundClass()
    {
        $this->assertEquals('<sound>dog</sound>', Util::addSoundClass('/dog/'));
    }

    public function testArrayToList()
    {
        $array = ['dog', 'cat', 'mouse'];
        $this->assertEquals("'dog','cat','mouse'", Util::arrayToList($array));
    }

    public function testBuildActionsLink()
    {
        $result = Util::buildActionsLink('render', ['P1' => 'me', 'P2' => 'you', 'action' => 'love']);
        $this->assertEquals('/actions/render.php?P1=me&P2=you&action=love', $result);
    }

    public function testContains()
    {
        $this->assertTrue(Util::contains('Robin Hood', 'obin'));
        $this->assertFalse(Util::contains('Robin Hood', 'rob'));
        $this->assertTrue(Util::contains('Robin Hood', ['bin', 'x']));
        $this->assertFalse(Util::contains('Robin Hood', ['x', 'y']));
    }

    public function testContains_ci()
    {
        $this->assertTrue(Util::contains_ci('Robin Hood', 'obin'));
        $this->assertTrue(Util::contains_ci('Robin Hood', 'rob'));
        $this->assertTrue(Util::contains_ci('Robin Hood', ['x', 'O']));
        $this->assertFalse(Util::contains_ci('Robin Hood', ['x', 'y']));
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

    public function testCsvStringToArray()
    {
    }

    public function testCsvFileToArray()
    {
    }

    public function testDbConnect()
    {
        $sqlObj = Util::dbConnect();
        $this->assertTrue(is_a($sqlObj, 'mysqli'));
    }

    public function testDbTestOnlyConnect()
    {
        $sqlObj = Util::dbTestOnlyConnect();
        $this->assertTrue(is_a($sqlObj, 'mysqli'));
    }

    public function testFixTabName()
    {
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
