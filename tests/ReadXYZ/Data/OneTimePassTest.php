<?php

namespace Tests\ReadXYZ\Data;

use App\ReadXYZ\Data\OneTimePass;
use App\ReadXYZ\Data\PhonicsDb;
use PHPUnit\Framework\TestCase;

class OneTimePassTest extends TestCase
{
    public function testConstructor()
    {
        $otp = new OneTimePass();
        $this->assertTrue($otp instanceof OneTimePass);
    }

    public function testAdd()
    {
        $query = 'SELECT * FROM abc_onetime_pass';
        $phonicsDb = new PhonicsDb();
        $result = $phonicsDb->queryAndGetCount($query);
        $countBefore = $result->wasSuccessful() ? $result->getResult() : -1;
        $otp = new OneTimePass();
        $hash = $otp->add('test');
        $this->assertEquals(32, strlen($hash));
        $result = $phonicsDb->queryAndGetCount($query);
        $countAfter = $result->wasSuccessful() ? $result->getResult() : -1;
        $this->assertEquals($countBefore + 1, $countAfter);
        $phonicsDb->queryStatement("DELETE FROM abc_onetime_pass WHERE hash = '$hash'");
    }

    public function testDecodeAndDelete()
    {
        $query = 'SELECT * FROM abc_onetime_pass';
        $phonicsDb = new PhonicsDb();
        $result = $phonicsDb->queryAndGetCount($query);
        $countBefore = $result->wasSuccessful() ? $result->getResult() : -1;
        $this->assertGreaterThanOrEqual(0, $countBefore);
        $otp = new OneTimePass();
        $hash = $otp->add('test');
        $this->assertEquals(32, strlen($hash));
        $result = $phonicsDb->queryAndGetCount($query);
        $countAfter = $result->wasSuccessful() ? $result->getResult() : -1;
        $this->assertEquals($countBefore + 1, $countAfter);
        $user = $otp->decodeAndDelete($hash);
        $this->assertEquals('test', $user);
        $result = $phonicsDb->queryAndGetCount($query);
        $countAfter = $result->wasSuccessful() ? $result->getResult() : -1;
        $this->assertEquals($countBefore, $countAfter);
    }
}
