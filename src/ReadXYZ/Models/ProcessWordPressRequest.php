<?php


namespace App\ReadXYZ\Models;


use App\ReadXYZ\Data\DbResult;
use App\ReadXYZ\Data\OneTimePass;
use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Data\TrainersData;
use App\ReadXYZ\Data\Views;
use App\ReadXYZ\Enum\TrainerType;
use App\ReadXYZ\Rest\RestTarget;
use Exception;
use stdClass;

class ProcessWordPressRequest extends RestTarget
{


    public function __construct()
    {

    }

    private function createUserObject(string $userLogin): stdClass
    {
        $pattern = "/([a-zA-Z0-9.+_-]+@[a-zA-Z0-9]+\.[a-zA-Z0-9]+)(\-*)([a-zA-Z]*)/";

        preg_match($pattern, $userLogin, $matches);
        $hasEmail = !empty($matches[0]);
        $hasStudent = !empty($matches[3]);
        $email = $hasEmail ? $matches[0] : '';
        $student = $hasStudent ? ucfirst($matches[3]) : '';
        $result = json_encode(['hasEmail' => $hasEmail, 'hasStudent' => $hasStudent, 'email' => $email, 'student' => $student]);

        return json_decode($result);
    }


    /**
     * returns a basicResponse object wiht result set to true if user exists in phonics101.
     *
     * @param string username The username to look for in abc_Users
     *
     * @return bool returns true is user is already in user database
     *
     * @throws Exception if query command unexpectedly fails
     */
    private function isUserInPhonics(string $user): bool
    {
        return (new TrainersData())->isValid($user);
    }

    /**
     * returns a basicResponse object with result set to true if user exists in as a trainer in phonics101.
     *
     * @param string user The username or trainerCode to look for in abc_Users
     *
     * @return bool returns true if user is a trainer for a student
     *
     * @throws Exception if query command unexpectedly fails
     */
    private function isUserTeacher($user): bool
    {
        return (new Views())->doesTrainerHaveStudents($user);
    }

    /**
     * returns a basicResponse object wiht result set to true if user exists in as a
     * trainer in phonics101 for the given student name.
     *
     * @param string user The username or trainerCode to look for in abc_Student in trainer1 field
     * @param string student The studentName or studentCode to look for in abc_Student
     *
     * @return bool returns true if the teacher/student combo is already in the database
     *
     * @throws Exception if query command unexpectedly fails
     */
    private function isTeacherStudentLogin(string $user, $student): bool
    {
        return (new Views())->isValidStudentTrainerPair($user, $student);
    }

    /**
     * @param string $userName the username to be added as
     *
     * @param string $studentName
     */
    private function addStudent(string $userName, string $studentName): void
    {
        $studentsData = new StudentsData();
        $studentsData->add($studentName, $userName);
    }

    /**
     * Adds the user to abc_Users.
     *
     * @param string $userName
     * @param string $password
     * @param string $first
     * @param string $last
     * @param string $type
     * @return DbResult
     */
    private function addUser(
        string $userName,
        string $password = 'read',
        string $first = '',
        string $last = '',
        string $type=TrainerType::TRAINER
    ): dbResult
    {
        //TODO: use Wordpress membership REST api to get firstName, lastName, password, trainerType
        return (new TrainersData())->add($userName, $password, $first, $last, $type);
    }

    /**
     * looks for a field in $_REQUEST named login. Looks in the readxyz0_1 database to see it
     * the username exists.
     * a json structure is returned (via echo) containing the fields 'code', 'msg' and 'canlogin'.
     *
     * If the username is of the form email.com-studentName we will try and add user if they don't
     * exist in abc_trainers and we will try and add student if they don't already exist in abc_Student.
     * If everything gets added ok or already existed, 'canlogin' will be set to YES', 'code' will be
     * 200 and msg should be 'OK'.
     *
     * If the username is not of the form email.com-studentName, we will look to see if the
     * username exists in abc_Users and if so is the username a trainer to any students. If so,
     * 'canlogin' will be set to YES', 'code' will be 200 and msg should be 'OK'.
     *
     * Otherwise 'canlogin' will be set to 'NO', 'code' will most likely be 500 and 'msg' will contain
     * an error message.
     * @param string $userLogin
     * @return string
     */
    private function CheckLogin(string $userLogin): string
    {
        $user = $this->createUserObject($userLogin);

        try {
            // handle the case where login includes a student name
            if ($user->hasStudent) {
                $alreadyExists = $this->isTeacherStudentLogin($userLogin, $user->student);
                // fall thru if already exists otherwise continue
                if (!$alreadyExists) {
                    // we should always have to create both the user and student for a user-student login
                    if (!$this->isUserInPhonics($userLogin)) {
                        $this->addUser($userLogin);
                    }
                    $this->addStudent($userLogin, $user->student);
                }
            } else { //the userLogin is non-hyphenated but might be in database
                if (not($this->isUserInPhonics($userLogin)) or not($this->isUserTeacher($userLogin))) {
                    return $this->getRestResponse(200, "No students assigned to $userLogin", false);
                }
            }
        } catch (Exception $ex) {
            return $this->getRestResponse(500, $ex->getMessage(), false);
        }
        $otpDispenser = new OneTimePass();
        $otp = $otpDispenser->getOTP($userLogin);
        return $this->getRestResponse(200, $otp, true);
    }

    public function handleRequestAndGetResponse(array $parameters): string
    {
        if (array_key_exists('login', $parameters)) {
            return $this->CheckLogin($parameters['login']);
        } else {
            return $this->getRestResponse(400, 'Unknown method.', false);
        }
    }
}
