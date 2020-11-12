<?php

namespace App\ReadXYZ\Models;

//  a student is represented by a record in StudentTable
//  the student and his training records are in a single record

use App\ReadXYZ\Data\StudentLessonsData;
use ArrayObject;
use App\ReadXYZ\Data\PhonicsDb;
use App\ReadXYZ\Database\StudentTable;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Lessons\Lesson;
use App\ReadXYZ\Lessons\Lessons;
use RuntimeException;

class Student
{

    private static Student $instance;

    private Session $session;
    private bool    $isValidStudent;

    private function __construct()
    {
        $this->session = new Session();
        $this->isValidStudent = SESSION::hasActiveStudent();
    }

    public static function getInstance()
    {
        self::$instance = new Student();
        return self::$instance;
    }

    public function getStudentName()
    {
        return $this->session->getStudentName();
    }

    public function getCapitalizedStudentName(): string
    {
        return ucfirst($this->session->getStudentName());
    }


    /**
     * persists the specified lesson key as the current lesson.
     *
     * @param string $lessonName
     */
    public function saveLessonSelection(string $lessonName): void
    {
        if (!$this->isValidStudent) {
            throw new RuntimeException("Cannot save lesson selection without a student session.");
        }
        $this->session->updateLesson($lessonName);
    }

    /**
     * find a NEW lesson (ie: not in currentLessons).
     *
     * @return mixed
     */
    public function prepareCurrentForUpdate()
    {
        return $this->session->getCurrentLesson();
    }

    public function updateTestCurveCargo(int $seconds)
    {
        if ($this->session->getStudentId() == 0) {
            throw new RuntimeException("Cannot update learning curve without an active student.");
        }
        (new StudentLessonsData())->updateTimedTest('test', $seconds);
    }

    public function updateTestMastery(string $lessonKey, string $masteryType)
    {
        switch ($masteryType) {
            case 'Advancing':
                $this->cargo['currentLessons'][$lessonKey]['mastery'] = 1;
                //If it was previously mastered get rid of the mastery entry;
                if (isset($this->cargo['masteredLessons'][$lessonKey])) {
                    unset($this->cargo['masteredLessons'][$lessonKey]);
                }
                break;
            case 'Mastered':
                // you can't just copy an array, you need to CLONE it
                $cloneObject = new ArrayObject(
                    $this->cargo['currentLessons'][$lessonKey]
                );
                $clone = $cloneObject->getArrayCopy();
                $clone['mastery'] = 5;
                $this->cargo['masteredLessons'][$lessonKey] = $clone;
                unset($this->cargo['currentLessons'][$lessonKey]); // won't affect the clone
                break;

            default:
                assert(false, "Did not expect '$$masteryType' as a submit type");
        }
    }

    /**
     * the target method for the fluencyTimerForm when fluencySaveButton is pressed.
     */
    public function fluencyTimer()
    { // this is the timer function for Repeated Reading
        $currentLessonName = $this->prepareCurrentForUpdate();
        $seconds = intval($_POST['seconds']);
        $this->cargo['currentLessons'][$currentLessonName]['learningCurve'][time()] = $seconds;
        while (count($this->cargo['currentLessons'][$currentLessonName]['learningCurve']) > 8) {
            array_shift($this->cargo['currentLessons'][$currentLessonName]['learningCurve']);
        }
        // we have mastered it after the first try but it is still in 'currentLessons'
        $this->cargo['currentLessons'][$currentLessonName]['mastery'] = 5;

        // update the student record
    }

    /**
     * This function is the OnSubmit callback for the Advancing and Mastered buttons on the Test tab
     * To get get here one of those buttons has been pressed.
     *
     * @param array $postData the form fields in an array
     */
    public function testTimer(array $postData)
    {
        // $button = $postData['masteryType'] ?? '';
        // make sure there is a current lesson with the current name
        // if its already mastered, it will be put back in current
        $currentLessonName = $this->prepareCurrentForUpdate();
        $lessonKey = $postData['lessonName'] ?? ($postData['lessonKey'] ?? '');
        if (!isset($postData['lessonKey'])) {
            $postData['lessonKey'] = $lessonKey;
        }

        if (isset($postData['seconds'])) {
            $seconds = intval($postData['seconds'] ?? '0');
            $this->updateTestgCurveCargo($seconds);
        } elseif (isset($postData['masteryType'])) {
            $masteryType = $postData['masteryType'] ?? ($postData['P1'] ?? '');

            $curLessonKey = $this->cargo['currentLesson'];
            assert(!empty($curLessonKey), 'Should never be empty because we just mastered it');
            // depending on how well the student did, move up his expertise
            $this->updateTestMastery($curLessonKey, $masteryType);
        } else {
            assert(false, 'POST data did not have required fields.');
        }
    }

    public function getMasteredWords(): array
    {
        $db = new PhonicsDb();
        $query = "SELECT word from abc_usermastery WHERE studentID='{$this->studentID}'";
        $result = $db->queryAndGetScalarArray($query);
        return $result->wasSuccessful() ? $result->getResult() : [];
    }
}
