<?php


namespace App\ReadXYZ\Models;


use App\ReadXYZ\Data\OneTimePass;
use App\ReadXYZ\Rest\RestTarget;
use Exception;
use mysqli;
use App\ReadXYZ\Helpers\Util;
use stdClass;

class ProcessWordPressRequest extends RestTarget
{
    private mysqli $conn;

    public function __construct()
    {
        $this->conn = Util::dbConnect();
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
    private function isUserInPhonics($username): bool
    {
        return $this->sqlExists("SELECT * FROM abc_Users WHERE EMail = '$username'");
    }

    /**
     * returns a basicResponse object with result set to true if user exists in as a trainer in phonics101.
     *
     * @param string username The username to look for in abc_Users
     *
     * @return bool returns true if user is a trainer for a student
     *
     * @throws Exception if query command unexpectedly fails
     */
    private function isUserTeacher($username): bool
    {
        return $this->sqlExists("SELECT * FROM abc_Student WHERE trainer1 = '$username'");
    }

    /**
     * returns a basicResponse object wiht result set to true if user exists in as a
     * trainer in phonics101 for the given student name.
     *
     * @param string username The username to look for in abc_Student in trainer1 field
     * @param string studentname The student to look for in abc_Student
     *
     * @return bool returns true if the teacher/student combo is already in the database
     *
     * @throws Exception if query command unexpectedly fails
     */
    private function isTeacherStudentLogin($userName, $studentName): bool
    {
        $query = "SELECT * FROM abc_Student WHERE trainer1 = '$userName' AND StudentName = '$studentName'";

        return $this->sqlExists($query);
    }

    /**

     * @param string $username the username to be added as
     *
     * @throws Exception if SQL command to add student failed
     */
    private function addStudent(string $username, string $studentName): void
    {
        $time = time();
        $humanTime = date('Y-M-j H:i:s', $time);
        $studentId = uniqid('S');
        $enrollForm = [
            'StudentName' => $studentName,
            'ParentName' => '',
            'ParentEmail' => '',
            'ParentMobile' => '',
            'Emergency' => '',
            'TeacherName' => $username,
            'TeacherEmail' => $username,
            'TeacherMobile' => '',
            'transaction' => uniqid(),
        ];
        $cargo = [
            'studentID' => $studentId,
            'currentLesson' => '',
            'currentLessons' => [],
            'masteredLessons' => [],
            'blockedLessons' => [],
            'preferences' => [],
            'enrollForm' => $enrollForm,
            'StudentName' => $studentName,
            'trainer1' => $username,
            'trainer2' => '',
            'trainer3' => '',
            'project' => '',
            'created' => $time,
            'lastupdate' => $time,
        ];
        $studentCargo = serialize($cargo);
        $fields = '(studentid, cargo, StudentName, project, trainer1, trainer2, trainer3, created, createdhuman, lastupdate)';
        $values = "('$studentId', '$studentCargo', '$studentName', '', '$username', '', '', $time, '$humanTime', $time)";
        $query = "INSERT INTO abc_Student $fields VALUES $values";

        $result = $this->conn->query($query);
        if (!$result) {
            throw new Exception("Unexpected error adding student $studentName with trainer $username. {$this->conn->error}");
        }
    }

    /**
     * Adds the user to abc_Users.
     *
     * @param mysqli conn  The mysqli connection object
     * @param string username The username to add to abc_Users

     *
     * @throws Exception if SQL error encountered adding $username to abc_Users
     */
    private function addUser($username): void
    {
        $time = time();
        $humanTime = date('Y-M-j H:i:s', $time);
        $uuid = uniqid('U');
        $cargo = [
            'Project' => '',
            'UserName' => $username,
            'Name' => '',
            'PasswordHash' => '',
            'EMail' => $username,
            'fb_userid' => '',
            'Message' => '',
            'Mobile' => '',
            'Preferences' => [],
            'UserRole' => 'Volunteer',
            'Authorized' => '0',
            'AuthHashKey' => '',
            'Blocked' => '0',
            'PolicyAgreed' => 0,		// a time field
            'registerDate' => $time,
            'registerIP' => '',  // record the IP
            'lastVisit' => $time,
            'uuid' => $uuid,
        ];
        $userCargo = serialize($cargo);
        $fields = 'uuid, UserName, EMail, cargo, created, createdhuman,RoleId';
        $values = "'$uuid', '$username', '$username', '$userCargo', $time, '$humanTime', 4";
        $query = "INSERT INTO abc_Users ($fields) VALUES ($values)";

        $result = $this->conn->query($query);
        if (!$result) {
            throw new Exception("Unexpected error adding user $username. {$this->conn->error}");
        }
    }

    /**
     * looks for a field in $_REQUEST named login. Looks in the readxyz0_1 database to see it
     * the username exists.
     * a json structure is returned (via echo) containing the fields 'code', 'msg' and 'canlogin'.
     *
     * If the username is of the form email.com-studentname we will try and add user if they don't
     * exist in abc_Useers and we will try and add student if they don't already exist in abc_Student.
     * If everything gets added ok or already existed, 'canlogin' will be set to YES', 'code' will be
     * 200 and msg should be 'OK'.
     *
     * If the username is not of the form email.com-studentname, we will look to see if the
     * username exists in abc_Users and if so is the username a trainer to any students. If so,
     * 'canlogin' will be set to YES', 'code' will be 200 and msg should be 'OK'.
     *
     * Otherwise 'canlogin' will be set to 'NO', 'code' will most likely be 500 and 'msg' will contain
     * an error message.
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
        if ($this->conn->connect_errno) {
            return $this->getRestResponse(500, 'Connect failed. '.$this->conn->connect_error, false);
        } elseif (array_key_exists('login', $parameters)) {
            return $this->CheckLogin($parameters['login']);
        } else {
            return $this->getRestResponse(400, 'Unknown method.', false);
        }
    }
}
