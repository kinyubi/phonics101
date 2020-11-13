<?php
// This is the target for the fluency timer and test timer

// we only use $_REQUEST['seconds']. We already know the current lesson and student.
use App\ReadXYZ\Data\StudentLessonsData;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\Log;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Twig\LessonTemplate;

require 'autoload.php';

if (Util::isLocal()) {
    error_reporting(E_ALL | E_STRICT);
}
$session = new Session();
if (!$session->hasLesson()) {
    throw new RuntimeException('timers cannot be targeted without an active lesson.');
}
// $_REQUEST['source'] should be test, testMastery or fluency
$source = $_REQUEST['source'] ?? 'unknown';
$seconds = $_REQUEST['seconds'] ?? 0;
$tab = ('fluency' == $source) ? 'fluency' : 'test';

$lessonTemplate = new LessonTemplate($session->getCurrentLessonName(), $tab);
$studentLessonData = new StudentLessonsData();

if (('fluency' == $source) || ('test' == $source)) {
    $studentLessonData->updateTimedTest($source, $seconds);
    $lessonTemplate->display();
} elseif ('testMastery' == $source) {
    // masteryType is 'Advancing' or 'Mastered'
    $masteryType = $_REQUEST['masteryType'];
    $studentLessonData->updateMastery($masteryType);
    $lessonTemplate->display();

} else {
    $message = "Call to timers.php with unrecognized source $source";
    Log::error($message);
    echo Util::redBox($message);
}

