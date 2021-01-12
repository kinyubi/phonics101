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
        $this->assertInstanceOf(GameType::class, $gameType);
        $this->assertEquals('Tic-Tac-Toe', $gameType->gameTitle);
        $this->assertEquals(true, $gameType->active);
    }

    public function testJson()
    {

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
