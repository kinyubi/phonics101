<?php

namespace Tests\ReadXYZ\Lessons;

use App\ReadXYZ\Lessons\Warmups;
use PHPUnit\Framework\TestCase;


class WarmupsTest extends TestCase
{

    public function testConstruct()
    {
        $warmups = Warmups::getInstance();
        $this->assertIsObject($warmups);
    }
}
