<?php


namespace App\ReadXYZ\Models;


use App\ReadXYZ\Data\LessonsData;
use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Data\TrainersData;
use App\ReadXYZ\Enum\TrainerType;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;


/**
 * Class Session
 * @package App\ReadXYZ\Models
 * This is a wrapper for the program's session variables. Since there can exist a session for each student,
 * it is not implemented as a singleton. However, settings persist across instantiations because the
 * underlying session variable persists.
 *
 * We allow for multiple coexisting sessions. We don't kill session data for a student if the the student
 * changes. We just change the $_SESSION[self::CURR_CODE].
 */
class Session
{
    // $_SESSION[self::CURR_CODE] contains the current userCode or studentCode.
    // If it's a userCode it means we don't have a student selected yet.
    const CURR_CODE        = 'currCode';
    const TEST_USERCODE    = 'U123456789abcde.12345678';
    const TEST_STUDENTCODE = 'S123456789abcde.12345678';
    const VALIDITY_WINDOW  = 60 * 60 * 24; // one day in seconds

    // $_SESSION[$_SESSION[self::CURR_STUDENT] contains the session object
    private string  $userCode          = '';
    private string  $studentCode       = '';
    private string  $currentLessonCode = '';
    private string  $studentName       = '';
    private string  $currentLesson     = '';
    private int     $lastValidated     = 0;


