<?php

namespace Tests\Lessons;

use App\ReadXYZ\Helpers\Util;
use Peekmo\JsonPath\JsonStore;
use PHPUnit\Framework\TestCase;
use App\ReadXYZ\Secrets\Access;
use App\ReadXYZ\Lessons\Lessons;

class LessonsTest extends TestCase
{

    public function testGetAllLessonNames()
    {
        $lessons = Lessons::getInstance();
        $names = $lessons->getAllLessonNames();
        $this->assertGreaterThan(90, $names);
    }

    public function testLessonExists()
    {
        $lessons = Lessons::getInstance();
        $inputFile = Util::getReadXyzSourcePath('resources/unifiedLessons.json');
        $json = file_get_contents($inputFile);
        $all = json_decode($json);
        foreach ($all->lessons->blending as $key => $lessonArray) {
            $this->assertTrue($lessons->lessonExists($key));
        }
        $this->assertFalse($lessons->lessonExists(''));
    }

    public function testValidateLessonName()
    {
        // The difference between this and lessonExists is that if we pass in the empty string here
        // it will get the current lesson name which should always be valid.
        $lessons = Lessons::getInstance();
        $this->assertTrue($lessons->validateLessonName(''));
    }

    public function testGetAndGetNextAndSetCurrentLesson()
    {
        $lessons = Lessons::getInstance();
        $names = $lessons->getAllLessonNames();
        for ($i=0; $i<count($names) -1; $i+=10) {
            $setName = $names[$i];
            $lessons->setCurrentLesson($setName);
            $getName = $lessons->getCurrentLessonName();
            $nextName = $lessons->getNextLessonName();
            $this->assertEquals($setName, $getName);
            $this->assertEquals($names[$i+1], $nextName);
        }
    }

    public function testMaxLengths()
    {
        $lessons = Lessons::getInstance();
        $maxLengths = $lessons->getMaxLengths();
        $this->assertGreaterThan(0, $maxLengths['stretchList']);
    }

    public function testInsertFromJson()
    {
        $inputFile = Util::getReadXyzSourcePath('resources/unifiedLessons.json');
        $json = file_get_contents($inputFile);
        $this->store = new JsonStore($json);
        $all = json_decode($json);
    }

    public function testGetAccordionList()
    {
    }

}
