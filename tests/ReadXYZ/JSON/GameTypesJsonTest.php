<?php

namespace Tests\ReadXYZ\JSON;

use App\ReadXYZ\JSON\GameTypesJson;
use PHPUnit\Framework\TestCase;

class GameTypesJsonTest extends TestCase
{

    public function testGet()
    {
        $gameTypeData = GameTypesJson::getInstance();
        $gameType = $gameTypeData->get('tic-tac-toe');
        $this->assertIsObject($gameType);
        $this->assertEquals('Tic-Tac-Toe', $gameType->gameTitle);
        $this->assertTrue($gameType->active);

        $gameType = $gameTypeData->get('ttt');
        $this->assertNull($gameType);
    }

    public function testExists()
    {
        $gameJson = GameTypesJson::getInstance();
        $this->assertTrue($gameJson->exists('tic-tac-toe'));
        $this->assertTrue($gameJson->exists('Tic-Tac-Toe'));
        $this->assertFalse($gameJson->exists('xx'));
    }

    public function testGetUniversal()
    {
        $gameJson = GameTypesJson::getInstance();
        $u = $gameJson->getUniversal();
        $this->assertGreaterThanOrEqual(2, count($u) );
    }

    public function testGetAll()
    {
        $gameTypeData = GameTypesJson::getInstance();
        $gameTypes = $gameTypeData->getAll();
        $this->assertGreaterThanOrEqual($gameTypeData->getCount(), count($gameTypes) );
    }

    public function testGetAllActive()
    {
        $gameTypeData = GameTypesJson::getInstance();
        $gameTypes = $gameTypeData->getAll();
        $this->assertCount($gameTypeData->getCount(), $gameTypes);
    }

}
