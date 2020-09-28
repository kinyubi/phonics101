<?php

namespace ReadXYZ\Twig;

use ReadXYZ\Display\LearningCurve;
use ReadXYZ\Helpers\Util;
use ReadXYZ\Lessons\Lesson;
use ReadXYZ\Lessons\Lessons;
use ReadXYZ\Lessons\SideNote;
use ReadXYZ\Lessons\TabTypes;
use ReadXYZ\Models\Cookie;
use ReadXYZ\Models\Identity;
use ReadXYZ\Models\Student;
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

    public function setDefaultTemplate(string $templateBaseName): void
    {
        $this->lessonTemplate = $templateBaseName;
    }


    private function renderIntroTab(Page $page): void
    {
        $tabTypeId = 'intro';
        $tabInfo = TabTypes::getInstance()->getTabInfo($tabTypeId);
        $args = [];
        $args['sidebarHtml'] = $this->renderSideBar($tabTypeId);
        $args['stretchList'] = $this->lesson->getStretchList();
        $args['games'] = $this->lesson->getGamesForTab($tabTypeId);
        $args['tabName'] = $tabTypeId;
        $html = $this->factory->renderBlock($this->lessonTemplate, 'IntroTab', $args);
        $page->addTab($tabInfo->getTabDisplayAs(), $html);
    }

    private function renderWriteTab(Page $page): void
    {
        $tabTypeId = 'write';
        $tabInfo = TabTypes::getInstance()->getTabInfo($tabTypeId);
        $args = [];
        $args['tabName'] = $tabTypeId;
        $args['sidebarHtml'] = $this->renderSideBar($tabTypeId);
        $args['wordList'] = $this->lesson->getWordLists($tabTypeId, Cookie::getInstance()->getListIndex($tabTypeId));
        $args['games'] = $this->lesson->getGamesForTab($tabTypeId);
        // add this tab's functionality
        $html = $this->factory->renderBlock($this->lessonTemplate, 'WriteTab', $args);
        $page->addTab($tabInfo->getTabDisplayAs(), $html);
    }

    private function renderPracticeTab(Page $page): void
    {
        $tabTypeId = 'practice';
        $tabInfo = TabTypes::getInstance()->getTabInfo($tabTypeId);
        $args = [];
        $args['tabName'] = $tabTypeId;
        $args['sidebarHtml'] = $this->renderSideBar($tabTypeId);
        $args['wordList'] = $this->lesson->getWordLists($tabTypeId, Cookie::getInstance()->getListIndex($tabTypeId));
        $args['games'] = $this->lesson->getGamesForTab($tabTypeId);
        // add this tab's functionality
        $html = $this->factory->renderBlock($this->lessonTemplate, 'PracticeTab', $args);
        $page->addTab($tabInfo->getTabDisplayAs(), $html);
    }

    private function renderSpellTab(Page $page): void
    {
        $tabTypeId = 'spell';
        $tabInfo = TabTypes::getInstance()->getTabInfo($tabTypeId);
        $args = [];
        $args['tabName'] = $tabTypeId;
        $args['sidebarHtml'] = $this->renderSideBar($tabTypeId);
        $args['spinner'] = $this->lesson->getSpinner();
        $args['games'] = $this->lesson->getGamesForTab($tabTypeId);
        // add this tab's functionality
        $html = $this->factory->renderBlock($this->lessonTemplate, 'SpellTab', $args);
        $page->addTab($tabInfo->getTabDisplayAs(), $html);
    }

    private function renderMasteryTab(Page $page): void
    {
        $tabTypeId = 'mastery';
        $tabInfo = TabTypes::getInstance()->getTabInfo($tabTypeId);
        $args = [];
        $args['tabName'] = $tabTypeId;
        $html = $this->renderSideBar($tabTypeId);
        $html .= $this->factory->renderBlock('lesson_blocks', 'MasterySaveProgressButton');
        $args['sidebarHtml'] = $html;
        $args['wordList'] = $this->lesson->getWordList();
        $args['games'] = $this->lesson->getGamesForTab($tabTypeId);
        // add this tab's functionality
        $html = $this->factory->renderBlock($this->lessonTemplate, 'MasteryTab', $args);
        $page->addTab($tabInfo->getTabDisplayAs(), $html);
    }

    private function renderFluencyTab(Page $page): void
    {
        $tabTypeId = 'fluency';
        $tabInfo = TabTypes::getInstance()->getTabInfo($tabTypeId);
        $args = [];
        $args['tabName'] = $tabTypeId;
        $args['sidebarHtml'] = $this->renderSideBar($tabTypeId);
        $args['fluencySentences'] = $this->lesson->getFluencySentences();
        $args['games'] = $this->lesson->getGamesForTab($tabTypeId);
        // add this tab's functionality
        $html = $this->factory->renderBlock($this->lessonTemplate, 'FluencyTab', $args);
        $page->addTab($tabInfo->getTabDisplayAs(), $html);
    }

    private function renderTestTab(Page $page): void
    {
        $tabTypeId = 'test';
        $tabInfo = TabTypes::getInstance()->getTabInfo($tabTypeId);
        $args = [];
        $args['tabName'] = $tabTypeId;
        $html = $this->renderSideBar($tabTypeId);
        $html .= $this->factory->renderBlock('timers', 'testButtons');
        $args['sidebarHtml'] = $html;
        $args['wordList'] = $this->lesson->getWordLists($tabTypeId, Cookie::getInstance()->getListIndex($tabTypeId));
        $args['games'] = $this->lesson->getGamesForTab($tabTypeId);
        // add this tab's functionality
        $html = $this->factory->renderBlock($this->lessonTemplate, 'TestTab', $args);
        $page->addTab($tabInfo->getTabDisplayAs(), $html);
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
        $side = SideNote::getInstance();
        $args = [];

        $timerArgs = ['action' => '/actions/timers.php'];
        $groupName = $this->lesson->getGroupId();
        $args['tabName'] = $tabTypeId;
        $args['games'] = $this->lesson->getGamesForTab($tabTypeId);
        if ('intro' == $tabTypeId) {
            $args['isBdpLesson'] = Util::contains($this->lesson->getLessonName(), 'b-d-p');
            $args['pronounceImage'] = $this->lesson->getPronounceImage();
            $args['pronounceImageThumb'] = $this->lesson->getPronounceImageThumb();
        }

        $args['canRefresh'] = $tabInfo->canRefresh();
        $info = $this->lesson->getNote($tabInfo->getTabDisplayAs());
        if (!$info) {
            $info = SideNote::getInstance()->getNote($groupName, $tabTypeId);
        }
        $args['information'] = $info;
        switch ($tabTypeId) {
            case 'fluency':
                $timerHtml = $this->factory->renderBlock('timers', 'fluencyTimer', $timerArgs);
                $curveHtml = $side->getLearningCurveHTML();
                $args['timerHtml'] = $timerHtml . $curveHtml;
                break;
            case 'practice':
                $timerHtml = $this->factory->renderBlock('timers', 'practiceTimer', $timerArgs);
                $args['timerHtml'] = $timerHtml;
                break;
            case 'test':
                $timerHtml = $this->factory->renderBlock('timers', 'testTimer', $timerArgs);
                $curveHtml = $side->getTestCurveHTML();
                $args['timerHtml'] = $timerHtml . $curveHtml;
                break;
            default:
                break;
        }
        return $this->factory->renderBlock($this->lessonTemplate, 'SideBar', $args);
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
        $args = [
            'accordion' => $lessons->getAccordionList(),
            'studentName' => $studentName,
            'isLocal' => Util::isLocal(),
        ];
        if (isAssociative($argsIn)) {
            foreach ($argsIn as $key => $value) {
                addAssociative($args, $key, $value);
            }
        }
        $page = new Page("ReadXYZ Lessons for $studentName");
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
        $page = new Page("$lessonName lesson for $studentName");
        if (Identity::getInstance()->hasMultipleStudents()) {
            $page->addActionsLink('Exit', 'render', ['P1' => 'studentList']);
        }
        if ($useNextLessonButton) {
            $nextLessonName = $lessons->getNextLessonName();
            $parms = [
                'P1' => $nextLessonName,
                'P2' => $initialTabName,
                'P3' => '1'
            ];
            $link = $link = '/actions/render.php?' . http_build_query($parms);
            $page->addNavLink('Next Lesson', $link);
        }
        $page->addActionsLink('Lesson Selections', 'render', ['target' => 'LessonList']);
        $page->addActionsLink('Next Lesson', 'render', ['target' => 'NextLesson']);
        if (Util::isLocal()) {
            $page->addActionsLink('Logout', 'render', ['target' => 'login']);
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

        return $page->render($initialTabName);
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
