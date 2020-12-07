<?php

namespace App\ReadXYZ\Models;

//  a student is represented by a record in StudentsData
//  the student and his training records are in a single record

use App\ReadXYZ\Data\StudentLessonsData;
use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Helpers\PhonicsException;

class OldStudent
{

    private static OldStudent $instance;

    private Session $session;
    private bool    $isValidStudent;

    private function __construct()
    {
        $this->session = new Session();
        $this->isValidStudent = SESSION::hasActiveStudent();
    }


    public function getStudentName()
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
            throw new PhonicsException("Cannot save lesson selection without a student session.");
        }
        $this->session->updateLesson($lessonName);
    }


    /**
     * the target method for the fluencyTimerForm when fluencySaveButton is pressed.
     */
    public function fluencyTimer()
    { // this is the timer function for Repeated Reading
        $seconds = intval($_POST['seconds']);
        (new StudentLessonsData())->updateTimedTest('fluency', $seconds);
    }

    /**
     * This function is the OnSubmit callback for the Advancing and Mastered buttons on the Test tab
     * To get get here one of those buttons has been pressed.
     *
     * @param array $postData the form fields in an array
     */
    public function testTimer(array $postData)
    {
        if (isset($postData['seconds'])) {
            $seconds = intval($postData['seconds'] ?? '0');
            (new StudentLessonsData())->updateTimedTest('test', $seconds);
        } elseif (isset($postData['masteryType'])) {
            $masteryType = $postData['masteryType'] ?? ($postData['P1'] ?? '');
            (new StudentLessonsData())->updateMastery($masteryType);
        } else {
            throw new PhonicsException( 'POST data did not have required fields.');
        }
    }

}
