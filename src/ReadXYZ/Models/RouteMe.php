<?php


namespace App\ReadXYZ\Models;

use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Data\TrainersData;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Targets\FormAction;
use App\ReadXYZ\Twig\CrudTemplate;
use App\ReadXYZ\Twig\LessonListTemplate;
use App\ReadXYZ\Twig\LessonTemplate;
use App\ReadXYZ\Twig\LoginTemplate;
use App\ReadXYZ\Twig\StudentListTemplate;

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

// ======================== STATIC METHODS =====================
    /**
     * After a user has been validated this takes him to the proper screen
     * @param bool $forceStudentList
     * @return void HTML for a student list or lesson list as appropriate is displayed
     * @throws PhonicsException
     */
    public static function autoLoginDisplay(bool $forceStudentList = false): void
    {
        $session     = new Session();
        $trainerCode = $session->getTrainerCode();
        if (empty($trainerCode)) {
            throw new PhonicsException("We shouldn't get here without session user being set.");
        }
        $studentsData = new StudentsData();
        $students     = $studentsData->getStudentNamesForUser($trainerCode);
        if ((count($students) > 1) || $forceStudentList) {
            (new StudentListTemplate())->display();
        } else {
            if (count($students) == 1) {
                $studentCode = $students[0]['studentCode'];
                $session->updateStudent($studentCode);
                (new LessonListTemplate())->display();
            } else {
                $trainersData = new TrainersData();
                if ($trainersData->isAdmin($trainerCode)) {
                    throw new PhonicsException('Admin screen not yet implemented');
                } else {
                    $userName = $trainersData->getUsername($trainerCode);
                    throw new PhonicsException("Trainer $userName has no students.");
                }
            }
        }
    }

    public static function generatePassword()
    {
        $chars      = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $strength   = rand(10, 20);
        $lastPos    = strlen($chars) - 1;
        $randomWord = '';
        for ($i = 0; $i < $strength; $i++) {
            $randomLetter = $chars[mt_rand(0, $lastPos)];
            $randomWord   .= $randomLetter;
        }

        return $randomWord;
    }

    /**
     * @throws PhonicsException
     */
    public static function parseRoute()
    {
        $session = new Session();

        $requestUri     = parse_url($_SERVER['REQUEST_URI']);
        $postParameters = [];
        $mainRoute      = '';
        $target         = '';
        // we want to keep form handlers out of the request_uri
        // if a form has an input named 'handler', we set the value to a value that identifies the handler to use
        if (isset($_REQUEST['handler'])) {
            $mainRoute = 'handler';
            $target    = $_REQUEST['handler'];
            unset($_REQUEST['handler']);
        }
        foreach ($_REQUEST as $key => $value) {
            $postParameters[$key] = $value;
        }

        //trim the path part of request_uri of whitespace and leading forward slash
        $fullPath = trim($requestUri['path'] ?? '');
        if ((strlen($fullPath) > 0) && ($fullPath[0] == '/')) {
            $fullPath = substr($fullPath, 1);
        }

        $routeParts = empty($fullPath) ? ['default'] : explode('/', $fullPath);

        // we handle the instance when a handler is part of the request_url (discouraged).
        if (empty($mainRoute) && in_array($routeParts[0], ['handler', 'act', 'action'])) {
            if (count($routeParts) > 1) {
                $mainRoute = 'handler';
                array_shift($routeParts);
                $target = array_shift($routeParts);
            } else {
                throw new PhonicsException("Ill-formed route: $fullPath");
            }
        } elseif (empty($mainRoute)) {
            $mainRoute = array_shift($routeParts) ?? '';
        }


        // Some routes have precondition, handle that here
        switch ($mainRoute) {
            case 'lesson':
                if ( ! $session->hasStudent()) {
                    $mainRoute = 'default';
                }
                break;
            case 'crud':
                if (( ! $session->isStaff()) || (count($routeParts) == 0)) {
                    throw new PhonicsException("Non-staff access to CRUD operations disallowed.");
                }
        }
        switch ($mainRoute) {
            // processing for forms
            case 'handler':
                $action = new FormAction();

                if ('crud' == $target) {
                    // create/read/update/delete
                    (new CrudAction())->handlePost();
                } elseif ('mastery' == $target) {
                    // mastery form on mastery tab
                    $action->wordMasteryFormHandler();
                } elseif ('student' == $target) {
                    // handle selection of student when trainer has multiple students
                    $action->studentSelectionHandler($routeParts[0]);
                } elseif ('timer' == $target) {
                    // handle timer button for fluency, test and practice
                    $action->timersHandler();
                } elseif ('validate' == $target) {
                    $action->loginHandler();
                } elseif ('lesson' == $target) {
                    //initialTabName set in LessonTemplate constructor
                    //phonics.js sets initialTabName in reload function as a route part
                    //FormAction::timersHandler sets initialTabName when initializing LessonTemplate object
                    $action->lessonSelectionHandler($postParameters, $routeParts);
                }
                break;
            case 'crud':
                // see tables_crud.html.twig
                (new CrudTemplate('abc_' . $routeParts[0]))->display();
                break;
            case 'lessonlist':
                self::rerouteCheck($session);
                (new LessonListTemplate())->display();
                break;
            case 'login':
                (new LoginTemplate())->display();
                break;
            case 'otp':
                $processor = new ProcessOneTimePassword();
                $processor->handleRequestAndEchoResponse($postParameters);
                break;
            case 'studentlist':
                self::autoLoginDisplay(true);
                break;
            case 'test':
                if ( ! Util::isLocal()) {
                    return;
                }
                if (empty($routeParts)) {
                    Util::redBox('test route requires additional postParameters.');
                }
                switch ($routeParts[0]) {
                    case 'lesson-list':
                        (new LessonListTemplate())->display();
                        break;
                }
                break;

            case 'wp':
                $login = new ProcessWordPressRequest();
                echo $login->handleRequestAndGetResponse($postParameters);
                break;
            case 'default':
            default:
                if ($session->hasLesson()) {
                    (new LessonTemplate($session->getCurrentLessonName(), ''))->display();
                } else {
                    if ($session->hasStudent()) {
                        (new LessonListTemplate())->display();
                    } else {
                        if ($session->hasTrainer()) {
                            self::autoLoginDisplay();
                        } else {
                            (new LoginTemplate())->display();
                        }
                    }
                }
                break;
        }
    }

// ======================== PRIVATE METHODS =====================
    /**
     * @param Session $session
     * @throws PhonicsException
     */
    private static function rerouteCheck(Session $session)
    {
        if ( ! $session->isValid()) {
            if ($session->hasTrainer()) {
                // we have a trainer but not a student
                self::autoLoginDisplay();
            } else {
                (new LoginTemplate())->display();
            }
        }
    }

}


