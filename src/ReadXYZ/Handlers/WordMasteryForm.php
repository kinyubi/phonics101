<?php


namespace App\ReadXYZ\Handlers;


use App\ReadXYZ\Data\WordMasteryData;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Models\Log;
use App\ReadXYZ\Models\Session;

class WordMasteryForm extends AbstractHandler
{

    /**
     * for processing mastery tab submit
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
            $studentCode = Session::getStudentCode();
            $presentedWordList = $_POST['wordlist'];
            $masteredWords = $_POST['word1'] ?? [];
            $wordMasteryData = new WordMasteryData();
            $result = $wordMasteryData->update($studentCode, $presentedWordList, $masteredWords);

            if ($result->wasSuccessful()) {
                self::sendResponse(200, 'Update successful');
            } else {
                $msg = $result->getErrorMessage();
                Log::error($msg);
                self::sendResponse(500, $msg);
            }
        } finally {
            self::fullLocalErrorReportingOff();
        }
    }
}
