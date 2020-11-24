<?php


namespace App\ReadXYZ\Models;

use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Data\TrainersData;
use App\ReadXYZ\Data\WordMasteryData;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Twig\LessonListTemplate;
use App\ReadXYZ\Twig\LessonTemplate;
use App\ReadXYZ\Twig\LoginTemplate;
use App\ReadXYZ\Twig\StudentListTemplate;
use LogicException;
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
        $session = new Session();
        $trainerCode = $session->getTrainerCode();
        if ($trainerCode == 0) {
            throw new LogicException("We shouldn't get here without session user being set.");
        }
        $studentsData = new StudentsData();
        $students = $studentsData->getStudentsForUser($trainerCode);
        if ((count($students) > 1) || $forceStudentList) {
            (new StudentListTemplate())->display();
        } else if (count($students) == 1) {
            $studentCode = $students[0]['studentCode'];
            $session->updateStudent($studentCode);
            (new LessonListTemplate())->display();
        } else {
            $trainersData = new TrainersData();
            if ($trainersData->isAdmin($trainerCode)) {
                throw new LogicException('Admin screen not yet implemented');
            } else {
                $userName = $trainersData->getUsername($trainerCode);
                throw new LogicException("Trainer $userName has no students.");
            }
        }
    }

    /**
     * @param array $parameters
     */
    public static function processLogin(array $parameters)
    {
        $session = new Session();
        $session->clearSession();
        $username = $parameters['username'] ?? $parameters['P1'] ?? '';
        $password = $parameters['password'] ?? $parameters['P2']  ??'';

        if (empty($username) or empty($password) ) {
            (new LoginTemplate())->display('Username and password must both be provided.');
            exit;
        }
        $validationStatus= (new TrainersData())->verifyPassword($username, $password);
        if (false == $validationStatus) {
            (new LoginTemplate())->display('Invalid user name or password.');
            exit;
        }
        self::autoLoginDisplay(false);
    }

    public static function parseRoute()
    {
        $session = new Session();
        $requestUri = parse_url($_SERVER['REQUEST_URI']);
        $parameters = [];
        $posts = $_REQUEST ?? [];
        foreach($posts as $key => $value) {
            $parameters[$key] = $value;
        }
        $fullPath= trim($requestUri['path']);
        $parms = [];
        if (($fullPath == '') || ($fullPath == '/') || ($fullPath[0] != '/')) {
            $parms[0] = '';
        } else {
            $parms = explode('/', substr($fullPath, 1));
        }

        $mainRoute = array_shift($parms);
        switch ($mainRoute) {
            case '':
                if ($session->hasLesson()) {
                    (new LessonTemplate($session->getCurrentLessonName(), ''))->display();
                } else if ($session->hasStudent()) {
                    (new LessonListTemplate())->display();
                } else if ($session->hasTrainer()) {
                    self::autoLoginDisplay();
                } else {
                    (new LoginTemplate())->display();
                }
                break;
            case 'wp':
                $login = new ProcessWordPressRequest();
                echo $login->handleRequestAndGetResponse($parameters);
                break;
            case 'otp':
                $processor = new ProcessOneTimePassword();
                $processor->handleRequestAndEchoResponse($parameters);
                break;
            case 'timer':
                include $_SERVER['DOCUMENT_ROOT'] . '/public/actions/timers.php';
                break;
            case 'login':
                (new LoginTemplate())->display();
                break;
            case 'lesson':
                $lessonName = $parameters['P1'] ?? $parameters['lessonName'] ?? $session->getCurrentLessonName() ?? '';
                $initialTabName = $parameters['P2'] ?? $parameters['initialTabName']  ?? '';
                $lessonTemplate = new LessonTemplate($lessonName, $initialTabName);
                $lessonTemplate->display();
                break;
            case 'lessonlist':
                if (count($parms) > 1) {
                    $trainerCode = $parms[0];
                    //TODO: finish this
                }
                break;
            case 'studentlist':
                self::autoLoginDisplay(true);
                break;
            case '/update_mastery':
                $mastery = new WordMasteryData();
                $mastery->processRequest();
                break;
            case 'test':
                if (!Util::isLocal()) return;
                if (empty($parms)) Util::redBox('test route requires additional parameters.');
                switch($parms[0]) {
                    case 'lesson-list':
                        (new LessonListTemplate())->display();
                        break;
                }
        }
    }
}


