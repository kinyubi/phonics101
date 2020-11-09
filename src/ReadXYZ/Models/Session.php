<?php


namespace App\ReadXYZ\Models;


use App\ReadXYZ\Data\StudentData;
use App\ReadXYZ\Data\UserData;
use App\ReadXYZ\Helpers\Util;
use LogicException;


/**
 * Class Session
 * @package App\ReadXYZ\Models
 * This is a wrapper for the program's session variables. Since there can exist a session for each student,
 * it is not implemented as a singleton. However, settings persist across instantiations because the
 * underlying session variable persists.
 *
 * We allow for multiple coexisting sessions. We don't kill session data for a student if the the student
 * changes. We just change the $_SESSION[self::CURR_ID].
 */
class Session
{
    // $_SESSION[self::CURR_USERID] contains the current userId or studentId.
    // If it's a userId it means we don't have a student selected yet.
    const CURR_ID = 'currId';
    const TEST_ID = 'S_TEST';

    const VALIDITY_WINDOW = 60 * 60 * 24; // one day in seconds

    // $_SESSION[$_SESSION[self::CURR_STUDENT] contains the session object
    private string $userId = '';
    private string $studentId = '';
    private string $studentName = '';
    private string $currentLesson = '';
    private int    $lastValidated = 0;
    private bool   $isValid = false;

    public function __construct()
    {
        $underTest = self::testingInProgress();
        // If unit testing we just build the session
        if ($underTest) {
            $this->userId = self::TEST_ID;
            $this->studentId = self::TEST_ID;
            $this->studentName = self::TEST_ID;
            $this->lastValidated = time();
            //When testing we don't use the $_SESSION variable
        } else {
            // If we have an active session we retrieve it, otherwise
            self::sessionContinue();
            $sessId = $_SESSION[self::CURR_ID] ?? '';
            if (!Util::startsWith($sessId, 'S')) {
                $this->userId = $sessId;
                $this->isValid = false;
            } else {
                $this->retrieveSession($sessId);
            }

        }

    }

    private function retrieveSession($studentId)
    {
        if (!isset($_SESSION[$studentId]) ) {
            Log::info("Nonexistent session $studentId. ");
        }
        // If we pass in a non-student id we will clear the object and invalidate it
        $this->studentId = $_SESSION[$studentId]['STUDENT_ID'] ?? '';
        $this->userId = $_SESSION[$studentId]['USER_ID'] ?? '';
        $this->studentName = $_SESSION[$studentId]['STUDENT_NAME'] ?? '';
        $this->currentLesson = $_SESSION[$studentId]['CURRENT_LESSON'] ?? '';
        $this->lastValidated = $_SESSION[$studentId]['VALIDATE'] ?? 0;
        $this->isValid = !empty($this->studentId);

    }
    private function persistSession($studentId)
    {
        if (!Util::startsWith($studentId, 'S')) {
            throw new LogicException("Invalid student id: $studentId. Must start with 'S'.",);
        }
        $_SESSION[self::CURR_ID] = $studentId;
        $_SESSION[$studentId]['USER_ID'] = $this->userId;
        $_SESSION[$studentId]['STUDENT_ID'] = $this->studentId;
        $_SESSION[$studentId]['STUDENT_NAME'] = $this->studentName;
        $_SESSION[$studentId]['CURRENT_LESSON'] = $this->currentLesson;
        $_SESSION[$studentId]['VALIDATE'] = $this->lastValidated;
    }

    public function updateUser(string $userId) {
        if (self::testingInProgress()) return;
        $validUserId = Util::startsWith($userId, 'U') && (strlen($userId) == 14);
        if (!$validUserId) {
            throw new LogicException("$userId format is illegal.");
        }
        $_SESSION[self::CURR_ID] = $userId;
        $this->retrieveSession($$userId); // cheesy way to clear the object and invalidate it.
        // set userId after clearing the other fields
        $this->userId = $userId;
    }


    public function updateStudent(string $studentId)
    {
        if (self::testingInProgress()) return;
        $_SESSION[self::CURR_ID] = $studentId;
        // if this matches the current session id just retrieve it
        if (isset($_SESSION[$studentId])) {
            $this->retrieveSession($studentId);
        } else {
            // validate the student record
            $studentData = new StudentData();
            $studentName = $studentData->getStudentName($studentId);
            if (empty($studentName)) {
                throw new LogicException("$studentId is not a valid student Id.");
            }
            // validate the student's trainer
            $teacherValidated = $studentData->studentHasTeacher($studentId, $this->userId);
            if (!$teacherValidated) {
                throw new LogicException("{$this->userId} does not teach $studentName($studentId).");
            }
            $this->studentId = $studentId;
            $this->studentName = $studentName;
            $this->currentLesson = '';
            $this->lastValidated = time();
            $this->isValid = true;
            $this->persistSession($studentId);
        }
    }

    public function updateLesson(string $lessonName): void
    {
        if (!$this->isValid()) {
            throw new LogicException("There is no session active for a student. Cannot update lesson.");
        }
        $this->currentLesson = $lessonName;
        if (self::testingInProgress()) return;

        $_SESSION[$this->studentId]['CURRENT_LESSON'] = $this->currentLesson;

    }


    public function updateValidation(string $userId, string $studentId): bool
    {
        // Eventually we will query the member manager API if the user and student are still valid.
        // For now, we just do nothing.
        $this->lastValidated = time();
        $this->isValid = true;

        if (!self::testingInProgress()) {
            $_SESSION[$studentId]['VALIDATE'] = $this->lastValidated;
        }
        return true;
    }

    public function getCurrentLesson(): string
    {
        return $this->currentLesson;
    }


    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getStudentId(): string
    {
        return $this->studentId;
    }

    public function getStudentName(): string
    {
        return $this->studentName;
    }

    public function getLastValidated()
    {
        return $this->lastValidated;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function clearSession()
    {
        if (isset($_SESSION[self::CURR_ID])) {
            unset($_SESSION[self::CURR_ID]);
        }
        $this->studentId = '';
        $this->userId =  '';
        $this->studentName =  '';
        $this->currentLesson = '';
        $this->lastValidated =  0;
        $this->isValid = false;
    }


    /**
     * We never destroy the session so we are just continuing the existing one. Every page that
     * gets rendered needs to have a session_start. Without it, $_SESSION variables aren't visible.
     */
    public static function sessionContinue(): void
    {

        if (self::testingInProgress()) return;
        if (!isset($_SESSION)) { //You can't start a session that's already going
            session_start(); // continues the existing session
        }
    }


    /**
     * Allows unit tests to not get messed up because there are no session variables
     * @return bool true if Testing define is set, otherwise false
     */
    public static function testingInProgress(): bool
    {
        return defined('TESTING_IN_PROGRESS');
    }

    public static function hasSession(): bool
    {
        if (self::testingInProgress()) return true;
        $sessId = $_SESSION[self::CURR_ID];
        if (!empty($sessId)) {
            Util::startsWith($sessId, 'S');
            return isset($_SESSION[$sessId]['STUDENT_ID']);
        }
        return false;
    }

    public static function hasOnlyUser(): bool
    {
        if (self::testingInProgress()) return false;
        $sessId = $_SESSION[self::CURR_ID] ?? '';
        return Util::startsWith($sessId, 'U');
    }

}
