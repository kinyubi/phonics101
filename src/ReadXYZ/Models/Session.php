<?php


namespace App\ReadXYZ\Models;

use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Data\TrainersData;
use App\ReadXYZ\Data\Views;
use App\ReadXYZ\Enum\TrainerType;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\JSON\LessonsJson;


/**
 * Class Session
 * @package App\ReadXYZ\Models
 * This is a wrapper for the program's session variables. Since there can exist a session for each student,
 * it is not implemented as a singleton. However, settings persist across instantiations because the
 * underlying session variable persists.
 *
 * We allow for multiple coexisting sessions. We don't kill session data for a student if the the student
 * changes.
 * $_SESSION['currStudent']
 * $_SESSION['currUser']
 * $_SESSION['STUDENTS'][$studentCode] object(currentLessonCode, studentName, lastValidated, currentLessonName?)
 * $_SESSION['USERS'][$trainerCode] object(userName, trainerCode, studentCt, studentmap, lastValidated)
 */
class Session
{
    const CURRENT_STUDENT  = 'currStudent';
    const CURRENT_USER     = 'currUser';
    const STUDENTS         = 'STUDENTS';
    const USERS            = 'USERS';
    const TEST_TRAINERCODE = 'U123456789abcde0Z12345678';
    const TEST_STUDENTCODE = 'S123456789abcde0Z12345678';
    const TEST_NAME        = 'Test';

    const VALIDITY_WINDOW = 60 * 60 * 24; // one day in seconds


// ======================== STATIC METHODS =====================
    /**
     * This kills the session. It does not kill the session settings associated with a student.
     * Once $_SESSION['currCode] gets set
     */
    public static function clearSession()
    {
        self::sessionContinue();
        self::cleanupSessionVariables();
        $_SESSION[self::CURRENT_STUDENT] = '';
        $_SESSION[self::CURRENT_USER]    = '';
    }

    /**
     * @return string current lesson code in use in session or empty string if none selected.
     * @throws PhonicsException
     */
    public static function getCurrentLessonCode(): string
    {
        if (self::testingInProgress()) {
            return 'TEST';
        }

        $student = self::get(self::STUDENTS);
        if (empty($student->studentCode)) {
            return '';
        }
        $lessonsJson = LessonsJson::getInstance();
        if (empty($student->lessonCode)) {
            if (empty($student->lessonName)) {
                return '';
            } else {
                $student->lessonCode = $lessonsJson->getLessonCode($student->lessonName);
                if ($student->lessonCode) {
                    self::set($student, self::STUDENTS, $student->studentCode);
                }
                return $student->lessonCode;
            }
        } else {
            // we have a lesson code
            if (empty($student->lessonName)) {
                $student->lessonName = $lessonsJson->getLessonName($student->lessonCode);
                if ($student->lessonName) {
                    self::set($student, self::STUDENTS, $student->studentCode);
                }
            }
            return $student->lessonCode;
        }
    }

    /**
     * @return string current lesson name in use in session or empty string if none selected.
     * @throws PhonicsException
     */
    public static function getCurrentLessonName(): string
    {
        if (self::testingInProgress()) {
            return self::TEST_NAME;
        }

        $student = self::get(self::STUDENTS);
        if (empty($student->studentCode)) {
            return '';
        }

        if (empty($student->lessonName)) {
            if (empty($student->lessonCode)) {
                return '';
            } else {
                $student->lessonName = LessonsJson::getInstance()->getLessonName($student->lessonCode);
                if ($student->lessonName) {
                    self::set($student, self::STUDENTS, $student->studentCode);
                }
                return $student->lessonName;
            }
        } else {
            // we have a lesson name
            if (empty($student->lessonCode)) {
                // if we have a lesson name but no code let's get the lesson code and update the session variable
                $student->lessonCode = LessonsJson::getInstance()->getLessonCode($student->lessonName);
                if ($student->lessonCode) {
                    self::set($student, self::STUDENTS, $student->studentCode);
                }
            }
            return $student->lessonName;
        }
    }

    /**
     * @return string studentCode of session
     * @throws PhonicsException
     */
    public static function getStudentCode(): string
    {
        if (self::testingInProgress()) {
            return self::TEST_STUDENTCODE;
        }
        return self::get(self::CURRENT_STUDENT);
    }

    /**
     * @return int epoch seconds since last validated. Not currently used.
     * @throws PhonicsException
     */
    public static function getStudentLastValidated()
    {
        return self::get(self::STUDENTS)->lastValidated;
    }

    /**
     * @return string studentName if student has been set in session otherwise empty string
     * @throws PhonicsException
     */
    public static function getStudentName(): string
    {
        if (self::testingInProgress()) {
            return self::TEST_NAME;
        }

        return  self::get(self::STUDENTS)->studentName;
    }

