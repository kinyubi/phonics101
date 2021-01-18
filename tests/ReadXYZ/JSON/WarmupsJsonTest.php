<?php

namespace Tests\ReadXYZ\JSON;

use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\JSON\WarmupsJson;
use PHPUnit\Framework\TestCase;

class WarmupsJsonTest extends TestCase
{

    public function testExists()
    {
        $j = WarmupsJson::getInstance();
        $this->assertTrue($j->exists('at'));
        $this->assertTrue($j->exists('igh'));
        $this->assertFalse($j->exists('xx'));
    }

    public function testGet()
    {
        $j = WarmupsJson::getInstance();
        $at = $j->get('at');
        $this->assertEquals('at', $at->lessonId);
        $this->assertTrue(Util::startsWith('Script', $at->instructions));
        $this->assertCount(6, $at->warmupItems);
        $item = $at->warmupItems[1];
        $this->assertTrue(Util::startsWith("What is the", $item->directions));
        $this->assertCount(4, $item->parts);
        $this->assertEquals("mat", $item->parts[3]);
    }

    public function testGetAll()
    {
        $j = WarmupsJson::getInstance();
        $this->assertCount(104, $j->getAll());
    }

    public function testGetCount()
    {
        $j = WarmupsJson::getInstance();
        $this->assertEquals(104, $j->getCount());
    }
}
