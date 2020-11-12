<?php
// This is the target for the fluency timer and test timer

// we only use $_REQUEST['seconds']. We already know the current lesson and student.
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\Log;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Models\Student;
use App\ReadXYZ\Twig\LessonTemplate;
use App\ReadXYZ\Twig\LoginTemplate;

require 'autoload.php';

if (Util::isLocal()) {
    error_reporting(E_ALL | E_STRICT);
}
Session::sessionContinue();
$student = Student::getInstance();
$studentName = $student->getCapitalizedStudentName();
$currentLessonName = Session::currentLesson();
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
        Log::info("Fluency timed test for $studentName was 0.");
    }


    $lessonTemplate->display();
} elseif ('test' == $source) {
    $assumedLessonName = $student->prepareCurrentForUpdate();
    $seconds = intval($_REQUEST['seconds'] ?? '0');
    $student->updateTestCurveCargo($seconds);

    $lessonTemplate->display();
} elseif ('testMastery' == $source) {
    $assumedLessonName = $student->prepareCurrentForUpdate();
    $masteryType = $_REQUEST['masteryType'];
    $student->updateTestMastery($currentLessonName, $masteryType);
    $lessonTemplate->display();

} else {
    $message = "Call to timers.php with unrecognized source $source";
    Log::error($message);
    echo Util::redBox($message);
}

