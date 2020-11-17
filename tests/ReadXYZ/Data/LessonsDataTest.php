<?php

namespace Tests\ReadXYZ\Data;

use App\ReadXYZ\Data\LessonsData;
use PHPUnit\Framework\TestCase;

class LessonsDataTest extends TestCase
{

    public function testGet()
    {
        $data = new LessonsData();
        $lessonData = $data->get('at');

    }

    public function testGetLessonCode()
    {
    }

    public function testGetLessonDisplayAs()
    {
    }
}
