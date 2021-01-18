<?php

namespace Tests\ReadXYZ\JSON;

use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\JSON\KeyChainJson;
use PHPUnit\Framework\TestCase;

class KeyChainJsonTest extends TestCase
{

    public function testExists()
    {
        $j = KeyChainJson::getInstance();
        $this->assertTrue($j->exists('phonics_1'));
        $this->assertTrue($j->exists('Phonics 1'));
        $this->assertFalse($j->exists('xx'));
    }

    public function testGet()
    {
        $j = KeyChainJson::getInstance();
        $a = $j->get('letters');
        $this->assertTrue(Util::contains('1.png', $a->fileName));
        $this->assertTrue(Util::contains('Elephant', $a->friendlyName));
    }

    public function testGetAll()
    {
        $j = KeyChainJson::getInstance();
        $a = $j->getAll();
        $this->assertCount(12, $a);
    }

    public function testGetCount()
    {
        $j = KeyChainJson::getInstance();
        $ct = $j->getCount();
        $this->assertEquals(12, $ct);
    }
}
