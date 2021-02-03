<?php

namespace Tests\ReadXYZ\JSON;

use App\ReadXYZ\Enum\Regex;
use App\ReadXYZ\JSON\GroupsJson;
use PHPUnit\Framework\TestCase;

class GroupsJsonTest extends TestCase
{
    public function testGet()
    {
        $j = GroupsJson::getInstance();
        $a = $j->get('a');
        $b = $j->get('/a/');
        $this->assertEquals(2, $a->ordinal);
        $this->assertEquals(2, $b->ordinal);
    }

    public function testGetAll()
    {
        $j = GroupsJson::getInstance();
        $a = $j->getAll();
        $this->assertCount(12, $a);
    }

    public function testGetCount()
    {
        $j = GroupsJson::getInstance();
        $ct = $j->getCount();
        $this->assertEquals(12, $ct);
    }

    public function testGetGroupCode()
    {
        $j = GroupsJson::getInstance();
        $this->assertEquals('magic_e', $j->getGroupCode('Magic e'));
        $this->assertEquals('magic_e', $j->getGroupCode('magic_e'));
        $this->assertFalse($j->getGroupCode('xx'));
    }

    public function testGetGroupCodeToNameMap()
    {
        $j = GroupsJson::getInstance();
        $arr = $j->getGroupCodeToNameMap();
        $this->assertCount(12, $arr);
        $this->assertContains('magic_e', array_keys($arr));
    }

    public function testGetGroupName()
    {
        $j = GroupsJson::getInstance();
        $this->assertEquals('Magic e', $j->getGroupName('Magic e'));
        $this->assertEquals('Magic e', $j->getGroupName('magic_e'));
        $this->assertFalse($j->getGroupName('xx'));
    }


    public function testGetGroupOrdinal()
    {
        $j = GroupsJson::getInstance();
        $this->assertEquals(1, $j->getGroupOrdinal('Letters'));
        $this->assertEquals(12, $j->getGroupOrdinal('Phonics 2'));
        $this->assertFalse($j->getGroupOrdinal('xx'));

    }


}
