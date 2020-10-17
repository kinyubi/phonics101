<?php

use ReadXYZ\Database\StudentTable;
use ReadXYZ\Helpers\Util;
use ReadXYZ\Lessons\Lessons;
use ReadXYZ\Models\Cookie;
use ReadXYZ\Twig\LessonListTemplate;
use ReadXYZ\Twig\LessonTemplate;
use ReadXYZ\Twig\Twigs;

require 'autoload.php';


if (Util::isLocal()) {
    error_reporting(E_ALL | E_STRICT);
}
$cookie = Cookie::getInstance();
$foundSession = $cookie->tryContinueSession();

$pageToRender = ($_REQUEST['P1'] ?? $_REQUEST['page'] ?? $_REQUEST['target'] ??  'none');
$twigs = Twigs::getInstance();

switch (Util::convertCamelToSnakeCase($pageToRender)) {
    case 'lesson_list':
        (new LessonListTemplate())->display();
        break;
    case 'lesson':
        // We handle refresh index updating here.
        // Everything else if handled by Twigs::renderLesson
        $lesson = $_REQUEST['lesson'] ?? 'unknown';
        $tab = Util::fixTabName($_REQUEST['tab'] && '');
        $refresh = ('1' == ($_REQUEST['refresh'] ?? '0'));

        Cookie::getInstance()->updateListIndex($tab);
        $lessonTemplate = new LessonTemplate($lesson, $tab);
        $lessonTemplate->displayLesson();
        break;
    case 'student_list':
        $studentTable = StudentTable::getInstance();
        $allStudents = $studentTable->GetAllStudents();
        echo $twigs->renderStudentList($allStudents);
        break;
    case 'next_lesson':
        $lessonTemplate = new LessonTemplate(Lessons::getInstance()->getNextLessonName(), '');
        $lessonTemplate->displayLesson();
        break;
    case 'login':
        $message = $_REQUEST['P2'] ?? '';
        echo $twigs->login($message);
        break;
    default:
        echo Util::redBox("$pageToRender is not a valid render target.");
}
