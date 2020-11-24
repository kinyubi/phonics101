<?php

namespace Tests\ReadXYZ\Enum;

use App\ReadXYZ\Enum\MasteryLevel;
use PHPUnit\Framework\TestCase;

class MasteryLevelTest extends TestCase
{

    public function testEquals()
    {
        $noLevel = new MasteryLevel(MasteryLevel::NONE);
        $another = new MasteryLevel('none');
        $this->assertTrue($noLevel->equals($another));
    }

    public function testGetKey()
    {
        $level = new MasteryLevel('mastered');
        $key = $level->getKey();
        $this->assertEquals('MASTERED', $key);
    }

    public function testGetValue()
    {
        $noLevel = new MasteryLevel(MasteryLevel::NONE);
        $this->assertEquals('none', $noLevel->getValue());
    }

    public function testIsValid()
    {
        $this->assertFalse(MasteryLevel::isValid(5));
        $this->assertTrue(MasteryLevel::isValid('mastered'));
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
            ['none', 'NONE'], ['advancing', 'ADVANCING'], ['mastered', 'MASTERED'], ["XXX", false]
        ];
    }

    public function testToArray()
    {
        $array = MasteryLevel::toArray();
        $this->assertCount(3, $array);
        $this->assertTrue(isAssociative($array));
    }

    public function testToIntegral()
    {
        $this->assertEquals(0, MasteryLevel::toIntegral('none'));
        $this->assertEquals(1, MasteryLevel::toIntegral('advancing'));
        $this->assertEquals(2, MasteryLevel::toIntegral('mastered'));
    }

    /**
     * values() returns an array of MasteryLevel object not a scalar value
     */
    public function testToSqlValue()
    {
        $this->assertEquals(MasteryLevel::NONE, MasteryLevel::toSqlValue(0));
        $this->assertEquals(MasteryLevel::ADVANCING, MasteryLevel::toSqlValue(1));
        $this->assertEquals(MasteryLevel::MASTERED, MasteryLevel::toSqlValue(2));
    }

}
