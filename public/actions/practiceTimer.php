<?php

// we only use $_REQUEST['seconds']. We already know the current lesson and student.
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Models\Student;
use App\ReadXYZ\Twig\LessonTemplate;

require 'autoload.php';

if (Util::isLocal()) {
    error_reporting(E_ALL | E_STRICT);
}

$currentLessonName = Session::currentLesson();
$seconds = $_REQUEST['seconds'] ?? 0;

$lessonTemplate = new LessonTemplate($currentLessonName, 'fluency');
$lessonTemplate->display();
