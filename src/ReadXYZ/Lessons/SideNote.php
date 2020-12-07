<?php

namespace App\ReadXYZ\Lessons;

use App\ReadXYZ\Data\GroupData;
use App\ReadXYZ\Data\StudentLessonsData;
use App\ReadXYZ\Enum\Regex;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Lessons\LearningCurve;
use App\ReadXYZ\Enum\TimerType;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\Log;
use App\ReadXYZ\Twig\TwigFactory;
use Exception;
use Throwable;

/**
 * @copyright (c) 2020 ReadXYZ, LLC
 * @author Carl Baker (carlbaker@gmail.com)
 * @license GPL3+
 */

/**
 * Class SideNote
 * a singleton class that applies side notes from side_notes.json.
 */
class SideNote
{
    private static SideNote $instance;           // Hold a singleton instance of the class
    private $data;

    /**
     * SideNote constructor.
     *
     * @throws Exception if the file side_notes.json is not found in the resources folder
     */
    private function __construct()
    {
        // If we want the structure returned as an associative array, use json_decode($JSON, true);
        $filename = Util::getReadXyzSourcePath('resources/side_notes.json');
        assert(file_exists($filename), "$filename expected to exist");
        $str = file_get_contents($filename, false, null);
        $this->data = [];
        $this->data = json_decode($str, true);
        if ( ! $this->data) {
            throw new Exception(json_last_error_msg());
        }
    }

// ======================== PUBLIC METHODS =====================
    /**
     * @return SideNote an instance of this singleton class
     */
    public static function getInstance(): SideNote
    {
        if ( ! isset(self::$instance)) {
            self::$instance = new SideNote();
        }

        return self::$instance;
    }

    /**
     * @return string
     * @throws PhonicsException
     */
    public function getLearningCurveHTML(): string
    {
        return $this->getCurveHTML(new TimerType(TimerType::FLUENCY));
    }

    /**
     * For the specified tab, returns lesson note if found or returns a group note if found
     * or returns a default note if found or returns an empty string.
     *
     * @param string $group groupCode or groupName (will convert to groupCode)
     * @param string $tabName
     * @return string return a corresponding note if found
     */
    public function getNote(string $group, string $tabName): string
    {

        try {
            if (Regex::isValidGroupCodePattern($group)) {
                $groupCode = $group;

            } else {
                $groupCode = $realGroupName = (new GroupData())->getGroupCode($group);
            }
        } catch (Throwable $ex) {
            $message = "Failure getting group code from  $group. " . $ex->getMessage();
            Log::warning($message, __METHOD__, __FILE__, __LINE__);
            return '';
        }

        try {
            $realTabName = TabTypes::getInstance()->fixTabName($tabName);
        } catch (Throwable $ex) {
            $message = "Failure getting valid tab name from $tabName " . $ex->getMessage();
            Log::warning($message, __METHOD__, __FILE__, __LINE__);
            return '';
        }
        $note = $this->getGroupNote($groupCode, $realTabName);
        if ($note) {
            return $note;
        } else {
            return $this->getDefaultTabNote($tabName) ?? '';
        }

    }

    /**
     * called by sidebar.html.twig
     * @return string
     * @throws PhonicsException
     */
    public function getTestCurveHTML(): string
    {
        return $this->getCurveHTML(new TimerType(TimerType::TEST));
    }

// ======================== PRIVATE METHODS =====================

    /**
     * @param TimerType $timerType currently supported indexes are 'learningCurve' and 'testCurve'
     *
     * @return string learning curve HTML
     * @throws PhonicsException
     */
    private function getCurveHTML(TimerType $timerType): string
    {
        $studentLessonData = new StudentLessonsData();
        $data = $studentLessonData->getTimedTest($timerType);
        if (count($data) == 0) return '';

        $learningCurve = new LearningCurve();
        $imgURL = $learningCurve->learningCurveChart($data);
        return TwigFactory::getInstance()->renderBlock('timers2', 'LearningCurve', ['imageUrl' => $imgURL]) ?? '';

    }

    /**
     * Looks for notes in 'default_tab_notes" group in side_notes.json. Called by getNote.
     *
     * @param string $tab_name
     *
     * @return mixed|string
     */
    private function getDefaultTabNote(string $tab_name)
    {
        return $this->data['default_tab_notes'][$tab_name] ?? '';
    }

    /**
     * Looks for notes in 'group_notes" group in side_notes.json. Called by getNote.
     *
     * @param string $groupCode
     * @param string $tabName
     * @return string
     */
    private function getGroupNote(string $groupCode, string $tabName): string
    {
        return $this->data['group_notes'][$groupCode][$tabName] ?? '';
    }

}
