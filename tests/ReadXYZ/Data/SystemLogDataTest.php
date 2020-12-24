<?php

namespace Tests\ReadXYZ\Data;

use App\ReadXYZ\Data\SystemLogData;
use App\ReadXYZ\Enum\LogLevel;
use App\ReadXYZ\Helpers\Debug;
use PHPUnit\Framework\TestCase;

class SystemLogDataTest extends TestCase
{

    public function testAdd()
    {
        $logData = new SystemLogData();
        $preCount = $logData->getCount();
        $logData->add(LogLevel::INFO, 'Trace data.', 'This was a unit test.');
        $postCount = $logData->getCount();
        $this->assertEquals($postCount, $preCount+1);
    }
}
