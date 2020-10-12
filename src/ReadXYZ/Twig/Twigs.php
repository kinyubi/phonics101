<?php

namespace ReadXYZ\Twig;

use App\ReadXYZ\Helpers\ScreenCookie;
use ReadXYZ\Display\LearningCurve;
use ReadXYZ\Helpers\Util;
use ReadXYZ\Lessons\Lesson;
use ReadXYZ\Lessons\LessonPage;
use ReadXYZ\Lessons\Lessons;
use ReadXYZ\Lessons\SideNote;
use ReadXYZ\Lessons\TabTypes;
use ReadXYZ\Lessons\Warmups;
use ReadXYZ\Models\Cookie;
use ReadXYZ\Models\Identity;
use ReadXYZ\Models\Student;
use ReadXYZ\POPO\Warmup;
use RuntimeException;

/**
 * Class Twigs a collection of methods to render our site's pages.
 * Tab types: stretch (Intro), words (Write), practice, spinner(Spell), mastery, fluency, test.
 *
 * @package ReadXYZ\Twig
 */
class Twigs
{
    private static Twigs $instance;

    private TwigFactory $factory;
    private ?Lesson $lesson;
    private Identity $identity;
    private string $lessonTemplate = 'lesson';

    private function __construct()
    {
        Util::sessionContinue();
        $this->identity = Identity::getInstance();
        $this->factory = TwigFactory::getInstance();
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Twigs();
        }
        // These go here because student and current lesson can change

