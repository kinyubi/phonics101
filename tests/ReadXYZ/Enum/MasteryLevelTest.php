<?php

namespace Tests\ReadXYZ\Enum;

use App\ReadXYZ\Enum\MasteryLevel;
use PHPUnit\Framework\TestCase;

class MasteryLevelTest extends TestCase
{

    public function testEquals()
    {
        $noLevel = new MasteryLevel(MasteryLevel::NONE);
        $another = new MasteryLevel(0);
        $this->assertTrue($noLevel->equals($another));
    }

    public function testGetKey()
    {
        $level = new MasteryLevel(2);
        $key = $level->getKey();
        $this->assertEquals('MASTERED', $key);
    }

    public function testGetSqlValue()
    {
        $level = new MasteryLevel(1);
        $value = $level->getSqlValue();
        $this->assertEquals('advancing', $value);
    }

    public function testGetValue()
    {
        $noLevel = new MasteryLevel(MasteryLevel::NONE);
        $this->assertEquals(0, $noLevel->getValue());
    }

    public function testIsValid()
    {
        $this->assertFalse(MasteryLevel::isValid(5));
        $this->assertTrue(MasteryLevel::isValid(2));
    }

    public function testIsValidKey()
    {
        $this->assertFalse(MasteryLevel::isValidKey("MASTER"));
        $this->assertTrue(MasteryLevel::isValidKey("ADVANCING"));
    }


    public function testKeys()
    {
        $keys = MasteryLevel::keys();
        $this->assertTrue(in_array("NONE", $keys));
        $this->assertCount(3, $keys);
    }

    /**
     * @dataProvider searchProvider()
     * @param $value
     * @param $expected
     */
    public function testSearch($value, $expected)
    {
        $this->assertSame($expected, MasteryLevel::search($value));
    }

    public function searchProvider()
    {
        return [
            [0, 'NONE'], [1, 'ADVANCING'], [2, 'MASTERED'], ["XXX", false]
        ];
    }

    public function testToArray()
    {
        $array = MasteryLevel::toArray();
        $this->assertCount(3, $array);
        $this->assertTrue(isAssociative($array));
    }

    public function testSqlValues()
    {
        $values = MasteryLevel::getSqlValues();
        $this->assertCount(3, $values);
        $this->assertFalse(isAssociative($values));
        $this->assertContains('advancing', $values);
    }

    /**
     * values() returns an array of MasteryLevel object not a scalar value
     */
    public function testGetValues()
    {
        $values = MasteryLevel::getValues();
        $this->assertCount(3, $values);
        $this->assertContains(0, $values);
        $this->assertFalse(isAssociative($values));
    }

    public function test__toString()
    {
        $level = new MasteryLevel(1);
        $str = (string) $level;
        $this->assertIsString($str);
        $this->assertEquals('1', $str);
    }


}
