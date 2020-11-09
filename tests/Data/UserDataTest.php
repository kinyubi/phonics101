<?php

namespace Tests\Data;

use App\ReadXYZ\Data\UserData;
use PHPUnit\Framework\TestCase;

class UserDataTest extends TestCase
{

    public function testGetUserId()
    {
        $userData = new UserData();
        $id = $userData->getUserId('MickeyMouse');
        $this->assertEmpty($id);
        $id = $userData->getUserId('carlb');
        $this->assertEquals('U5eb35004bbea9', $id);
    }
}