    /**
     * Session constructor.
     * Normal flow - we make sure $_SESSION variables have been exposed with a start_session.
     * We check for the existence of $_SESSION['currCode'].
     */
    public function __construct()
    {
        $underTest = self::testingInProgress();
        // If unit testing we just build the session
        if ($underTest) {
            $this->userCode          = self::TEST_USERCODE;
            $this->studentCode       = self::TEST_STUDENTCODE;
            $this->studentName       = 'Test';
            $this->currentLessonCode = 'TEST';
            $this->currentLesson     = 'test';
            $this->lastValidated     = time();

            //When testing we don't use the $_SESSION variable
        } else {
            // If we have an active session we retrieve it, otherwise
            self::sessionContinue();
            if (isset($_SESSION['identity'])) unset($_SESSION['identity']);

            $sessId = $_SESSION[self::CURR_CODE] ?? '';

            if (empty($sessId)) {
                // we haven't set up $_SESSION['currCode'] yet so return empty handed
                return;
            }
            if (Util::startsWith('S', $sessId)) {
                // $_SESSION['currCode'] contains a student id so we retrieve all the information
                $this->retrieveSession($sessId);
            } else {
                // we assume $_SESSION['currCode'] is a userCode. We retrieve the id but session is still invalid
                $this->userCode = $sessId;
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
        $sessId = $_SESSION[self::CURR_CODE] ?? '';
        return Util::startsWith('S', $sessId);
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
        $sessId = $_SESSION[self::CURR_CODE] ?? '';
        return Util::startsWith('U', $sessId);
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
     * Once $_SESSION['currCode] gets set
     */
    public function clearSession()
    {
        if (isset($_SESSION[self::CURR_CODE])) {
            unset($_SESSION[self::CURR_CODE]);
        }
        $this->studentCode       = '';
        $this->userCode          = '';
        $this->studentName       = '';
        $this->currentLesson     = '';
        $this->currentLessonCode = '';
        $this->lastValidated     = 0;
    }

    /**
     * @return string current lesson code in use in session or empty string if none selected.
     */
    public function getCurrentLessonCode(): string
    {
        return $this->currentLessonCode;
    }

    /**
     * @return string current lesson name in use in session or empty string if none selected.
     */
    public function getCurrentLessonName(): string
    {
        return $this->currentLesson;
    }

    /**
     * @return int epoch seconds since last validated. Not currently used.
     */
    public function getLastValidated()
    {
        return $this->lastValidated;
    }

    /**
     * @return string studentName if student has been set in session otherwise empty string
     */
    public function getStudentName(): string
    {
        return $this->studentName;
    }

    public function getTrainerCode(): string
    {
        return $this->userCode;
    }

    /**
     * @return string studentCode of session
     */
    public function getStudentCode(): string
    {
        return $this->studentCode;
    }

    /**
     * @return bool returns true is session has a trainer, student and lesson identified.
     * @throws PhonicsException
     */
    public function hasLesson(): bool
    {
        return $this->hasStudent() && ! empty($this->currentLessonCode);
    }

    /**
     * Should be the equivalent of isValid because we don't set isValid under we have a student
     * @return bool
     * @throws PhonicsException
     */
    public function hasStudent(): bool
    {
        if (self::testingInProgress()) {return true;}
        if (empty($this->userCode) || empty($this->studentCode)) return false;

        if (Util::isValidStudentCode($this->studentCode) && $this->hasTrainer()) {
            return (new StudentsData())->isValidStudentTrainerPair($this->studentCode, $this->userCode);
        } else {
            return false;
        }
    }

    /**
     * @return bool true if session has at least a user, otherwise false.
     * @throws PhonicsException
     */
    public function hasTrainer(): bool
    {
        if (self::testingInProgress()) {return true;}
        if (empty($this->userCode)) return false;
        return Util::isValidTrainerCode($this->userCode);
    }

    /**
     * @return bool
     * @throws PhonicsException
     */
    public function isAdmin(): bool
    {
        if (self::testingInProgress()) {return true;}
        if ( ! $this->hasTrainer()) {return false;}

        $trainerData = new TrainersData();
        $trainerType = $trainerData->getTrainerType($this->userCode);
        return (TrainerType::ADMIN == $trainerType);
    }

    /**
     * @return bool
     * @throws PhonicsException
     */
    public function isStaff(): bool
    {
        if (self::testingInProgress()) {return true;}
        if ( ! $this->hasTrainer()) {return false;}

        $trainerData = new TrainersData();
        $trainerType = $trainerData->getTrainerType($this->userCode);
        return in_array($trainerType, ['admin', 'staff']);
    }

    /**
     * A session is considered active if we have identified the trainer and student for this session.
     * @return bool true if we have trainer/teacher, otherwise false.
     * @throws PhonicsException
     */
    public function isValid(): bool
    {
        return $this->hasStudent();
    }

    /**
     * @param string $lessonName
     * @throws PhonicsException
     */
    public function updateLesson(string $lessonName): void
    {
        if ( ! $this->isValid()) {
            throw new PhonicsException("There is no session active for a student. Cannot update lesson.");
        }
        $this->currentLesson     = $lessonName;
        $this->currentLessonCode = (new LessonsData())->getLessonCode($lessonName);
        if (self::testingInProgress()) {
            return;
        }
        $key                                   = $this->studentCode;
        $_SESSION[$key]['CURRENT_LESSON']      = $this->currentLesson;
        $_SESSION[$key]['CURRENT_LESSON_CODE'] = $this->currentLessonCode;
    }

    /**
     * If we have session variables associated with the specified student id we retrieve them.
     * @param string $studentCode
     * @throws PhonicsException
     */
    public function updateStudent(string $studentCode)
    {
        if (self::testingInProgress()) {
            return;
        }
        if ( ! isset($_SESSION[self::CURR_CODE])) {
            throw new PhonicsException("Cannot update student session when no session key is present.");
        }
        if ( ! Util::isValidStudentCode($studentCode)) {
            throw new PhonicsException("$studentCode is not a valid student code.");
        }

        $_SESSION[self::CURR_CODE] = $studentCode;
        // if this matches the current session id just retrieve it
        if (isset($_SESSION[$studentCode])) {
            $this->retrieveSession($studentCode);
        } else {
            // validate the student record
            $studentsData = new StudentsData();
            $studentName  = $studentsData->getStudentName($studentCode);
            if (empty($studentName)) {
                throw new PhonicsException("$studentCode not found in abc_students.");
            }
            // validate the student's trainer
            $teacherValidated = $studentsData->isValidStudentTrainerPair($studentCode, $this->userCode);
            if ( ! $teacherValidated) {
                $userName = (new TrainersData())->getUsername($this->userCode);
                throw new PhonicsException("$userName does not teach $studentName($studentCode).");
            }
            $this->studentCode       = $studentCode;
            $this->studentName       = $studentName;
            $this->currentLesson     = '';
            $this->currentLessonCode = '';
            $this->lastValidated     = time();
            $this->persistSession($studentCode);
        }
    }

    /**
     * Updates the Session with the given user. Accepts userCode or userName
     * @param string $user trainerCode or userName
     * @throws PhonicsException
     */
    public function updateUser(string $user)
    {
        if (self::testingInProgress()) {
            return;
        }
        $trainersData  = new TrainersData();
        $trainerCode   = Util::isValidTrainerCode($user) ? $user : $trainersData->getTrainerCode($user);
        if (empty($trainerCode)) {
            throw new PhonicsException("$user does not exist.");
        }

        // set userCode after clearing the other fields
        $this->clearSession();
        $_SESSION[self::CURR_CODE] = $trainerCode;
        $this->userCode            = $trainerCode;
    }

    /**
     * @param $trainer
     * @param $student
     * @throws PhonicsException
     */
    public function updateUserAndStudent($trainer, $student)
    {
        throw new PhonicsException("Not yet implemented.");
    }

// ======================== PRIVATE METHODS =====================
    /**
     * Persist this object into session variables.
     * @param string $studentCode the student the data belongs to.
     */
    private function persistSession(string $studentCode)
    {
        $_SESSION[self::CURR_CODE]                     = $studentCode;
        $_SESSION[$studentCode]['USER_CODE']           = $this->userCode;
        $_SESSION[$studentCode]['STUDENT_CODE']        = $this->studentCode;
        $_SESSION[$studentCode]['STUDENT_NAME']        = $this->studentName;
        $_SESSION[$studentCode]['CURRENT_LESSON']      = $this->currentLesson;
        $_SESSION[$studentCode]['CURRENT_LESSON_CODE'] = $this->currentLessonCode;
        $_SESSION[$studentCode]['VALIDATE']            = $this->lastValidated;
    }

    /**
     * Copies the persisted session information into our Session object.
     * @param string $studentCode the student the data belongs to.
     */
    private function retrieveSession(string $studentCode)
    {
        if ( ! isset($_SESSION[$studentCode])) {
            Log::info("Nonexistent session $studentCode. ");
        }
        // If we pass in a non-student id we will clear the object and invalidate it
        $this->studentCode       = $_SESSION[$studentCode]['STUDENT_CODE'] ?? '';
        $this->userCode          = $_SESSION[$studentCode]['USER_CODE'] ?? '';
        $this->studentName       = $_SESSION[$studentCode]['STUDENT_NAME'] ?? '';
        $this->currentLesson     = $_SESSION[$studentCode]['CURRENT_LESSON'] ?? '';
        $this->currentLessonCode = $_SESSION[$studentCode]['CURRENT_LESSON_CODE'] ?? '';
        $this->lastValidated     = $_SESSION[$studentCode]['VALIDATE'] ?? 0;
    }

    /**
     * @param string $userCode
     * @param string $studentCode
     * @return bool
     */
    private function updateValidation(string $userCode, string $studentCode): bool
    {
        // Eventually we will query the member manager API if the user and student are still valid.
        // For now, we just do nothing.
        $this->lastValidated = time();

        if ( ! self::testingInProgress()) {
            $_SESSION[$studentCode]['VALIDATE'] = $this->lastValidated;
        }
        return true;
    }

}
