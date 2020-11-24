<?php

namespace Tests\ReadXYZ\Data;

use App\ReadXYZ\Data\GameTypesData;
use App\ReadXYZ\POPO\GameType;
use PHPUnit\Framework\TestCase;

class GameTypesDataTest extends TestCase
{

    public function testGet()
    {
        $gameTypeData = new GameTypesData();
        $gameType = $gameTypeData->get('test');
        $this->assertInstanceOf(GameType::class, $gameType);
        $this->assertEquals('Test', $gameType->gameDisplayAs);
        $this->assertEquals('N', $gameType->active);
    }

    public function testGetAll()
    {
        $gameTypeData = new GameTypesData();
        $gameTypes = $gameTypeData->getAll();
        $this->assertGreaterThanOrEqual($gameTypeData->getCount(), count($gameTypes) );
    }

    public function testGetAllActive()
    {
        $gameTypeData = new GameTypesData();
        $gameTypes = $gameTypeData->getAll();
        $this->assertCount($gameTypeData->getCount(), $gameTypes);
    }

    public function testInsertOrUpdateStd()
    {
        $gameTypeData = new GameTypesData();
        $stdGameType = (object) [
            'gameTypeId'        => 'test2',
            'gameDisplayAs'     => 'Test',
            'thumbNailUrl'      => '',
            'cssClass'          => 'test',
            'belongsOnTab'      => 'test',
            'isUniversal'       => false,
            'universalGameUrl'  => '',
            'active'            => false
        ];
        $result = $gameTypeData->insertOrUpdateStd($stdGameType);
        $this->assertTrue($result->wasSuccessful());
    }
}
