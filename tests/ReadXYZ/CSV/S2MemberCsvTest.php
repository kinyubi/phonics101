<?php

namespace Tests\ReadXYZ\CSV;

use App\ReadXYZ\CSV\S2MemberCsv;
use PHPUnit\Framework\TestCase;

class S2MemberCsvTest extends TestCase
{

    public function testGetInstance()
    {
        $csv = S2MemberCsv::getInstance();
        $this->assertTrue(($csv instanceof S2MemberCsv));
        $r = $csv->getRecord('lisal');
        $this->assertEquals('Lisa', $r['First Name']);
    }
}