    /**
     * @return string
     * @throws PhonicsException
     */
    public static function getTrainerCode(): string
    {
        if (self::testingInProgress()) {
            return self::TEST_TRAINERCODE;
        }

        return self::get(self::CURRENT_USER);
    }

    /**
     * @return int epoch seconds since last validated. Not currently used.
     * @throws PhonicsException
     */
    public static function getUserLastValidated()
    {
        return self::get(self::USERS)->lastValidated;
    }

    /**
     * @return string
     * @throws PhonicsException
     */
    public static function getUserName(): string
    {
        if (self::testingInProgress()) {
            return self::TEST_NAME;
        }
        return self::get(self::USERS)->userName;
    }

    /**
     *  an object with the fields (studentCode, studentName, lessonCode, lessonName, lastValidated)
     * @return object|null
     * @throws PhonicsException
     */
    public static function getStudentObject(): ?object
    {
        return (self::hasStudent()) ? self::get(self::STUDENTS) : null;
    }

    /**
     * an object with the fields (trainerCode, userName, trainerType, studentCt, studentMap, lastValidated)
     * @return object|null
     * @throws PhonicsException
     */
    public static function getUserObject(): ?object
    {
        if (! self::hasTrainer()) return null;
        $user = self::get(self::USERS);
        if (! isset($users->trainerType)) {
            $trainerType = (new TrainersData())->getTrainerType($user->trainerCode);
            $user->trainerType = $trainerType;
            self::set($user, self::USERS);
        }
        return $user;
    }

    /**
     * @return bool
     * @throws PhonicsException
     */
    public static function hasActiveStudent(): bool
    {
        if (self::testingInProgress()) {
            return true;
        }

        return ! empty(self::get(self::CURRENT_STUDENT));
    }

    /**
     * @return bool returns true is session has a trainer, student and lesson identified.
     * @throws PhonicsException on ill-formed SQL
     */
    public static function hasLesson(): bool
    {
        if (self::testingInProgress()) {
            return true;
        }
        return ! empty(self::get(self::STUDENTS)->lessonCode);
    }

    /**
     * Should be the equivalent of isValid because we don't set isValid under we have a student
     * @return bool
     * @throws PhonicsException on ill-formed SQL
     */
    public static function hasStudent(): bool
    {
        if (self::testingInProgress()) {
            return true;
        }
        self::sessionContinue();
        $hasStudent = ! empty(self::get(self::CURRENT_STUDENT));
        if ($hasStudent) {
            $studentCode = self::getStudentCode();
            $trainerCode = self::getTrainerCode();
            if (empty($trainerCode) || empty($studentCode)) {
                return false;
            }
            return Views::getInstance()->isValidStudentTrainerPair($trainerCode, $studentCode,);
        } else {
            return false;
        }
    }

    /**
     * @return bool true if session has at least a user, otherwise false.
     * @throws PhonicsException on ill-formed SQL
     */
    public static function hasTrainer(): bool
    {
        if (self::testingInProgress()) {
            return true;
        }
        $trainerCode = self::get(self::CURRENT_USER);
        return (new TrainersData())->isValid($trainerCode);
    }

    /**
     * @return bool
     * @throws PhonicsException on ill-formed SQL
     */
    public static function isAdmin(): bool
    {
        if (self::testingInProgress()) {
            return true;
        }

        return self::get(self::USERS)->type == TrainerType::ADMIN;

    }

    /**
     * @return bool
     * @throws PhonicsException on ill-formed SQL
     */
    public static function isStaff(): bool
    {
        if (self::testingInProgress()) {
            return true;
        }

        $trainerType = self::get(self::USERS)->type;
        return in_array($trainerType, [TrainerType::ADMIN, TrainerType::STAFF]);
    }

