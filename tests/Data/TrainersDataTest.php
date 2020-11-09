<?php

namespace Tests\Data;

use App\ReadXYZ\Data\TrainersData;
use PHPUnit\Framework\TestCase;

class TrainersDataTest extends TestCase
{

    public function testGetTrainerId()
    {
        $trainersData = new TrainersData();
        $id = $trainersData->getTrainerId('nobody');
        $this->assertEquals(0, $id);

        $id = $trainersData->getTrainerId('test');
        $this->assertGreaterThan(0, $id);
    }

    public function testGetHash()
    {
        $trainersData = new TrainersData();
        $id = $trainersData->getHash('nobody');
        $this->assertEmpty( $id);

        $id = $trainersData->getTrainerId('test');
        $this->assertNotEmpty($id);
    }

    public function testVerifyPassword()
    {
        //known existing can be verified
        $trainersData = new TrainersData();
        $verified = $trainersData->verifyPassword('test', 'test');
        $this->assertTrue($verified);

        //known existing with wrong password fails
        $verified = $trainersData->verifyPassword('test', 'whatever');
        $this->assertFalse($verified);

        //known non-existing will fail
        $verified = $trainersData->verifyPassword('nobody', 'test');
        $this->assertFalse($verified);
    }
    public function testAdd()
    {
        $trainersData = new TrainersData();
        $result = $trainersData->add('test_user', 'FirstTest', 'LastTest', 'trainer', 'test');
    }
}
