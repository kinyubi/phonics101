<?php

namespace Tests\ReadXYZ\Lessons;

use App\ReadXYZ\Lessons\TabTypes;
use PHPUnit\Framework\TestCase;

class TabTypesTest extends TestCase
{

    public function testGetTabInfo()
    {
        $tabTypes = TabTypes::getInstance();
        $fluencyTabInfo = $tabTypes->getTabInfo('fluency');
        $readingTabInfo = $tabTypes->getTabInfo('reading');
        $this->assertEquals($fluencyTabInfo->script, $readingTabInfo->script);
        $this->assertEquals('Reading', $fluencyTabInfo->tabDisplayAs);
        $this->assertGreaterThan(50, strlen($readingTabInfo->script));
    }



    public function testIsValid()
    {
        $tabTypes = TabTypes::getInstance();

        $goodTabs = [
            'book', 'fluency', 'intro', 'mastery', 'practice', 'spell', 'test',
            'warmup', 'write', 'words', 'reading', 'Stretch', 'Spinner'
        ];
        $badTabs = ['dog', 'cat', 'rat'];
        foreach ($goodTabs as $goodTab) {
            $this->assertTrue($tabTypes->isValid($goodTab));
        }
        foreach ($badTabs as $badTab) {
            $this->assertFalse($tabTypes->isValid($badTab));
        }
    }
}
