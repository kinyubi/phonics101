<?php


namespace App\ReadXYZ\Twig;


use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Data\WordMasteryData;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\ScreenCookie;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Lessons\GameTypes;
use App\ReadXYZ\Lessons\LearningCurve;
use App\ReadXYZ\Lessons\Lessons;
use App\ReadXYZ\Lessons\SideNote;
use App\ReadXYZ\Lessons\TabTypes;
use App\ReadXYZ\Lessons\Warmups;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Page\LessonPage;
use App\ReadXYZ\POPO\Lesson;

class LessonTemplate
{
    private Lessons    $lessonFactory;
    private ?Lesson    $lesson;
    private LessonPage $page;
    private string     $lessonName;
    private string     $initialTabName;
    private string     $trainerCode;

    /**
     * LessonTemplate constructor.
     * @param string $lessonName
     * @param string $initialTabName
     * @throws PhonicsException
     */
    public function __construct(string $lessonName = '', string $initialTabName = '')
    {
        $session              = new Session();
        $this->lessonFactory  = Lessons::getInstance();
        $this->lessonName     = $lessonName;
        $this->initialTabName = $initialTabName;
        $this->trainerCode    = $session->getTrainerCode();
        $session->updateLesson($lessonName);
        $lessons = Lessons::getInstance();

        $studentName = $session->getStudentName();

        if (not($lessons->lessonExists($lessonName))) {
            return Util::redBox("A lesson named $lessonName does not exist.");
        }
        $this->lesson = $this->lessonFactory->getCurrentLesson();
        if (null === $this->lesson) {
            throw new PhonicsException('Lesson should never be null here.');
        }
        $this->page = new LessonPage($lessonName, $studentName);
    }

// ======================== PUBLIC METHODS =====================
    /**
     * @throws PhonicsException
     */
    public function display(): void
    {
        LearningCurve::cleanUpOldGraphics();
        $sideNote              = SideNote::getInstance();
        $args                  = [];
        $args['students']      = (new StudentsData())->getStudentNamesForUser($this->trainerCode);
        $args['warmups']       = Warmups::getInstance()->getLessonWarmup($this->lessonName);
        $args['page']          = $this->page;
        $args['lesson']        = $this->lesson;
        $args['tabTypes']      = TabTypes::getInstance();
        $args['gameTypes']     = GameTypes::getInstance();
        $args['isSmallScreen'] = ScreenCookie::isScreenSizeSmall();
        $args['sideNote']      = SideNote::getInstance();
        $args['testCurve']     = $sideNote->getTestCurveHTML();
        $args['learningCurve'] = $sideNote->getLearningCurveHTML();
        $args['masteredWords'] = (new WordMasteryData())->getMasteredWords();
        $this->page->addArguments($args);
        $this->page->displayLesson();
    }

}
