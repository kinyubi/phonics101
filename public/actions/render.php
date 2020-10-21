<?php

use ReadXYZ\Helpers\Util;
use ReadXYZ\Lessons\Lessons;
use ReadXYZ\Models\Cookie;
use ReadXYZ\Twig\LessonListTemplate;
use ReadXYZ\Twig\LessonTemplate;
use ReadXYZ\Twig\LoginTemplate;
use ReadXYZ\Twig\StudentListTemplate;

require 'autoload.php';


if (Util::isLocal()) {
    error_reporting(E_ALL | E_STRICT);
}
$cookie = new Cookie();
$foundSession = $cookie->tryContinueSession();

$pageToRender = ($_REQUEST['P1'] ?? $_REQUEST['page'] ?? $_REQUEST['target'] ??  'none');

switch (Util::convertCamelToSnakeCase($pageToRender)) {
    case 'lesson_list':
        (new LessonListTemplate())->display();
        break;
    case 'lesson':
        // We handle refresh index updating here.
        $lesson = $_REQUEST['lesson'] ?? 'unknown';
        $tab = Util::fixTabName($_REQUEST['tab'] && '');
        $refresh = ('1' == ($_REQUEST['refresh'] ?? '0'));

        $cookie->updateListIndex($tab);
        $lessonTemplate = new LessonTemplate($lesson, $tab);
        $lessonTemplate->display();
        break;
    case 'student_list':
        (new StudentListTemplate())->display();
        break;
    case 'next_lesson':
        $lessonTemplate = new LessonTemplate(Lessons::getInstance()->getNextLessonName(), '');
        $lessonTemplate->display();
        break;
    case 'login':
        $message = $_REQUEST['P2'] ?? '';
        (new LoginTemplate($message))->display();
        break;
    default:
        echo Util::redBox("$pageToRender is not a valid render target.");
}
