<?php
// This is the target for the fluency timer and test timer

// we only use $_REQUEST['seconds']. We already know the current lesson and student.
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\Cookie;
use App\ReadXYZ\Models\Student;
use App\ReadXYZ\Twig\LessonTemplate;
use App\ReadXYZ\Twig\LoginTemplate;

require 'autoload.php';

if (Util::isLocal()) {
    error_reporting(E_ALL | E_STRICT);
}
$cookie = new Cookie();
if (!$cookie->tryContinueSession()) {
    (new LoginTemplate("Login has expired (fluency timer).\n" . $cookie->getCookieString()))->display();
    exit;
}
$student = Student::getInstance();
$studentName = $student->getCapitalizedStudentName();
$currentLessonName = $cookie->getCurrentLesson();
$student->saveLessonSelection($currentLessonName);
$source = $_REQUEST['source'] ?? 'unknown';
$seconds = $_REQUEST['seconds'] ?? 0;
$tab = ('fluency' == $source) ? 'fluency' : 'test';

$lessonTemplate = new LessonTemplate($currentLessonName, $tab);

if ('fluency' == $source) {
    if ($seconds) {
        $student->cargo['currentLessons'][$currentLessonName]['learningCurve'][time()] = $seconds;
        while (count($student->cargo['currentLessons'][$currentLessonName]['learningCurve']) > 8) {
            array_shift($student->cargo['currentLessons'][$currentLessonName]['learningCurve']);
        }
    } else {
        $studentName = $student->getCapitalizedStudentName();
        error_log("Fluency timed test for $studentName was 0.");
    }


    $lessonTemplate->display();
} elseif ('test' == $source) {
    $assumedLessonName = $student->prepareCurrentForUpdate();
    $seconds = intval($_REQUEST['seconds'] ?? '0');
    $student->updateLearningCurveCargo($assumedLessonName, $seconds);

    $lessonTemplate->display();
} elseif ('testMastery' == $source) {
    $assumedLessonName = $student->prepareCurrentForUpdate();
    $masteryType = $_REQUEST['masteryType'];
    $student->updateTestMastery($currentLessonName, $masteryType);
    $lessonTemplate->display();

} else {
    $message = "Call to timers.php with unrecognized source $source";
    error_log($message);
    echo Util::redBox($message);
}

