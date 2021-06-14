<?php


namespace App\ReadXYZ\Handlers;


use App\ReadXYZ\Data\StudentLessonsData;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\Log;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Twig\LessonTemplate;

class TimerForms extends AbstractHandler
{
    /**
     * @throws PhonicsException on ill-formed SQL
     */
    public static function handlePost(): void
    {
        self::fullLocalErrorReportingOn();
        try {
            if(isset($_POST['currentLesson'])){
                $lessonName = $_POST['currentLesson'];
            }
            else if (!Session::hasLesson()) {
                throw new PhonicsException('Cannot update user mastery without an active lesson.');
            }
            else{
                $lessonName = Session::getCurrentLessonName();
            }
            $source = $_POST['source'] ?? 'unknown';
            $seconds = intval($_POST['seconds'] ?? '0');
            $timeStamp = intval($_POST['timestamp'] ?? '0');
            $tab = ('fluency' == $source) ? 'fluency' : 'test';

            $lessonTemplate = new LessonTemplate($lessonName, $tab);
            $studentLessonData = new StudentLessonsData();
            if (('fluency' == $source) || ('test' == $source)) {
                $studentLessonData->updateTimedTest($source, $seconds, $timeStamp);
                $lessonTemplate->display($source);
            } elseif ('testMastery' == $source) {
                // masteryType is 'Advancing' or 'Mastered'
                $masteryType = $_POST['masteryType'];
                $studentLessonData->updateMastery($masteryType);
                $lessonTemplate->display('test');

            } else {
                $message = '$_POST["source"] must be fluency, test or testMastery.';
                Log::error($message);
                echo Util::redBox($message);
            }
        } finally {
            self::fullLocalErrorReportingOff();
        }

    }
}
