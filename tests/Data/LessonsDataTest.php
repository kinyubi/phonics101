<?php

namespace Tests\Data;

use App\ReadXYZ\Data\LessonsData;
use App\ReadXYZ\JSON\UnifiedLessons;
use PHPUnit\Framework\TestCase;

class LessonsDataTest extends TestCase
{

    public function testInsertOrUpdate()
    {
        $unifiedLessons = new UnifiedLessons();
        $lessonName =  "/a/ + /i/";
        $lesson = $unifiedLessons::getDataAsStdClass()->lessons->blending->$lessonName;
        $lessonsTable = new LessonsData();
        $result = $lessonsTable->insertOrUpdate($lesson, 1);
        $this->assertTrue($result->wasSuccessful());

    }
}