    /**
     * A session is considered active if we have identified the trainer and student for this session.
     * @return bool true if we have trainer/teacher, otherwise false.
     * @throws PhonicsException on ill-formed SQL
     */
    public static function isValid(): bool
    {
        return self::hasStudent();
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

        if ( ! isset($_SESSION)) {
            session_start();
        }
        if ( ! isset($_SESSION[self::CURRENT_USER])) {
            $_SESSION[self::CURRENT_USER] = '';
        }
        if ( ! isset($_SESSION[self::CURRENT_STUDENT])) {
            $_SESSION[self::CURRENT_STUDENT] = '';
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

    /**
     * @param string $lessonName
     * @throws PhonicsException on ill-formed SQL
     */
    public static function updateLesson(string $lessonName): void
    {
        if (self::testingInProgress()) {
            return;
        }

        if ( ! self::isValid()) {
            throw new PhonicsException("There is no session active for a student. Cannot update lesson.");
        }
        $lessons             = LessonsJson::getInstance();
        $student             = self::get(self::STUDENTS);
        $student->lessonName = $lessons->getLessonName($lessonName);
        $student->lessonCode = $lessons->getLessonCode($lessonName);

        if ($student->studentCode && $student->lessonCode && $student->lessonName) {
            self::set($student, self::STUDENTS);
        } else {
            $message = "Cannot update session lesson. Student: {$student->studentCode}, lesson: {$student->lessonCode}";
            throw new PhonicsException($message);
        }
    }

    /**
     * If we have session variables associated with the specified student id we retrieve them.
     * @param string $studentCode
     * @throws PhonicsException on ill-formed SQL
     */
    public static function updateStudent(string $studentCode)
    {
        if (self::testingInProgress()) {
            return;
        }
        if ( ! self::hasTrainer()) {
            throw new PhonicsException("Cannot update student session without a user.");
        }
        if ( ! (new StudentsData())->doesStudentExist($studentCode)) {
            throw new PhonicsException("$studentCode is not a valid student code.");
        }

        self::set($studentCode, self::CURRENT_STUDENT);
        // if this matches the current session id just retrieve it
        $student = self::getEmptyStudentObject();

        $studentsData = new StudentsData();
        $studentName  = $studentsData->getStudentName($studentCode);
        if (empty($studentName)) {
            throw new PhonicsException("$studentCode not found in abc_students.");
        }
        $student->studentCode = $studentCode;
        $student->studentName = $studentName;
        $trainerCode          = self::get(self::CURRENT_USER);
        // validate the student's trainer
        $teacherValidated = Views::getInstance()->isValidStudentTrainerPair($trainerCode, $studentCode);
        if ( ! $teacherValidated) {
            $userName = (new TrainersData())->getUsername($trainerCode);
            throw new PhonicsException("$userName does not teach $studentName($studentCode).");
        }

        $student->lessonName    = '';
        $student->lessonCode    = '';
        $student->lastValidated = time();
        self::set($student, self::STUDENTS);
    }

    /**
     * Updates the Session with the given user. Accepts trainerCode or userName as first parameter.
     * If this call is based on S2Member information, we don't know the trainerCode
     * @param string $userName usually an email address
     * @param string $trainerCode
     * @param string $userDisplayAs trainers full name
     * @param string $trainerType
     * @param array $studentMap [ ['studentCode' => code, 'studentName' => name]  ]
     * @throws PhonicsException on ill-formed SQL
     */
    public static function updateUser(
        string $userName,
        string $trainerCode = '',
        string $userDisplayAs = '',
        string $trainerType = '',
        array $studentMap = [])
    {
        if (self::testingInProgress()) {
            return;
        }
        self::sessionContinue();
        $trainersData = new TrainersData();
        $userObject   = null;
        if (empty($trainerCode) || empty($userDisplayAs) || empty($studentMap || empty($trainerType))) {
            // we only have partial data so get information from the database

            $trainer = $trainersData->get($userName);
            if ($trainer == null) {
                throw new PhonicsException("$userName is not a valid user name.");
            } else {
                $studentMap = Views::getInstance()->getMapOfStudentsForUser();
                $count      = count($studentMap);
                $userObject = self::createUserObject($trainer->trainerCode, $trainer->userName, $trainer->trainerType, $count, $studentMap);
                $trainerCode = $trainer->trainerCode;
            }
        } else {
            $userObject = self::createUserObject($trainerCode, $userName, $trainerType, count($studentMap), $studentMap);
        }
        self::clearSession();
        self::set($trainerCode, self::CURRENT_USER);
        self::set($userObject, self::USERS);
    }

// ======================== PUBLIC METHODS =====================
// ======================== PRIVATE METHODS =====================

    /**
     *  $_SESSION['currStudent']
     * $_SESSION['currUser']
     * $_SESSION['STUDENTS'][$studentCode] object(currentLessonCode, studentName, lastValidated, currentLessonName?)
     * $_SESSION['USERS'][$trainerCode] object(userName, students['code' => $code, 'name' => $name], lastValidated)
     */
    private static function cleanupSessionVariables(): void
    {
        self::sessionContinue();
        $allowed = ['currStudent', 'currUser', 'STUDENTS', 'USERS'];
        $keys    = array_keys($_SESSION);
        foreach ($keys as $key) {
            if ( ! in_array($key, $allowed)) {
                unset($_SESSION[$key]);
            }
        }
    }

    /**
     * creates an object with the fields (studentCode, studentName, lessonCode, lessonName, lastValidated)
     * @param string $studentCode
     * @param string $studentName
     * @param string $lessonCode
     * @param string $lessonName
     * @param int $lastValidated
     * @return object
     */
    private static function createStudentObject(
        string $studentCode,
        string $studentName,
        string $lessonCode,
        string $lessonName,
        int $lastValidated
    ): object
    {
        $seconds = ($lastValidated == 0) ? time() : $lastValidated;
        return (object)[
            'studentCode'   => $studentCode,
            'studentName'   => $studentName,
            'lessonCode'    => $lessonCode,
            'lessonName'    => $lessonName,
            'lastValidated' => $seconds
        ];
    }

    /**
     * creates an object with the fields (trainerCode, userName, studentCt, studentMap, lastValidated)
     * @param string $trainerCode
     * @param string $userName
     * @param int $studentCt
     * @param array $studentMap
     * @param int $lastValidated
     * @return object
     */
    private static function createUserObject(
        string $trainerCode,
        string $userName,
        string $trainerType,
        int $studentCt,
        array $studentMap,
        int $lastValidated = 0
    ): ?object
    {
        $seconds = ($lastValidated == 0) ? time() : $lastValidated;
        return (object)[
            'trainerCode'   => $trainerCode,
            'userName'      => $userName,
            'studentCt'     => $studentCt,
            'trainerType'   => $trainerType,
            'studentMap'    => $studentMap,
            'lastValidated' => $seconds
        ];
    }

    /**
     * return a session variable. If STUDENTS or USERS is specified, the current student/user value is used
     * if subKey is empty.
     * @param string $key
     * @param string $subKey
     * @return string|?object
     * @throws PhonicsException
     */
    private static function get(string $key, string $subKey = '')
    {
        self::sessionContinue();
        if (in_array($key, [self::CURRENT_STUDENT, self::CURRENT_USER])) {
            return $_SESSION[$key] ?? '';
        } elseif (in_array($key, [self::STUDENTS, self::USERS])) {
            if (empty($subKey) && ($key == self::STUDENTS)) {
                if (empty($_SESSION[self::CURRENT_STUDENT])) {
                    return self::getEmptyStudentObject();
                } else {
                    return $_SESSION[self::STUDENTS][$_SESSION[self::CURRENT_STUDENT]] ?? null;
                }
            } elseif (empty($subKey) && ($key == self::USERS)) {
                if (empty($_SESSION[self::CURRENT_USER])) {
                    return self::getEmptyUserObject();
                } else {
                    return $_SESSION[self::USERS][$_SESSION[self::CURRENT_USER]] ?? null;
                }
            } else {
                if ($key == self::STUDENTS) {
                    return $_SESSION[$key][$subKey] ?? self::getEmptyStudentObject();
                } else {
                    return $_SESSION[$key][$subKey] ?? self::getEmptyUserObject();
                }
            }
        } else {
            throw new PhonicsException("Invalid session key $key.");
        }
    }

    /**
     * @return object an empty
     */
    private static function getEmptyStudentObject(): object
    {
        return self::createStudentObject('', '', '', '', 1);
    }

    /**
     * @return object an empty object with the fields (trainerCode, userName, studentCt, studentMap, lastValidated)
     */
    private static function getEmptyUserObject(): object
    {
        return self::createUserObject('', '', '', 0, []);
    }

    /**
     * set a session variable. see explanation in get for key and subKey.
     * @param mixed $value
     * @param string $key
     * @param string $subKey
     * @throws PhonicsException
     */
    private static function set($value, string $key, string $subKey = ''): void
    {
        self::sessionContinue();
        if (in_array($key, [self::CURRENT_STUDENT, self::CURRENT_USER])) {
            $_SESSION[$key] = $value;
        } elseif (in_array($key, [self::STUDENTS, self::USERS])) {
            if (empty($subKey) && ($key == self::STUDENTS)) {
                if (empty($_SESSION[self::CURRENT_STUDENT])) {
                    throw new PhonicsException("SubKey must be present if no current student.");
                }
                $_SESSION[self::STUDENTS][$_SESSION[self::CURRENT_STUDENT]] = $value;
            } elseif (empty($subKey) && ($key == self::USERS)) {
                if (empty($_SESSION[self::CURRENT_USER])) {
                    throw new PhonicsException("SubKey must be present if no current user.");
                }
                $_SESSION[self::USERS][$_SESSION[self::CURRENT_USER]] = $value;
            } else {
                $_SESSION[$key][$subKey] = $value;
            }
        } else {
            throw new PhonicsException("Invalid session key $key.");
        }
    }

    /**
     * @param string $trainerCode
     * @param string $studentCode
     * @return bool
     */
    private function updateValidation(string $trainerCode, string $studentCode): bool
    {
        // Eventually we will query the member manager API if the user and student are still valid.
        // For now, we just do nothing.
        self::sessionContinue();

        $this->lastValidated = time();

        if ( ! self::testingInProgress()) {
            $_SESSION[$studentCode]['VALIDATE'] = $this->lastValidated;
        }
        return true;
    }

}
