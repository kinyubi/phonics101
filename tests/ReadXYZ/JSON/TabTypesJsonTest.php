<?php

namespace Tests\ReadXYZ\JSON;

use App\ReadXYZ\Enum\Regex;
use App\ReadXYZ\JSON\TabTypesJson;
use PHPUnit\Framework\TestCase;

class TabTypesJsonTest extends TestCase
{

    public function testGet()
    {
        $j = TabTypesJson::getInstance();
        $i = $j->get('practice');
        $this->assertEquals('practice', $i->tabTypeId);
        $this->assertEquals('Practice', $i->tabDisplayAs);
        $x = $j->get('missing');
        $this->assertNull($x);
    }
}
