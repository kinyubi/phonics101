<?php

namespace Tests\ReadXYZ\JSON;

use App\ReadXYZ\Enum\MasteryLevel;
use App\ReadXYZ\JSON\LessonsJson;
use App\ReadXYZ\Lessons\Lesson;
use PHPUnit\Framework\TestCase;

class LessonsJsonTest extends TestCase
{

    public function testExists()
    {
        $j = LessonsJson::getInstance();
        $this->assertTrue($j->exists('at'));
        $this->assertFalse($j->exists('xx'));
    }

    public function testGet()
    {
        $j = LessonsJson::getInstance();
        $a = $j->get('lower');
        $z = $j->get('/igh/');
        $this->assertIsObject($a);
        $this->assertEquals('letters', $a->groupCode);
        $this->assertCount(21, $a->wordLists['practice']);
        $this->assertCount(9, $a->wordLists['mastery']);
        $this->assertCount(9, $a->wordLists['test']);
        $this->assertEquals('Consonants 2', $a->lessonName);
        $this->assertStringContainsString('Consonant', $a->book);
        $this->assertEquals('tic-tac-toe', $a->games['practice'][0]->gameTypeId);
        $this->assertIsObject($z);
    }

    public function testGetAccordion()
    {
        $j = LessonsJson::getInstance();
        $a = $j->getAccordion();
        $this->assertIsArray($a);
        $this->assertEquals('Letters', $a['letters']['groupName']);
        $this->assertEquals('Vowels', $a['letters']['lessons']['vowels']['lessonName']);
        $this->assertEquals(0, $a['letters']['lessons']['vowels']['mastery']);
    }

    public function testGetAccordionWithMastery()
    {
        $j = LessonsJson::getInstance();
        $a = $j->getAccordionWithMastery('S5d6fc4866e9e10Z123456789' );
        $this->assertIsArray($a);
        $advancing = $mastered = 0;
        foreach ($a as $groupCode => $lessons) {
            foreach ($lessons['lessons'] as $lessonId => $item) {
                if ($item['mastery'] == 1) $advancing++;
                if ($item['mastery'] == 2) $mastered++;
            }
        }
        $this->assertGreaterThan(0, $advancing);
        $this->assertGreaterThan(0, $mastered);

    }

    public function testGetAll()
    {
        $j = LessonsJson::getInstance();
        $a = $j->getAll();
        $this->assertIsArray($a);
        $this->assertTrue($a['at'] instanceof Lesson);
        $this->assertGreaterThan(100, count($a));
    }

    public function testGetCount()
    {
        $j = LessonsJson::getInstance();
        $this->assertGreaterThan(100, $j->getCount());
    }

    public function testGetGroupCode()
    {
        $j = LessonsJson::getInstance();
        $c = $j->getGroupCode('u_e+u');
        $this->assertEquals('magic_e', $j->getGroupCode('u_e+u'));
        $this->assertEquals('magic_e', $j->getGroupCode('u_e + /u/'));
        $this->assertEquals('magic_e', $j->getGroupCode('Contrast u_e /ue/ and u /uh/'));
        $this->assertFalse($j->getGroupCode('freaky friday'));
    }

    public function testGetGroupName()
    {
        $j = LessonsJson::getInstance();
        $this->assertEquals('Magic e', $j->getGroupName('u_e+u'));
        $this->assertEquals('Magic e', $j->getGroupName('u_e + /u/'));
        $this->assertEquals('Magic e', $j->getGroupName('Contrast u_e /ue/ and u /uh/'));
        $this->assertFalse($j->getGroupName('freaky friday'));
    }

    public function testGetLesson()
    {
        $j = LessonsJson::getInstance();
        $a = $j->getLesson('lower');
        $z = $j->getLesson('/igh/');
        $this->assertIsObject($a);
        $this->assertEquals('letters', $a->groupCode);
        $this->assertCount(21, $a->wordLists['practice']);
        $this->assertCount(9, $a->wordLists['mastery']);
        $this->assertCount(9, $a->wordLists['test']);
        $this->assertEquals('Consonants 2', $a->lessonName);
        $this->assertStringContainsString('Consonant', $a->book);
        $this->assertEquals('tic-tac-toe', $a->games['practice'][0]->gameTypeId);
        $this->assertIsObject($z);
    }

    public function testGetLessonCode()
    {
        $j = LessonsJson::getInstance();
        $this->assertEquals('u_e_u', $j->getLessonCode('u_e+u'));
        $this->assertEquals('u_e_u', $j->getLessonCode('u_e + /u/'));
        $this->assertEquals('u_e_u', $j->getLessonCode('Contrast u_e /ue/ and u /uh/'));
        $this->assertFalse($j->getLessonCode('freaky friday'));
    }

    public function testGetLessonId()
    {
        $j = LessonsJson::getInstance();
        $this->assertEquals('u_e_u', $j->getLessonId('u_e+u'));
        $this->assertEquals('u_e_u', $j->getLessonId('u_e + /u/'));
        $this->assertEquals('u_e_u', $j->getLessonId('Contrast u_e /ue/ and u /uh/'));
        $this->assertFalse($j->getLessonId('freaky friday'));
    }

    public function testGetLessonName()
    {
        $j = LessonsJson::getInstance();
        $this->assertEquals('u_e + /u/', $j->getLessonName('u_e+u'));
        $this->assertEquals('u_e + /u/', $j->getLessonName('u_e + /u/'));
        $this->assertEquals('u_e + /u/', $j->getLessonName('Contrast u_e /ue/ and u /uh/'));
        $this->assertFalse($j->getLessonName('freaky friday'));
    }
}
