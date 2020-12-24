<?php

namespace Tests\ReadXYZ\Secrets;

use App\ReadXYZ\Secrets\Access;
use PDO;
use PHPUnit\Framework\TestCase;

class AccessTest extends TestCase
{

    public function testConnectLocalPdo()
    {
        $this->assertTrue(Access::connectLocalPdo() instanceof PDO);
    }

    public function testConnectRemotePdo()
    {
        $this->assertTrue(Access::connectRemotePdo() instanceof PDO);
    }
}
