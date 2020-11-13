<?php


namespace App\ReadXYZ\Models;


use App\ReadXYZ\Data\LessonsData;
use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Data\TrainersData;
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
    const TEST_ID = 9999999999;

    const VALIDITY_WINDOW = 60 * 60 * 24; // one day in seconds

    // $_SESSION[$_SESSION[self::CURR_STUDENT] contains the session object
    private int     $userId = 0;
    private int     $studentId = 0;
    private string  $currentLessonCode = '';
    private string  $studentName = '';
    private string  $currentLesson = '';
    private int     $lastValidated = 0;
    private bool    $isValid = false;

    /**
     * Session constructor.
     * Normal flow - we make sure $_SESSION variables have been exposed with a start_session.
     * We check for the existence of $_SESSION['currId'].
     */
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

            if (empty($sessId)) {
                // we haven't set up $_SESSION['currId'] yet so return empty handed
                return;
            }
            if (Util::startsWith($sessId, 'S')) {
                // $_SESSION['currId'] contains a student id so we retrieve all the information
                $this->retrieveSession($sessId);
            } else {
                // we assume $_SESSION['currId'] is a userId. We retrieve the id but session is still invalid
                $this->userId = $sessId;
                $this->isValid = false;
            }
        }
    }

// ======================== STATIC METHODS =====================
    public static function hasActiveStudent(): bool
    {
        if (self::testingInProgress()) {
            return true;
        }
        self::sessionContinue();
        $sessId = $_SESSION[self::CURR_ID] ?? '';
        return Util::startsWith($sessId, 'S');
    }

    public static function hasNoSession(): bool
    {
        return not(self::hasOnlyUser() || self::hasActiveStudent());
    }

    public static function hasOnlyUser(): bool
    {
        if (self::testingInProgress()) {
            return false;
        }
        self::sessionContinue();
        $sessId = $_SESSION[self::CURR_ID] ?? '';
        return Util::startsWith($sessId, 'U');
    }

    /**
     * We never destroy the session so we are just continuing the existing one. Every page that
     * gets rendered needs to have a session_start. Without it, $_SESSION variables aren't visible.
     */
    public static function sessionContinue(): void
    {
        if (self::testingInProgress()) {
            return;
        }
        if ( ! isset($_SESSION)) { //You can't start a session that's already going
            session_start();       // continues the existing session
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

// ======================== PUBLIC METHODS =====================

    /**
     * This kills the session. It does not kill the session settings associated with a student.
     * Once $_SESSION['currId] gets set
     */
    public function clearSession()
    {
        if (isset($_SESSION[self::CURR_ID])) {
            unset($_SESSION[self::CURR_ID]);
        }
        $this->studentId = '';
        $this->userId = '';
        $this->studentName = '';
        $this->currentLesson = '';
        $this->currentLessonCode = '';
        $this->lastValidated = 0;
        $this->isValid = false;
    }

    /**
     * @return string current lesson name in use in session or empty string if none selected.
     */
    public function getCurrentLessonName(): string
    {
        return $this->currentLesson;
    }

    /**
     * @return string current lesson code in use in session or empty string if none selected.
     */
    public function getCurrentLessonCode(): string
    {
        return $this->currentLessonCode;
    }

    /**
     * @return int epoch seconds since last validated. Not currently used.
     */
    public function getLastValidated()
    {
        return $this->lastValidated;
    }

    /**
     * @return string studentId of session
     */
    public function getStudentId(): string
    {
        return $this->studentId;
    }

    /**
     * @return string studentName if student has been set in session otherwise empty string
     */
    public function getStudentName(): string
    {
        return $this->studentName;
    }

    public function getTrainerId(): string
    {
        return $this->userId;
    }

    /**
     * @return bool returns true is session has a trainer, student and lesson identified.
     */
    public function hasLesson(): bool
    {
        return $this->hasStudent() && ! empty($this->currentLessonCode);
    }

    /**
     * Should be the equivalent of isValid because we don't set isValid under we have a student
     * @return bool
     */
    public function hasStudent(): bool
    {
        // TODO refactor to merge with isValid()
        return $this->isValid() && ($this->studentId > 0);
    }

    /**
     * A session is considered active if we have identified the trainer and student for this session.
     * @return bool true if we have trainer/teacher, otherwise false.
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function updateLesson(string $lessonName): void
    {
        if ( ! $this->isValid()) {
            throw new LogicException("There is no session active for a student. Cannot update lesson.");
        }
        $this->currentLesson = $lessonName;
        $this->currentLessonCode = (new LessonsData())->getLessonCode($lessonName);
        if (self::testingInProgress()) {
            return;
        }
        $key = 'S' . strval($this->studentId);
        $_SESSION[$key]['CURRENT_LESSON'] = $this->currentLesson;
        $_SESSION[$key]['CURRENT_LESSON_CODE'] = $this->currentLessonCode;
    }

    /**
     * If we have session variables associated with the specified student id we retrieve them.
     * @param int $studentId
     */
    public function updateStudent(int $studentId)
    {
        if (self::testingInProgress()) {
            return;
        }
        if ( ! isset($_SESSION[self::CURR_ID])) {
            throw new LogicException("Cannot update student session when no session key is present.");
        }
        $key = "S$studentId";
        $_SESSION[self::CURR_ID] = $key;
        // if this matches the current session id just retrieve it
        if (isset($_SESSION[$key])) {
            $this->retrieveSession($studentId);
        } else {
            // validate the student record
            $studentsData = new StudentsData();
            $studentName = $studentsData->getStudentName($studentId);
            if (empty($studentName)) {
                throw new LogicException("$studentId is not a valid student Id.");
            }
            // validate the student's trainer
            $teacherValidated = $studentsData->isValidStudentTrainerPair($studentId, $this->userId);
            if ( ! $teacherValidated) {
                $userName = (new TrainersData())->getUsername($this->userId);
                throw new LogicException("$userName does not teach $studentName($studentId).");
            }
            $this->studentId = $studentId;
            $this->studentName = $studentName;
            $this->currentLesson = '';
            $this->currentLessonCode = '';
            $this->lastValidated = time();
            $this->isValid = true;
            $this->persistSession($studentId);
        }
    }

    /**
     * Updates the Session with the given user id. If
     * @param int $userId
     */
    public function updateUser(int $userId)
    {
        if (self::testingInProgress()) {
            return;
        }
        // a valid userId start with a U and is 14 characters long.
        $validUserId = Util::startsWith($userId, 'U');
        if ( ! $validUserId) {
            throw new LogicException("$userId format is illegal.");
        }

        // set userId after clearing the other fields
        $this->clearSession();
        $_SESSION[self::CURR_ID] = "U$userId";
        $this->userId = $userId;
    }

    public function updateValidation(string $userId, string $studentId): bool
    {
        // Eventually we will query the member manager API if the user and student are still valid.
        // For now, we just do nothing.
        $this->lastValidated = time();
        $this->isValid = true;

        if ( ! self::testingInProgress()) {
            $_SESSION[$studentId]['VALIDATE'] = $this->lastValidated;
        }
        return true;
    }

// ======================== PRIVATE METHODS =====================

    /**
     * Persist this object into session variables.
     * @param int $studentId the student the data belongs to.
     */
    private function persistSession(int $studentId)
    {
        $key = "S$studentId";
        $_SESSION[self::CURR_ID] = $key;
        $_SESSION[$key]['USER_ID'] = $this->userId;
        $_SESSION[$key]['STUDENT_ID'] = $this->studentId;
        $_SESSION[$key]['STUDENT_NAME'] = $this->studentName;
        $_SESSION[$key]['CURRENT_LESSON'] = $this->currentLesson;
        $_SESSION[$key]['CURRENT_LESSON_CODE'] = $this->currentLessonCode;
        $_SESSION[$key]['VALIDATE'] = $this->lastValidated;
    }

    /**
     * Copies the persisted session information into our Session object.
     * @param int $studentId the student the data belongs to.
     */
    private function retrieveSession(int $studentId)
    {
        $key = "S$studentId";
        if ( ! isset($_SESSION[$key])) {
            Log::info("Nonexistent session $studentId. ");
        }
        // If we pass in a non-student id we will clear the object and invalidate it
        $this->studentId = $_SESSION[$key]['STUDENT_ID'] ?? '';
        $this->userId = $_SESSION[$key]['USER_ID'] ?? '';
        $this->studentName = $_SESSION[$key]['STUDENT_NAME'] ?? '';
        $this->currentLesson = $_SESSION[$key]['CURRENT_LESSON'] ?? '';
        $this->currentLessonCode = $_SESSION[$key]['CURRENT_LESSON_CODE'] ?? '';
        $this->lastValidated = $_SESSION[$key]['VALIDATE'] ?? 0;
        $this->isValid = (new StudentsData())->doesStudentExist($studentId);
    }

}
