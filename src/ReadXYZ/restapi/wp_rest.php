<?php

/**
 * wp_rest.php is a REST API for phonics101 plugin.
 *
 * @author Carl Baker <carl.baker@readxyz.org>
 * @copyright (c) 2020 ReadXYZ, LLC
 */

include dirname(__DIR__) . '/autoload.php';
use ReadXYZ\Helpers\Util;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

/* HTTP Codes
200 - OK
400 - Bad request
401 - Unauthorized (authentication required but not provided)
403 - Forbidden (valid data but unable to process. Do not repeat request)
404 - Not found (resource not found)
500 - Internal Server Error
501 - Not implemented (Server doesn't recognize method or can't fulfill request)
505 - HTTP Version not supported
*/

/*
Possibilities:
1.  We have an username that isn't in the form of email-studentName
    a. If the username isn't in abc_Users, return "Can't log in"
    b. If the username isn't a trainer in abc_Student, return "Can't log in"
    c. Otherwise, return "Can log in"
2.  We have an email in the form of email-studentName
    a. If the email doesn't exist in abc_Users, create it.
    b. If there is no record in abc_Student with a trainer=email-studentName and StudentName=studentName, create it.
    c. If we get to this point without error, return "Can log in" otherwise return "can't log in"
*/

/**
 * @param string $jsonString a json string to be decoded into a stdClass object
 *
 * @return stdClass a stdClass object with the fields 'code', 'msg' and 'canlogin'
 */
function decodeResponse(string $jsonString): stdClass
{
    $jsonObject = json_decode($jsonString);
    if ($jsonObject) {
        return $jsonObject;
    }

    $fail = json_encode(['code' => 0, 'msg' => 'JSON decode failed', 'canlogin' => 'NO']);

    return json_decode($fail);
}

/**
 * send a json string to stdout with the fields 'code', 'msg' and ''canlogin'.
 *
 * @param string $msg if http_code not 200, an explanation of the problem
 */
function sendRestResponse(int $httpCode = 200, string $msg = 'OK', bool $canLogin = false): void
{
    http_response_code($httpCode);
    $canString = $canLogin ? 'YES' : 'NO';
    echo json_encode(['code' => $httpCode, 'msg' => $msg, 'canlogin' => $canString]);
}

function createUserObject(string $user_login): stdClass
{
    $pattern = "/([a-zA-Z0-9.+_-]+@[a-zA-Z0-9]+\.[a-zA-Z0-9]+)(\-*)([a-zA-Z]*)/";

    preg_match($pattern, $user_login, $matches);
    $hasEmail = !empty($matches[0]);
    $hasStudent = !empty($matches[3]);
    $email = $hasEmail ? $matches[0] : '';
    $student = $hasStudent ? ucfirst($matches[3]) : '';
    $result = json_encode(['hasEmail' => $hasEmail, 'hasStudent' => $hasStudent, 'email' => $email, 'student' => $student]);

    return json_decode($result);
}

/**
 * Creates a StdClass basicResponse object with the fields 'success', 'code', 'msg' and 'result'
 * from the inputs and returns it.
 *
 * @param bool success   Was the rest call successful
 * @param int code      The http code we want to return (default 200)
 * @param string msg       The message to return (default OK)
 * @param mixed result    The result. Type depends
 *
 * @return string a JSON-encoded string with the fields 'success', 'msg' and 'result'
 */
function basicResponse(bool $success = true, int $code = 200, string $message = 'OK', $result = null): string
{
    return json_encode(['success' => $success, 'code' => $code, 'msg' => $message, 'result' => $result]);
}

/**
 * Executes a MySQLi query and returns a StdClass object with result set to true or false
 * depending on whether the query returned any rows.
 *
 * @param mysqli conn  The mysqli connection object
 * @param string query The SQL query to be executed
 *
 * @return bool returns true if the sql query returns something other than empty
 *
 * @throws Exception if query command unexpectedly fails
 */
function sqlExists($conn, $query): bool
{
    if ($result = $conn->query($query)) {
        $exists = ($result->num_rows > 0);
        $result->close();

        return $exists;
    } else {
        throw new Exception("Query unexpectedly failed ({$conn->error}): $query");
    }
}

