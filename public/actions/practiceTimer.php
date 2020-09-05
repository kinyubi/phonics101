<?php

// we only use $_REQUEST['seconds']. We already know the current lesson and student.
use ReadXYZ\Helpers\Util;
use ReadXYZ\Models\Cookie;
use ReadXYZ\Models\Student;
use ReadXYZ\Twig\Twigs;

require 'autoload.php';

if (Util::isLocal()) {
    error_reporting(E_ALL | E_STRICT);
}
$cookie = Cookie::getInstance();
if (!$cookie->tryContinueSession()) {
    echo $twigs->login('Login has expired (fluency timer).');
    exit;
}
$student = Student::getInstance();

$currentLessonName = $cookie->getCurrentLesson();
$seconds = $_REQUEST['seconds'] ?? 0;
if ($seconds) {
    $student->cargo['currentLessons'][$currentLessonName]['learningCurve'][time()] = $seconds;
    while (count($student->cargo['currentLessons'][$currentLessonName]['learningCurve']) > 8) {
        array_shift($student->cargo['currentLessons'][$currentLessonName]['learningCurve']);
    }
} else {
    $studentName = $student->getCapitalizedStudentName();
    error_log("Fluency timed test for $studentName was 0.");
}

$USE_NEXT_LESSON_BUTTON = true;
Twigs::getInstance()->renderLesson($currentLessonName, 'fluency', $USE_NEXT_LESSON_BUTTON);
