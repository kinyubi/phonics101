<?php


namespace App\ReadXYZ\Models;

use App\ReadXYZ\Data\CompareLocalRemote;
use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Data\TrainersData;
use App\ReadXYZ\Data\Views;
use App\ReadXYZ\Handlers\CrudHandler;
use App\ReadXYZ\Handlers\LessonSelector;
use App\ReadXYZ\Handlers\LoginForm;
use App\ReadXYZ\Handlers\StudentSelector;
use App\ReadXYZ\Handlers\TimerForms;
use App\ReadXYZ\Handlers\WordMasteryForm;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\ScreenCookie;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Twig\CacheTemplate;
use App\ReadXYZ\Twig\CrudTemplate;
use App\ReadXYZ\Twig\LessonListTemplate;
use App\ReadXYZ\Twig\LessonTemplate;
use App\ReadXYZ\Twig\LoginTemplate;
use App\ReadXYZ\Twig\TicTacToeTemplate;
use App\ReadXYZ\Twig\ZooTemplate;
use App\ReadXYZ\Twig\StudentListTemplate;

class RouteMe
{


// ======================== STATIC METHODS =====================
    /**
     * After a user has been validated this takes him to the proper screen
     * @param string $explicitStudentName
     * @return void HTML for a student list or lesson list as appropriate is displayed
     * @throws PhonicsException on ill-formed SQL
     */
    public static function computeImpliedRoute(string $explicitStudentName = ''): void
    {
        $trainerCode = Session::getTrainerCode();
        if (empty($trainerCode)) {
            throw new PhonicsException("We shouldn't get here without session user being set.");
        }

        $students     = Views::getInstance()->getMapOfStudentsForUser($trainerCode);
        if ((count($students) > 1) && empty($explicitStudentName)) {
            (new StudentListTemplate())->display();
            exit;
        } else {
            if (count($students) == 1) {
                $studentCode = reset($students); //returns the first value in an associative array;
                Session::updateStudent($studentCode);
                (new LessonListTemplate())->display();
                exit;
            } else {
                if (not(empty($explicitStudentName))) {
                    $studentCode = '';
                    foreach($students as $name => $code) {
                        if (strtolower($name) == strtolower($explicitStudentName)) {
                            $studentCode = $code;
                            break;
                        }
                    }
                    if ($studentCode) {
                        Session::updateStudent($studentCode);
                        (new LessonListTemplate())->display();
                        exit;
                    }
                }
                // Were here because someone appended a student name to the email that doesn't belong to them.
                (new LoginTemplate("Please specify just an email address or an email with a valid student name."));
            }
        }
    }


    /**
     * @throws PhonicsException on ill-formed SQL
     */
    public static function parseRoute()
    {
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

        // the routines that consume routeParts should get integers instead of strings if numeric
        for ($i=0; $i<count($routeParts); $i++) {
            if (is_numeric($routeParts[$i])) {$routeParts[$i] = intval($routeParts[$i]);}
        }
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
                if ( ! Session::hasStudent()) {
                    $mainRoute = 'default';
                }
                break;
            case 'crud':
                if (( ! Session::isStaff()) || (count($routeParts) == 0)) {
                    throw new PhonicsException("Non-staff access to CRUD operations disallowed.");
                }
        }
        switch ($mainRoute) {
            // processing for forms
            case 'handler':
                switch ($target) {
                    case 'crud':
                        CrudHandler::handlePost();
                        break;
                    case 'getStudents':
                        LoginForm::handleUserEntry($routeParts);
                        break;
                    case 'mastery':
                        WordMasteryForm::handlePost();
                        break;
                    case 'student':
                        StudentSelector::route($routeParts[0]);
                        break;
                    case 'timer':
                        TimerForms::handlePost();
                        break;
                    case 'validate':
                        LoginForm::handlePost();
                        break;
                    case 'lesson':
                        LessonSelector::route($postParameters, $routeParts);
                        break;
                    case 'award':
                        $result = (new StudentsData())->advanceAnimal(Session::getStudentCode());
                        echo $result;
                        break;
                    default:
                }
                break;
            case 'sess_fix':

            case 'clear':
                (new CacheTemplate())->display();

            case 'crud':
                // see tables_crud.html.twig
                (new CrudTemplate('abc_' . $routeParts[0]))->display();
                break;
            case 'lessons':
            case 'lessonlist':
                self::rerouteCheck();
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
                self::computeImpliedRoute();
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
            case 'compare':
                (new CompareLocalRemote())->analyze();
                break;
            case 'default':
            case 'reloadLesson':
            case '':
                if (Session::hasLesson()) {
                    (new LessonTemplate(Session::getCurrentLessonName(), ''))->display();
                } else {
                    if (Session::hasStudent()) {
                        (new LessonListTemplate())->display();
                    } else {
                        if (Session::hasTrainer()) {
                            self::computeImpliedRoute();
                        } else {
                            (new LoginTemplate())->display();
                        }
                    }
                }
                break;
            default:
                (new LoginTemplate())->display("$mainRoute is unknown.");
        }
    }

// ======================== PRIVATE METHODS =====================

    /**
     * Performs a check before we display a lesson list.
     * If the session variables tell us we don't have a trainer and a student we're going to the login screen.
     * If the session variables tell us we have a trainer validated but not a student we pass off the decision
     * to computeImpliedRoute.
     * @throws PhonicsException on ill-formed SQL
     */
    private static function rerouteCheck()
    {
        if ( ! Session::isValid()) {
            if (Session::hasTrainer()) {
                // we have a trainer but not a student
                self::computeImpliedRoute();
            } else {
                (new LoginTemplate())->display();
            }
        }
    }

}


