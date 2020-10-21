<?php

namespace ReadXYZ\Models;

define('TEST_USER', 'smile_123456@gmail.com');
define('TEST_PASSWORD', 'xx');

use ReadXYZ\Database\StudentTable;
use ReadXYZ\Database\Users;
use ReadXYZ\Helpers\Debug;
use ReadXYZ\Helpers\Util;
use RuntimeException;

class Identity
{
    // The singleton method
    private static Identity $instance;           // Hold a singleton instance of the class
    private bool $isValidUser = false;
    private string $name = '';
    private string $userName = '';        // use the access functions
    private string $userRole = '';        //    don't access these directly from outside
    private string $project = '';
    private string $studentId = '';
    private string $currentScript = 'Blending';
    private string $sessionId = '';    // and the script we are training him on
    private string $deviceType = '';        // ties together a training session
    private string $request = '';        // 'phone', otherwise defaults to laptop
    private array $varList = [        // make it easier to add a var
        'name',
        'userName',
        'userRole',
        'project',
        'studentId',
        'sessionId',
        'deviceType',
        'currentScript',
        'request',
    ];        // 'Assessment' or similar, we need to choose student before we deliver it

    public function __construct()
    {
        $this->loadPersistentState();
    }

    // constructor only gets called the FIRST time the singleton is invoked

    public function loadPersistentState(): void
    {    // may want to encrypt one day
        $this->resetProperties();        // sets them all empty
        Util::sessionContinue();
        if (isset($_SESSION['identity'])
            and isset($_SESSION['identity']['userName'])) {    // identity is persistent through this session
            foreach ($this->varList as $var) {
                $this->$var = $_SESSION['identity'][$var];
            }
        }
        $this->isValidUser = (!empty($this->userName));
    }

    public function resetProperties()
    {
        foreach ($this->varList as $var) {
            $this->$var = '';
        }
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Identity();
        }

