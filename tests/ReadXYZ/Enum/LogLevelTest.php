<?php

namespace Tests\ReadXYZ\Enum;

use App\ReadXYZ\Enum\LogLevel;
use PHPUnit\Framework\TestCase;

class LogLevelTest extends TestCase
{

    public function testGetIntegral()
    {
        $this->assertEquals(0, LogLevel::getIntegral('info'));
        $this->assertEquals(4, LogLevel::getIntegral(LogLevel::FATAL));
    }
}
