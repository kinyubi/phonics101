<?php

namespace Tests\ReadXYZ\Data;

use App\ReadXYZ\Data\GeneralData;
use App\ReadXYZ\Data\StudentLessonsData;
use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Enum\TimerType;
use App\ReadXYZ\JSON\LessonsJson;
use App\ReadXYZ\Models\Session;
use PHPUnit\Framework\TestCase;

class StudentLessonsDataTest extends TestCase
{

    public function testUpdateMastery()
    {
    }

    public function testUpdateTimedTest()
    {
        $this->assertEquals('TEST', Session::getCurrentLessonCode());
        $testTimer = new TimerType(TimerType::TEST);
        $fluencyTimer = new TimerType(TimerType::FLUENCY);
        $timers = [$testTimer, $fluencyTimer];
        foreach ($timers as $timer) {
            $studentLessonsData = new StudentLessonsData();
            $studentLessonsData->clearTimedTest($timer);
            $times = $studentLessonsData->getTimedTest($timer);
            $this->assertCount(0, $times);
            for ($i=0; $i<12; $i++) {
                $studentLessonsData->updateTimedTest($timer, 12 - $i, time());
            }
            $times = $studentLessonsData->getTimedTest($timer);
            $this->assertCount(8, $times);
            for ($i=0; $i<12; $i++) {
                $this->assertContains($i, $times);
            }
            $this->assertNotContains(9, $times);
        }

    }
    public function testInsertAll()
    {
        $studentCode = 'S5fb85708ef0b74Z27026968 ';
        $studentLessonsData = new StudentLessonsData($studentCode);
        $query = "DELETE FROM abc_student_lesson WHERE studentCode = '$studentCode'";
        $studentLessonsData->throwableQuery($query, QueryType::STATEMENT);

        $totalLessonCount = LessonsJson::getInstance()->getCount();
        $studentLessonsData->createStudentLessonAsNeeded('at');
        $mastery = $studentLessonsData->getLessonMastery();

        $originalMasteryCount = count($mastery);
        $studentLessonsData->insertAll();
        $mastery = $studentLessonsData->getLessonMastery();
        $finalCount = count($mastery);
        $this->assertEquals($totalLessonCount, $finalCount);
        $query = "DELETE FROM abc_student_lesson WHERE studentCode = '$studentCode'";
        $studentLessonsData->throwableQuery($query, QueryType::STATEMENT);
    }
}