        return self::$instance;
    }

    /**
     * unsets the student currently associated with Identity.
     */
    public function clearIdentity(): void
    {
        $this->isValidUser = false;
        $this->resetProperties();        // sets them all empty
    }

    public function getProject(): string
    {
        if (!$this->isValidUser()) {
            throw new RuntimeException('User not yet validated.');
        }        // hopefully we catch the culprit in testing

        return $this->project;
    }

    public function isValidUser(): bool
    {
        return $this->isValidUser;    // everyone else should check this function
    }

    //////////////////////////////////////////////
    /// access functions (class vars are protected)
    //////////////////////////////////////////////

    // these guys expose the inside variables, but with sanity checks

    public function getName(): string
    {
        if (!$this->isValidUser()) {
            throw new RuntimeException('User not yet validated.');
        }

        return $this->name;
    }

    public function getUserName(): string
    {
        if (!$this->isValidUser()) {
            return 'Not Logged In';
        }        // but no error, needed for System Log...

        return $this->userName;
    }

    public function getUserRole(): string
    {
        if (!$this->isValidUser()) {
            throw new RuntimeException('User not yet validated.');
        }

        return $this->userRole;
    }

    public function getSessionId(): string
    {
        return $this->isValidUser() ? $this->sessionId : '';
    }

    /**
     * @return string
     *
     * @deprecated Use studentID() instead
     */
    public function student()
    {
        throw new RuntimeException('Deprecated - use studentID() instead');
    }

    /**
     * if valid user returns student id, otherwise returns false.
     *
     * @return bool|string
     */
    public function getStudentId(): string
    {        // this is an id, which is used everywhere
        if (!$this->isValidUser()) {
            return false;
        }

        return $this->studentId;
    }

    public function setCurrentScript($currentScript)
    {    // if we are setting the script
        if (!$this->isValidUser()) {
            throw new RuntimeException('User not yet validated.');
        }

        if (empty($this->studentId)) {
            throw new RuntimeException('not valid studentID when setting currentScript');
        }

        return $this->setUserProperty('currentScript', $currentScript);
    }

    public function setUserProperty($property, $value)
    {
        assert(!empty($property));
        assert('isValidUser' !== $property);

        if (!$this->isValidUser()) {
            throw new RuntimeException('User not yet validated.');
        }

        // update in case we keep values in this singleton
        $this->$property = $value;

        // now fix up the database
        $userDB = Users::getInstance();
        $cargo = $userDB->getUserCargo($this->userName);

        // make sure any strings we may add are in old cargos - default to empty
        foreach ($this->varList as $varValue) {
            if (!isset($cargo[$varValue])) {
                $cargo[$varValue] = '';
            }
        }

        // now fix up the database
        $cargo[$property] = $value;  // add if not already there
        if (isset($cargo['uuid'])) {
            $userDB->updateByKey($cargo['uuid'], $cargo);
        }
        $this->savePersistentState();

        return true;    // no problem
    }

    public function savePersistentState(): void
    {    // may want to encrypt one day
        Util::sessionContinue();
        if (!isset($_SESSION)) {
            $_SESSION = [];
        }
        if (isset($_SESSION['identity'])) {
            unset($_SESSION['identity']);
        }
        $_SESSION['identity'] = [];
        foreach ($this->varList as $var) {
            $_SESSION['identity'][$var] = $this->$var;
        }
    }

    public function request()
    {
        if (!$this->isValidUser()) {
            throw new RuntimeException('User not yet validated.');
        }

        return $this->request;
    }

    //////////////////////////////////////////////
    /// USERS table functions (validate, process a registration, etc)
    //////////////////////////////////////////////

    public function deviceType()
    {
        // no check for isValidUser, can always get deviceType
        if (empty($this->deviceType)) {
            return 'laptop';
        }

        return $this->deviceType;
    }

    /**
     * sign a user in without going to the login screen.
     *
     * @param $userName
     */
    public function backdoorSignin($userName): void
    {
        $cargo = Users::getInstance()->getUserCargo($userName);
        $this->name = $cargo['Name'];
        $this->userName = $cargo['UserName'];
        $this->isValidUser = true;    // congratulations
        $this->savePersistentState();
    }

    public function hasMultipleStudents(): bool
    {
        $studentTable = StudentTable::getInstance();
        $students = $studentTable->getAllStudents();

        return count($students) > 1;
    }

    public function validateSignin($user, $pswd, $deviceType = ''): BoolWithMessage
    {
        Debug::printNice('Identity', "validateSignin($user, $pswd, $deviceType)");
        // back door password:   admin / ???????

        if (empty($user) or empty($pswd)) {        // these are not allowed
            Debug::printNice('Identity', "Validate Signing for $user fails - empty");

            return BoolWithMessage::badResult('Username cannot be blank.');
        }

        $this->isValidUser = false;    // just in case

        $userDB = Users::getInstance();
        $cargo = $userDB->getUserCargo($user);
        Debug::printNice('Identity', $cargo);

        // no such user
        if (!$cargo) {
            if (isset($_SESSION['identity'])) {
                unset($_SESSION['identity']);
                error_log("Session Identity destroyed.");
            }
            Debug::printNice('Identity', "Validate Signing for $user fails - no such user");

            return BoolWithMessage::badResult("$user is not a valid user.");
        }

        // ok, user and password are verified
        $this->name = $cargo['Name'];
        $this->userName = $cargo['UserName'];
        if (isset($cargo['UserRole'])) {
            $this->userRole = $cargo['UserRole'];
        }
        if (isset($cargo['Project'])) {
            $this->project = $cargo['Project'];
        }
        $this->deviceType = $deviceType;

        $this->isValidUser = true;    // congratulations
        $cookie = new Cookie();
        if ($cookie->getUsername() != $this->userName) {
            $cookie->setUsername($this->userName);
        }
        $this->savePersistentState();

        $studentTable = StudentTable::getInstance();
        $students = $studentTable->getAllStudents();
        if (count($students) < 1) {
            return BoolWithMessage::badResult("$user has no students. See administrator");
        }
        Debug::printNice('Identity', "Validate Signing for $user succeeds");

        return BoolWithMessage::goodResult();    // true
    }

    public function randomString($length)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz1234567890';

        return substr(str_shuffle($chars), 0, $length);
    }

    public function setStudent($studentID)
    {    // if we are setting the student,
        if (!$this->isValidUser()) {
            throw new RuntimeException('User not yet validated.');
        }

        $this->sessionId = uniqid();    //    then also start a new session
        $this->isValidUser = true;
        $this->setUserProperty('studentId', $studentID); // saves persistent
        $this->setUserProperty('sessionId', $this->sessionId);

        if (empty($this->studentId)) {  // we just set it, really just testing 'setUserProperty()'
            throw new RuntimeException('not valid studentID after setting');
        }
        $cookie = new Cookie();

        $cookie->setUsername($this->userName);
        $cookie->setStudentId($studentID, $this->sessionId);
        // if ($cookie->getUsername() != $this->userName) {
        //     $cookie->setUsername($this->userName);
        // }
        // if (($cookie->getStudentId() != $studentID) or ($this->sessionId != $cookie->getSessionId())) {
        //     $cookie->setStudentId($studentID, $this->sessionId);
        // }
        return true;
    }

    public function clearStudent()
    {    // if we are setting the student,
        // don't test whether we have one, this is a good function
        // to call more often than necessary.

        $this->sessionId = '';    //    also clear the sessionID

        return $this->setUserProperty('studentId', '');
    }

    public function setRequest($request)
    {    // if we are setting the script
        if (!$this->isValidUser()) {
            throw new RuntimeException('User not yet validated.');
        }

        $this->setUserProperty('request', $request);

        return $request;
    }
}
