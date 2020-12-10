<?php

namespace Tests\ReadXYZ\Data;

use App\ReadXYZ\Data\KeychainData;
use App\ReadXYZ\Enum\QueryType;
use PHPUnit\Framework\TestCase;

class KeychainDataTest extends TestCase
{

    public function testGet()
    {
        $data = new KeychainData();
        $tags = ['fox' => 'Fox' , '9' => 'Chip', 'G10' => 'Rac', 'k11' => 'Faw'];
        foreach ($tags as $tag => $expected) {
            $actual = $data->get($tag);
            $this->assertStringStartsWith($expected, $actual->friendlyName);
        }
        $actual = $data->get(99);
        $this->assertNull($actual);
    }

    public function testDelete()
    {
        $data = new KeychainData();
        $count = $data->getCount();
        $query = "SELECT * FROM abc_keychain WHERE keychainCode='k99'";
        $exists = $data->throwableQuery($query, QueryType::EXISTS);
        $affected = $data->insertOrUpdate(99, 'test');
        $actual = $data->get(99);
        $this->assertEquals('G99', $actual->groupCode);
        $finalCount = $exists ? $count : $count + 1;
        $this->assertEquals($finalCount, $data->getCount());
        $data->delete(99);
        $exists = $data->throwableQuery($query, QueryType::EXISTS);
        $finalCount = $data->getCount();
        $this->assertEquals($count, $finalCount);
    }

    public function testInsertOrUpdate()
    {
    }
}
