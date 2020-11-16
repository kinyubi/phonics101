<?php

namespace Tests\Data;

use App\ReadXYZ\Data\StudentLessonsData;
use App\ReadXYZ\Enum\TimerType;
use App\ReadXYZ\Models\Session;
use PHPUnit\Framework\TestCase;

class StudentLessonsDataTest extends TestCase
{

    public function testUpdateMastery()
    {
    }

    public function testUpdateTimedTest()
    {
        $session = new Session();
        $this->assertEquals('TEST', $session->getCurrentLessonCode());
        $testTimer = new TimerType(TimerType::TEST);
        $fluencyTimer = new TimerType(TimerType::FLUENCY);
        $timers = [$testTimer, $fluencyTimer];
        foreach ($timers as $timer) {
            $studentLessonsData = new StudentLessonsData();
            $studentLessonsData->clearTimedTest($timer);
            $times = $studentLessonsData->getTimedTest($timer);
            $this->assertCount(0, $times);
            for ($i=0; $i<12; $i++) {
                $studentLessonsData->updateTimedTest($timer, 12 - $i);
            }
            $times = $studentLessonsData->getTimedTest($timer);
            $this->assertCount(8, $times);
            for ($i=0; $i<12; $i++) {
                $this->assertContains($i, $times);
            }
            $this->assertNotContains(9, $times);
        }

    }
}
