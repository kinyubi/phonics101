<?php


namespace App\ReadXYZ\Models;

use App\ReadXYZ\Data\UserMasteryData;
use App\ReadXYZ\Database\StudentTable;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Twig\LessonListTemplate;
use App\ReadXYZ\Twig\LessonTemplate;
use App\ReadXYZ\Twig\LoginTemplate;
use App\ReadXYZ\Twig\StudentListTemplate;
use Throwable;

class RouteMe
{
    // private static function httpPost($url, $data){
    // 	$options = array(
    // 		'http' => array(
    //      		'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
    //         	'method'  => 'POST',
    //         	'content' => http_build_query($data)
    //     	)
    //     );
    // 	$context  = stream_context_create($options);
    // 	return file_get_contents($url, false, $context);
    // }

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
     * @param bool $forceStudentList
     * @return void HTML for a student list or lesson list as appropriate is displayed
     */
    public static function autoLoginDisplay(bool $forceStudentList = false): void
    {
        Session::sessionContinue();
        $identity = Identity::getInstance();
        if ($identity->hasMultipleStudents() || $forceStudentList) {
            (new StudentListTemplate())->display();
        } else {
            $allStudents = StudentTable::getInstance()->GetAllStudents();
            $studentId = $allStudents[0]['studentID'];
            $identity->setStudent($studentId);
            $identity->savePersistentState();
            (new LessonListTemplate())->display();
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

        if (empty($username) or empty($password) ) {
            (new LoginTemplate())->display('Username and password must both be provided.');
            exit;
        }
        $result = $identity->validateSignin($username, $password);
        if ($result->failed()) {
            (new LoginTemplate())->display($result->getErrorMessage());
            exit;
        }
        self::autoLoginDisplay($password == 'zz');
    }

    public static function parseRoute()
    {
        $cookie = new Cookie();
        try {
            $foundSession = $cookie->tryContinueSession();
        } catch (Throwable $ex) {
            $foundSession = false;
        }


        $requestUri = parse_url($_SERVER['REQUEST_URI']);
        $parameters = [];
        $posts = $_REQUEST ?? [];
        foreach($posts as $key => $value) {
            $parameters[$key] = $value;
        }
        $path = $requestUri['path'] ?? '/';
        switch ($path) {
            case '/':
                if ($foundSession) {
                    self::autoLoginDisplay();
                } else {
                    (new LoginTemplate())->display();
                }
                break;
            case '/wp':
                $login = new ProcessWordPressRequest();
                echo $login->handleRequestAndGetResponse($parameters);
                break;
            case '/otp':
                $processor = new ProcessOneTimePassword();
                $processor->handleRequestAndEchoResponse($parameters);
                break;
            case '/timer':
                include $_SERVER['DOCUMENT_ROOT'] . '/public/actions/timers.php';
                break;
            case '/login':
                (new LoginTemplate())->display();
                break;
            case '/lesson':
                $lessonName = $parameters['P1'] ?? $parameters['lessonName'] ?? $cookie->getCurrentLesson() ?? '';
                $initialTabName = $parameters['P2'] ?? $parameters['initialTabName'] ?? $cookie->getCurrentTab() ?? '';
                $lessonTemplate = new LessonTemplate($lessonName, $initialTabName);
                $lessonTemplate->display();
                break;
            case '/lessonlist':

                break;
            case '/studentlist':
                self::autoLoginDisplay(true);                                                ;
                break;
            case '/update_mastery':
                $mastery = new UserMasteryData();
                $mastery->processRequest();
                break;
        }
    }
}


