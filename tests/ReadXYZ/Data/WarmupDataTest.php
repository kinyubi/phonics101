<?php

namespace Tests\ReadXYZ\Data;

use App\ReadXYZ\Data\WarmupData;
use PHPUnit\Framework\TestCase;

class WarmupDataTest extends TestCase
{

    public function testGet()
    {
        $lessonCode = 'G01L02';
        $data = new WarmupData();
        $warmup = $data->get($lessonCode);
        $this->assertNotNull($warmup);
        $this->assertCount(6, $warmup->warmupItems);
        $this->assertEquals($lessonCode, $warmup->lessonCode);
        $this->assertNotEmpty($warmup->lessonName);
    }

    public function testExists()
    {
        $lessonCode = 'G01L02';
        $data = new WarmupData();
        $this->assertTrue($data->exists($lessonCode));
        $this->assertFalse($data->exists('xxx'));
    }

    // // tested by importJson which we know works
    // public function testInsert()
    // {
    // }
}