/**
 * returns a basicResponse object wiht result set to true if user exists in phonics101.
 *
 * @param mysqli conn  The mysqli connection object
 * @param string username The username to look for in abc_Users
 *
 * @return bool returns true is user is already in user database
 *
 * @throws Exception if query command unexpectedly fails
 */
function isUserInPhonics($conn, $username): bool
{
    $query = "SELECT * FROM abc_Users WHERE EMail = '$username'";

    return sqlExists($conn, $query);
}

/**
 * returns a basicResponse object with result set to true if user exists in as a trainer in phonics101.
 *
 * @param mysqli conn  The mysqli connection object
 * @param string username The username to look for in abc_Users
 *
 * @return bool returns true if user is a trainer for a student
 *
 * @throws Exception if query command unexpectedly fails
 */
function isUserTeacher($conn, $username): bool
{
    $query = "SELECT * FROM abc_Student WHERE trainer1 = '$username'";

    return sqlExists($conn, $query);
}

/**
 * returns a basicResponse object wiht result set to true if user exists in as a
 * trainer in phonics101 for the given student name.
 *
 * @param mysqli conn  The mysqli connection object
 * @param string username The username to look for in abc_Student in trainer1 field
 * @param string studentname The student to look for in abc_Student
 *
 * @return bool returns true if the teacher/student combo is already in the database
 *
 * @throws Exception if query command unexpectedly fails
 */
function isTeacherStudentLogin($conn, $userName, $studentName): bool
{
    $query = "SELECT * FROM abc_Student WHERE trainer1 = '$userName' AND
        StudentName = '$studentName'";

    return sqlExists($conn, $query);
}

/**
 * Adds the user to abc_Users.
 *
 * @param mysqli conn  The mysqli connection object
 * @param string username The username to add to abc_Users

 *
 * @throws Exception if SQL error encountered adding $username to abc_Users
 */
function addUser($conn, $username): void
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

    $query = <<<QUERY
    INSERT INTO abc_Users (uuid, UserName, EMail, cargo, created, createdhuman,RoleId)
    VALUES ('$uuid', '$username', '$username', '$userCargo', $time, '$humanTime', 4)
QUERY;
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Unexpected error adding user $username. {$conn->error}");
    }
}

/**
 * @param mysqli $conn     the sql connection
 * @param string $username the username to be added as
 *
 * @throws Exception if SQL command to add student failed
 */
function addStudent(mysqli $conn, string $username, string $studentName): void
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
    $query = <<<QUERY
    INSERT INTO abc_Student (studentid, cargo, StudentName, project, trainer1, trainer2, trainer3, created, createdhuman, lastupdate)
    VALUES ('$studentId', '$studentCargo', '$studentName', '', '$username', '', '', $time, '$humanTime', $time)
QUERY;
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Unexpected error adding student $studentName with trainer $username. {$conn->error}");
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
function CheckLogin(mysqli $connect)
{
    $user_login = $_REQUEST['login'] ?? '';
    $user = createUserObject($user_login);

    try {
        if ($user->hasStudent) {
            $alreadyExists = isTeacherStudentLogin($connect, $user_login, $user->student);
            if ($alreadyExists) {
                sendRestResponse(200, 'OK', true);

                return;
            }

            // we couldn't find student/teacher in abc_Student so try and add
            if (!isUserInPhonics($connect, $user_login)) {
                addUser($connect, $user_login);
                addStudent($connect, $user_login, $user->student);
            }
        } else { //the user_login is non-hyphenated but might be in database
            if (isUserInPhonics($connect, $user_login) && isUserTeacher($connect, $user_login)) {
                sendRestResponse(200, 'OK', true);
            } else {
                sendRestResponse(200, "No students assigned to $user_login", false);

                return;
            }
        }
    } catch (Exception $ex) {
        sendRestResponse(500, $ex->getMessage(), false);
    }
}

// =========================================================
// Main entry point
// =========================================================

//turn off error reporting briefly
//$old_error_reporting = error_reporting(0);

// open the database
$conn = Util::dbConnect();
if ($conn->connect_errno) {
    sendRestResponse(500, 'Connect failed. '.$conn->connect_error, false);
    exit;
}

// verify we have a valid REST call
if (array_key_exists('login', $_REQUEST)) {
    CheckLogin($conn);
} else {
    sendRestResponse(400, 'Unknown method.', false);
}
$conn->close();
