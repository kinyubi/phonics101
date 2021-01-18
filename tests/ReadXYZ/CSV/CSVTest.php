<?php

namespace Tests\ReadXYZ\CSV;

use App\ReadXYZ\CSV\CSV;
use PHPUnit\Framework\TestCase;

class CSVTest extends TestCase
{
    public function testListToArray()
    {
        $csv = CSV::getInstance();
        $array = $csv->listToArray("dog, cat, mouse");
        $this->assertCount(3, $array);
        //verify values get trimmed
        $this->assertEquals('dog', $array[0]);

        $array = $csv->listToArray('');
        $this->assertTrue(is_array($array) && empty($array));
    }

    public function testQuoteList()
    {
        $csv = CSV::getInstance();
        $quotedList = $csv->quoteList("dog, cat, mouse");
        $this->assertTrue($quotedList == "'dog','cat','mouse'");

        $quotedList = $csv->quoteList("Don't, say, I, can't");
        $this->assertTrue($quotedList == "'Don\'t','say','I','can\'t'");
    }

    public function testStretchListToArray()
    {
    }
}
