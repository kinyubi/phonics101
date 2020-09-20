<?php


namespace ReadXYZ\Models;

use ReadXYZ\Database\OneTimePass;
use ReadXYZ\Database\StudentTable;
use ReadXYZ\Helpers\Util;
use ReadXYZ\Twig\Twigs;
use Throwable;

class RouteMe
{
    private static function httpPost($url, $data){
    	$options = array(
    		'http' => array(
         		'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            	'method'  => 'POST',
            	'content' => http_build_query($data)
        	)
        );
    	$context  = stream_context_create($options);
    	return file_get_contents($url, false, $context);
    }

    public static function generatePassword()
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $strength = rand(10, 20);
        $lastPos = strlen($chars) - 1;
        $randomWord = '';
        for($i = 0; $i < $strength; $i++) {
            $randomLetter = $chars[mt_rand(0, $lastPos)];
            $randomWord .= $randomLetter;
        }

        return $randomWord;
    }

    /**
     * After a user has been validated this takes him to the proper screen
     * @return string HTML for a student list or lesson list as appropriate
     */
    public static function autoLogin(): string
    {
        $twigs = Twigs::getInstance();
        Util::sessionContinue();
        $identity = Identity::getInstance();
        $studentTable = StudentTable::getInstance();
        $allStudents = $studentTable->GetAllStudents();
        if ($identity->hasMultipleStudents()) {
            return $twigs->renderStudentList($allStudents);
        } else {
            $studentId = $allStudents[0]['studentID'];
            $identity->setStudent($studentId);
            $identity->savePersistentState();
            $cookie = Cookie::getInstance();
            $args = [];
            $args['mostRecentLesson'] = $cookie->getCurrentLesson();
            $args["mostRecentTab"] = $cookie->getCurrentTab();
            return $twigs->renderLessonList($args);
        }
    }

    /**
     * @param array $parameters
     */
    public static function processLogin(array $parameters)
    {
        $identity = Identity::getInstance();
        $identity->clearIdentity();
        $username = $parameters['username'] ?? $parameters['P1'] ?? '';
        $password = $parameters['password'] ?? $parameters['P2']  ??'';
        $action = $parameters['action'] ?? $parameters['P3'] ?? '';
        $errorMessage = '';
        $twigs = Twigs::getInstance();
        if (empty($username) or empty($password) ) {
            echo $twigs->login('Username and password must both be provided.');
            exit;
        }
        $result = $identity->validateSignin($username, $password);
        if ($result->failed()) {
            echo $twigs->login($result->getErrorMessage());
            exit;
        }
        echo self::autoLogin();
    }

    public static function parseRoute()
    {
        $cookie = Cookie::getInstance();
        try {
            $foundSession = $cookie->tryContinueSession();
        } catch (Throwable $ex) {
            $foundSession = false;
        }


        $requestUri = parse_url($_SERVER['REQUEST_URI']);
        $parameters = $requestUri['query'] ?? [];
        $posts = $_REQUEST ?? [];
        foreach($posts as $key => $value) {
            $parameters[$key] = $value;
        }
        $path = $requestUri['path'] ?? '/';
        switch ($path) {
            case '/':
                if ($foundSession) {
                    echo self::autoLogin();
                } else {
                    echo Twigs::getInstance()->login();
                }
                break;
            case '/wp':
                $login = new ProcessWordPressRequest();
                echo $login->handleRequestAndGetResponse($parameters);
                break;
            case '/otp':
                $processor = new ProcessOneTimePassword();
                echo $processor->handleRequestAndGetResponse($parameters);
                break;
            case '/timer':
                include $_SERVER['DOCUMENT_ROOT'] . '/public/actions/timers.php';
                break;
            case '/login':
                echo Twigs::getInstance()->login();


        }
    }
}