        return self::$instance;
    }

    public static function fixTabName($tabName): string
    {
        switch (strtolower($tabName)) {
            case 'stretch':
            case 'intro':
                return 'intro';
            case 'words':
            case 'write':
                return 'write';
            case 'practice':
                return 'practice';
            case 'spell':
            case 'spinner':
                return 'spell';
            case 'mastery':
                return 'mastery';
            case 'fluency':
                return 'fluency';
            case 'test':
                return 'test';
            default:
                error_log("$tabName is not a recognized tab name.");

                return 'unknown';
        }
    }

    private function baseRender(string $id, array $argsIn, Page $page, string $blockName = '', $templateName = ''): void
    {
        $tabTypeId = $id;
        $tabInfo = TabTypes::getInstance()->getTabInfo($tabTypeId);
        $args = $argsIn;
        $args['sidebarHtml'] = $this->renderSideBar($tabTypeId);
        $args['games'] = $this->lesson->getGamesForTab($tabTypeId);
        $args['tabInfo'] = $tabInfo;
        $args['isSmallScreen'] = ScreenCookie::isScreenSizeSmall();
        if(empty($blockName)) { // by convention block name is capitalized id + 'Tab'
            $blockName = ucfirst($id) . 'Tab';
        }
        if (empty($templateName)) { // by convention, template name is same as $id
            $templateName = $id;
        }
        $html = $this->factory->renderBlock($templateName, $blockName, $args);
        $page->addTab($tabInfo, $html);
    }

    private function renderWarmupTab(Page $page, Warmup $warmup): void
    {
        $id = 'warmup';
        $args = ['wordList' => $this->lesson->getWordList(), 'warmups' => $warmup];
        $this->baseRender($id, $args,  $page);
    }

    private function renderIntroTab(Page $page): void
    {
        $args = ['stretchList' => $this->lesson->getStretchList()];
        $this->baseRender('intro', $args, $page);
    }

    private function renderWriteTab(Page $page): void
    {
        $id = 'write';
        $args = [];
        $args['wordList'] = $this->lesson->getWordLists($id, Cookie::getInstance()->getListIndex($id));
        $args['lessonName'] = $this->lesson->getLessonName();
        $cookie = $_COOKIE['readxyz_sound_box'] ?? '3blue';
        $cookieCount = intval(substr($cookie,0, 1));
        $cookieColor = substr($cookie, 1);
        $args['color'] = $cookieColor;
        $args['count'] = $cookieCount;
        $this->baseRender($id, $args,  $page);
    }

    private function renderPracticeTab(Page $page): void
    {
        $id = 'practice';
        $args = ['wordList' => $this->lesson->getWordLists($id, Cookie::getInstance()->getListIndex($id))];
        $this->baseRender($id, $args, $page);
    }

    private function renderSpellTab(Page $page): void
    {
        $args = ['spinner' => $this->lesson->getSpinner()];
        $this->baseRender('spell', $args, $page);
    }

    private function renderMasteryTab(Page $page): void
    {
        $id = 'mastery';
        $args = ['wordList' => $this->lesson->getWordList()];
        $this->baseRender($id, $args,  $page);
    }

    private function renderFluencyTab(Page $page): void
    {
        $args = ['fluencySentences' => $this->lesson->getFluencySentences()];
        $this->baseRender('fluency', $args, $page);
    }

    private function renderTestTab(Page $page): void
    {
        $id = 'test';
        $args = ['wordList' => $this->lesson->getWordLists($id, Cookie::getInstance()->getListIndex($id))];
        $this->baseRender($id, $args,  $page);
    }

    /**
     * Renders the sidebar for each of the lesson tabs.
     *
     * @param string $tabTypeId
     *
     * @return string
     */
    private function renderSideBar(string $tabTypeId)
    {
        $tabInfo = TabTypes::getInstance()->getTabInfo($tabTypeId);
        if (null == $tabInfo) {
            throw new RuntimeException("tabInfo should never be null for $tabTypeId.");
        }
        $side = SideNote::getInstance();
        $args = [];

        $groupName = $this->lesson->getGroupId();
        $args['tabInfo'] = $tabInfo;
        $args['games'] = $this->lesson->getGamesForTab($tabTypeId);
        $args['isSmallScreen'] = ScreenCookie::isScreenSizeSmall();
        if ('intro' == $tabTypeId) {
            $args['isBdpLesson'] = Util::contains($this->lesson->getLessonName(), 'b-d-p');
            $args['pronounceImage'] = $this->lesson->getPronounceImage();
            $args['pronounceImageThumb'] = $this->lesson->getPronounceImageThumb();
        }

        $info = $this->lesson->getNote($tabInfo->tabTypeId);
        if (!$info) {
            $info = SideNote::getInstance()->getNote($groupName, $tabTypeId);
        }
        $args['information'] = $info;
        switch ($tabTypeId) {
            case 'fluency':
                $timerHtml = $this->factory->renderBlock('timers2', 'fluencyTimer');
                $curveHtml = $side->getLearningCurveHTML();
                $args['timerHtml'] = $timerHtml . $curveHtml;
                break;
            case 'practice':
                $timerHtml = $this->factory->renderBlock('timers2', 'practiceTimer');
                $args['timerHtml'] = $timerHtml;
                break;
            case 'test':
                $timerHtml = $this->factory->renderBlock('timers2', 'testTimer');
                $curveHtml = $side->getTestCurveHTML();
                $args['timerHtml'] = $timerHtml . $curveHtml;
                break;
            default:
                break;
        }
        $html = $this->factory->renderBlock('sidebar', 'SideBar', $args);
        if ('mastery' == $tabTypeId) {
            $html .= $this->factory->renderBlock('timers2', 'MasterySaveProgressButton');
        } elseif ('test' == $tabTypeId) {
            $html .= $this->factory->renderBlock('timers2', 'testButtons');
        }
        return $html;
    }

    /**
     * Renders the lesson list page. Each lesson is anchored to the specified action file with the parameter
     * P1 being the lesson name. If no action is specified then we use the action handler Lessons::renderLesson.
     *
     * @param array $argsIn any arguments you want added to the template to process
     *
     * @return string HTML for the lesson list page being rendered
     */
    public function renderLessonList(array $argsIn = []): string
    {
        LearningCurve::cleanUpOldGraphics();
        if (!Cookie::getInstance()->tryContinueSession()) {
            return $this->login('Login has expired (2).');
        }
        $lessons = Lessons::getInstance();
        $studentName = Student::getInstance()->getCapitalizedStudentName();
        $accordion = $lessons->getAccordionList();
        $displayAs = [];
        foreach ($accordion as $group => $lessons) {
            $displayAs[$group] = Util::addSoundClass($group);
            foreach($lessons as $lessonName => $masteryLevel) {
                $displayAs[$lessonName] = Util::addSoundClass($lessonName);
            }
        }
        $args = [
            'accordion' => $accordion,
            'studentName' => $studentName,
            'isLocal' => Util::isLocal(),
            'displayAs' => $displayAs
        ];
        if (isAssociative($argsIn)) {
            foreach ($argsIn as $key => $value) {
                addAssociative($args, $key, $value);
            }
        }
        $page = new Page($studentName);
        $page->addArguments($argsIn);

        return $page->simpleRender('lesson_list', 'body', $args);
    }

    /**
     * renders the 'Select Student' screen.
     *
     * @param array $allStudents a student list obtained from StudentTable::getInstance
     *
     * @return string the HTML to display the 'Select Student' screen
     */
    public function renderStudentList(array $allStudents): string
    {
        $page = new Page('Select a student');
        $studentLinks = [];
        foreach ($allStudents as $student) {
            $studentLinks[] = [
                'url' => Util::buildActionsLink('processStudentSelection', ['P1' => $student['studentID']]),
                'title' => ucfirst($student['enrollForm']['StudentName'])
            ];
        }

        return $page->simpleRender('login', 'selectStudent', ['studentLinks' => $studentLinks]);
    }

    /**
     * renders the specified lesson.
     *
     * @param string $lessonName          the lesson we want rendered
     * @param string $initialTabName      The active tab name if other than the first tab
     * @param bool   $useNextLessonButton Whether or not to include a 'Next Lesson' nav item
     *
     * @return string
     */
    public function renderLesson(string $lessonName = '', string $initialTabName = '', bool $useNextLessonButton = false): string
    {
        $cookie = Cookie::getInstance();
        LearningCurve::cleanUpOldGraphics();
        if (!$cookie->tryContinueSession()) {
            return $this->login('Login has expired (3).');
        }
        $cookie->setCurrentLesson($lessonName);
        $lessons = Lessons::getInstance();

        if (empty($lessonName)) {
            $lessonName = $lessons->getCurrentLessonName();
        }
        $student = Student::getInstance();
        if (null === $student) {
            throw new RuntimeException('Student should never be null here.');
        }
        $studentName = $student->getCapitalizedStudentName();

        if (not($lessons->lessonExists($lessonName))) {
            return Util::redBox("A lesson named $lessonName does not exist.");
        }
        $lessons->setCurrentLesson($lessonName);
        $this->lesson = $lessons->getCurrentLesson();
        if (null === $this->lesson) {
            throw new RuntimeException('Lesson should never be null here.');
        }
        $page = new LessonPage($lessonName, $studentName);
        if (Identity::getInstance()->hasMultipleStudents()) {
            $page->addActionsLink('Exit', 'render', ['P1' => 'studentList']);
        }

        $page->addActionsLink('Lessons', 'render', ['target' => 'LessonList']);
        $page->addActionsLink('Next', 'render', ['target' => 'NextLesson']);
        if (Util::isLocal()) {
            $page->addActionsLink('Logout', 'render', ['target' => 'login']);
        }
        $warmup = Warmups::getInstance()->getLessonWarmup($lessonName);
        if ($warmup) {
            $this->renderWarmupTab($page, $warmup);
        }
        $tabs = $this->lesson->getTabNames();
        if (in_array('stretch', $tabs) or in_array('intro', $tabs)) {
            $this->renderIntroTab($page);
        }
        if (in_array('words', $tabs) or in_array('write', $tabs)) {
            $this->renderWriteTab($page);
        }
        if (in_array('practice', $tabs)) {
            $this->renderPracticeTab($page);
        }
        if (in_array('spinner', $tabs) or in_array('spell', $tabs)) {
            $this->renderSpellTab($page);
        }
        if (in_array('mastery', $tabs)) {
            $this->renderMasteryTab($page);
        }
        if (in_array('fluency', $tabs)) {
            $this->renderFluencyTab($page);
        }
        if (in_array('test', $tabs)) {
            $this->renderTestTab($page);
        }

        return $page->lessonRender($initialTabName);
    }

    /**
     * Returns the html for the login screen. If error message is specified, it will show up in a modal.
     * @param string $errorMessage
     * @return string
     */
    public function login(string $errorMessage = ''): string
    {
        $page = new Page('ReadXYZ Login');
        if ($errorMessage) {
            $page->addError($errorMessage);
        }

        return $page->simpleRender('login', 'login');
    }
}
