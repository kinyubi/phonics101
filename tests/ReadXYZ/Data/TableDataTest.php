<?php

namespace Tests\ReadXYZ\Data;

use App\ReadXYZ\Data\TableData;
use PHPUnit\Framework\TestCase;

class TableDataTest extends TestCase
{

    public function testAll()
    {
        $keychainTable = new TableData('abc_groups');
        $args = $keychainTable->getTwigArguments();
        $this->assertCount(5, $args['fields']);
        $groupCode = $args['fields']['groupCode'] ?? null;
        $this->assertNotNull($groupCode);
        $this->assertTrue($groupCode->isKey);
        $pct = floatval($groupCode->width);
        $this->assertTrue(($pct > 9.0) && ($pct < 10.0));
        $count = count($args['data']);
        $this->assertTrue(($count > 10) && ($count < 20));
    }

}
