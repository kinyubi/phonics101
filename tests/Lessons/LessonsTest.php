<?php

namespace ReadXYZ\Tests\Lessons;

use PHPUnit\Framework\TestCase;
use ReadXYZ\Helpers\Util;
use ReadXYZ\Lessons\Lessons;

class LessonsTest extends TestCase
{

    public function testGetAllLessonNames()
    {
        Util::fakeLogin();
        $lessons = Lessons::getInstance();
        $names = $lessons->getAllLessonNames();
        $this->assertGreaterThan(90, $names);
    }

    public function testLessonExists()
    {
        Util::fakeLogin();
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
        Util::fakeLogin();
        $lessons = Lessons::getInstance();
        $this->assertTrue($lessons->validateLessonName(''));
    }

    public function testGetAndGetNextAndSetCurrentLesson()
    {
        Util::fakeLogin();
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

    public function testGetAccordionList()
    {
    }

}
