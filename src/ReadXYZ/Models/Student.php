<?php

namespace ReadXYZ\Models;

//  a student is represented by a record in StudentTable
//  the student and his training records are in a single record

use ArrayObject;
use ReadXYZ\Database\LessonResults;
use ReadXYZ\Database\StudentTable;
use ReadXYZ\Database\TrainingLog;
use ReadXYZ\Helpers\Debug;
use ReadXYZ\Helpers\Util;
use ReadXYZ\Lessons\Lesson;
use ReadXYZ\Lessons\Lessons;

class Student
{
    // mandatory properties (make sure they are in the $cargoStuff list)

    private static ?Student $instance = null; // should ONLY be false if we are about to add a new student
    public bool $isValidStudent = false;
    public string $studentID;
    public array $cargo; // this is the cargo of the student record

    // optional properties
    public int $prefDictationCount = 3;
    private string $currentScript;

    private function __construct()
    {
        $identity = Identity::getInstance();
        $studentID = $identity->getStudentId(); // hopefully not empty
        $this->studentID = $studentID;
        $this->currentScript = 'Blending'; // that's all we use now

        $s = StudentTable::getInstance();
        if (!empty($studentID)) {
            $this->cargo = $s->getCargoByKey($studentID); //returns the student record

            if (!empty($this->cargo)) { // found the student, should do some more validations
                $this->isValidStudent = true;
                Debug::printNice('Student Cargo', $this->cargo);
            } else { // we have a studentID, but no record in the student table for it.
                $this->isValidStudent = false;
            }
        } else { // we don't have a student record for this studentID.
            $this->isValidStudent = false;
        }
    }

    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new Student();
        }

        return self::$instance;
    }

    public function getStudentName()
    {
        return $this->cargo['enrollForm']['StudentName'];
    }

    public function getCapitalizedStudentName(): string
    {
        return ucfirst($this->cargo['enrollForm']['StudentName']);
    }

    public function selectByName($key): Lesson
    {
        $lessonName = Util::convertLessonKeyToLessonName(rawurldecode($key)); // gets rid of %20, etc if they are there
        $lessons = Lessons::getInstance();
        if (not($lessons->lessonExists($lessonName))) {
            trigger_error("SelectByName did not find '$lessonName' in lessons", E_USER_ERROR);
        } else {
            $lessons->setCurrentLesson($lessonName);

            return $lessons->getCurrentLesson();
        }
    }

    /**
     * persists the student cargo to the database.
     */
    public function saveSession()
    { // update changes to the student record
        if ($this->isValidStudent) {
            $identity = Identity::getInstance();
            $s = StudentTable::getInstance();
            $s->updateByKey($identity->getStudentId(), $this->cargo); // not serialized here
        }
    }

    /**
     * persists the specified lesson key as the current lesson.
     *
     * @param string $lessonKey the lesson key we want to make current
     */
    public function saveLessonSelection(string $lessonKey): void
    {
        // too simple, we simply update the cargo
        $this->cargo['currentLesson'] = $lessonKey;
        $cookie = Cookie::getInstance();
        $sessionId = Identity::getInstance()->getSessionId();
        if (($cookie->getStudentId() != $this->studentID) or ($cookie->getSessionId() != $sessionId)) {
            $cookie->setStudentId($this->studentID, $sessionId);
        }
        $lessonName = Util::convertLessonKeyToLessonName($lessonKey);
        if ($cookie->getCurrentLesson() != $lessonName) {
            $cookie->setCurrentLesson($lessonName);
        }
        $this->saveSession();
    }

    /**
     * find a NEW lesson (ie: not in currentLessons).
     *
     * @return mixed
     */
    public function prepareCurrentForUpdate()
    {
        $currentLessonName = $this->cargo['currentLesson'];

        // make sure current lesson is set up.  the special case is if the lessons is in MASTERED and
        //      not in CURRENT, then it is an old lesson that someone is adding to.  Copy it to CURRENT
        //      before updating.
        if (isset($this->cargo['masteredLessons'][$currentLessonName])) {
            //it shouldn't be in both places but
            if (!isset($this->cargo['currentLessons'][$currentLessonName])) {
                $this->cargo['currentLessons'][$currentLessonName] = $this->cargo['masteredLessons'][$currentLessonName];
            }
            unset($this->cargo['masteredLessons'][$currentLessonName]);
        }

        // maybe we have never seen this lesson before...  create all the elements
        if (!isset($this->cargo['currentLessons'][$currentLessonName])) {
            $this->cargo['currentLessons'][$currentLessonName] = [
                'timesPresented' => 0,
                'mastery' => 0,
                'learningCurve' => [],
                'testCurve' => [],
                'lastPresented' => '',
                'lastPresentedHuman' => '',
            ];
        }

        // make sure there is a 'learningCurve' record in the current lesson
        if (!isset($this->cargo['currentLessons'][$currentLessonName]['learningCurve'])) { // older records might not have it set
            $this->cargo['currentLessons'][$currentLessonName]['learningCurve'] = [];
        }
        // make sure there is a 'testCurve' record in the current lesson
        if (!isset($this->cargo['currentLessons'][$currentLessonName]['testCurve'])) {
            $this->cargo['currentLessons'][$currentLessonName]['testCurve'] = [];
        }

        // we have presented it so increment the count
        if (!isset($this->cargo['currentLessons'][$currentLessonName]['timesPresented'])) {
            $this->cargo['currentLessons'][$currentLessonName]['timesPresented'] = 0;
        }
        ++$this->cargo['currentLessons'][$currentLessonName]['timesPresented'];
        $this->cargo['currentLessons'][$currentLessonName]['lastPresented'] = time();
        $this->cargo['currentLessons'][$currentLessonName]['lastPresentedHuman'] = Util::getHumanReadableDateTime();

        return $currentLessonName;
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
        $this->saveSession();
    }

    /**
     * This function is the OnSubmit callback for the Advancing and Mastered buttons on the Test tab
     * To get get here one of those buttons has been pressed.
     *
     * @param array $postData the form fields in an array
     */
    public function testTimer($postData)
    {
        $button = $postData['masteryType'] ?? '';
        // make sure there is a current lesson with the current name
        // if its already mastered, it will be put back in current
        $currentLessonName = $this->prepareCurrentForUpdate();
        $lessonKey = $postData['lessonName'] ?? ($postData['lessonKey'] ?? '');
        if (!isset($postData['lessonKey'])) {
            $postData['lessonKey'] = $lessonKey;
        }

        if (isset($postData['seconds'])) {
            $seconds = intval(
                $postData['seconds'] ?? '0'
            );
            $this->cargo['currentLessons'][$currentLessonName]['testCurve'][time()] = $seconds;
            while (count($this->cargo['currentLessons'][$currentLessonName]['testCurve']) > 8) {
                array_shift($this->cargo['currentLessons'][$currentLessonName]['testCurve']);
            }

            $this->saveSession();
        } elseif (isset($postData['masteryType'])) {
            $masteryType = $postData['masteryType'] ?? ($postData['P1'] ?? '');
            $lessonResult = LessonResults::getInstance();
            $lessonResult->write($postData);

            TrainingLog::getInstance()->insertLog('LessonResult', $lessonKey, $masteryType);
            $curLessonKey = $this->cargo['currentLesson'];
            assert(!empty($curLessonKey), 'Should never be empty because we just mastered it');

            // depending on how well the student did, move up his expertise
            switch ($masteryType) {
                case 'Advancing':
                    $this->cargo['currentLessons'][$curLessonKey]['mastery'] = 1;
                    //If it was previously mastered get rid of the mastery entry;
                    if (isset($this->cargo['masteredLessons'][$curLessonKey])) {
                        unset($this->cargo['masteredLessons'][$curLessonKey]);
                    }
                    break;
                case 'Mastered':
                    // you can't just copy an array, you need to CLONE it
                    $cloneObject = new ArrayObject(
                        $this->cargo['currentLessons'][$curLessonKey]
                    );
                    $clone = $cloneObject->getArrayCopy();
                    $clone['mastery'] = 5;
                    $this->cargo['masteredLessons'][$curLessonKey] = $clone;
                    unset($this->cargo['currentLessons'][$curLessonKey]); // won't affect the clone
                    break;

                default:
                    assert(false, "Did not expect '$button' as a submit type");
            }
            $this->saveSession();
        } else {
            assert(false, 'POST data did not have required fields.');
        }
    }
}
