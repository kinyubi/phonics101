<?php

namespace Tests\ReadXYZ\Data;

use App\ReadXYZ\Data\GeneralData;
use App\ReadXYZ\Enum\DbVersion;
use PHPUnit\Framework\TestCase;

class GeneralDataTest extends TestCase
{

    public function testDoesDatabaseExist()
    {
        $this->assertTrue((new GeneralData())->doesDatabaseExist(DbVersion::READXYZ0_PHONICS));
        $this->assertFalse((new GeneralData())->doesDatabaseExist(DbVersion::READXYZ0_PHONICS . 'x') );

    }

    public function testDoesTableExist()
    {
        $this->assertTrue((new GeneralData(DbVersion::READXYZ0_1))->doesTableExist('abc_Users'));
        $this->assertFalse((new GeneralData(DbVersion::READXYZ0_PHONICS))->doesTableExist('abc_Users'));
    }

    public function testGetAllTables()
    {
        $genPhonics = new GeneralData();
        $gen01 = new GeneralData(DbVersion::READXYZ0_1);

        $this->assertCount(3, $gen01->getAllTables());

        $this->assertTrue(count($genPhonics->getAllTables()) > 6);
    }

    public function testGetAllViews()
    {
        $genPhonics = new GeneralData();
        $gen01 = new GeneralData(DbVersion::READXYZ0_1);

        $this->assertCount(2, $gen01->getAllViews());
        $this->assertContains('vw_accordion', $genPhonics->getAllViews());
        $this->assertContains('vw_users_without_students', $gen01->getAllViews());

        $this->assertNotContains('vw_accordion', $gen01->getAllViews());
        $this->assertNotContains('vw_users_without_students', $genPhonics->getAllViews());
    }

    public function testGetDynamicTables()
    {
        $genPhonics = new GeneralData();
        $tables = $genPhonics->getDynamicTables();
        $this->assertContains('abc_students', $tables);
        $this->assertNotContains('abc_tabTypes', $tables);
    }

    public function testGetFixedTables()
    {
        $genPhonics = new GeneralData();
        $tables = $genPhonics->getFixedTables();
        $this->assertNotContains('abc_students', $tables);
        $this->assertContains('abc_tabtypes', $tables);
    }

    public function testSmartQuotes()
    {
        $genPhonics = new GeneralData();
        $data = "bands,camps,facts,hands,lambs,masks,pants,tasks,bends,dents,desks,helps,melts,rents";
        $quoted = $genPhonics->smartQuotes($data);
        $jsonQuoted = $genPhonics->encodeJsonQuoted($data);
        $this->assertEquals($quoted, $jsonQuoted);
    }
}
