<?php


namespace ReadXYZ\Twig;


use ReadXYZ\Helpers\ScreenCookie;
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

class LessonTemplate
{

    private ?Lesson $lesson;
    private LessonPage $page;
    private TwigFactory $factory;
    private string $lessonName;
    private string $initialTabName;

    public function __construct(string $lessonName = '', string $initialTabName = '')
    {
        $cookie = Cookie::getInstance();
        LearningCurve::cleanUpOldGraphics();
        if (!$cookie->tryContinueSession()) {
            throw new RuntimeException("Session not found.");
        }
        $this->lessonName = $lessonName;
        $this->initialTabName = $initialTabName;
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
        $this->page = new LessonPage($lessonName, $studentName);
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


    private function lessonTabRender(string $id, array $argsIn, string $blockName = '', $templateName = ''): void
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
        $this->page->addTab($tabInfo, $html);
    }

    private function renderWarmupTab(Warmup $warmup): void
    {
        $id = 'warmup';
        $args = ['wordList' => $this->lesson->getWordList(), 'warmups' => $warmup];
        $this->lessonTabRender($id, $args,  $this->page);
    }

    private function renderIntroTab(): void
    {
        $args = ['stretchList' => $this->lesson->getStretchList()];
        $this->lessonTabRender('intro', $args, $this->page);
    }

    private function renderWriteTab(): void
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
        $this->lessonTabRender($id, $args,  $this->page);
    }

    private function renderPracticeTab(): void
    {
        $id = 'practice';
        $args = ['wordList' => $this->lesson->getWordLists($id, Cookie::getInstance()->getListIndex($id))];
        $this->lessonTabRender($id, $args, $this->page);
    }

    private function renderSpellTab(): void
    {
        $args = ['spinner' => $this->lesson->getSpinner()];
        $this->lessonTabRender('spell', $args, $this->page);
    }

    private function renderMasteryTab(): void
    {
        $id = 'mastery';
        $args = ['wordList' => $this->lesson->getWordList()];
        $this->lessonTabRender($id, $args,  $this->page);
    }

    private function renderFluencyTab(): void
    {
        $args = ['fluencySentences' => $this->lesson->getFluencySentences()];
        $this->lessonTabRender('fluency', $args, $this->page);
    }

    private function renderTestTab(): void
    {
        $id = 'test';
        $args = ['wordList' => $this->lesson->getWordLists($id, Cookie::getInstance()->getListIndex($id))];
        $this->lessonTabRender($id, $args,  $this->page);
    }
    /**
     * renders the specified lesson and returns it as an HTML string.
     *
     * @return string
     */
    public function renderLessonHtml(): string
    {

        if (Identity::getInstance()->hasMultipleStudents()) {
            $this->page->addActionsLink('Exit', 'render', ['P1' => 'studentList']);
        }

        $this->page->addActionsLink('Lessons', 'render', ['target' => 'LessonList']);
        $this->page->addActionsLink('Next', 'render', ['target' => 'NextLesson']);
        if (Util::isLocal()) {
            $this->page->addActionsLink('Logout', 'render', ['target' => 'login']);
        }
        $warmup = Warmups::getInstance()->getLessonWarmup($this->lessonName);
        if ($warmup) {
            $this->renderWarmupTab($warmup);
        }
        $tabs = $this->lesson->getTabNames();
        if (in_array('stretch', $tabs) or in_array('intro', $tabs)) {
            $this->renderIntroTab();
        }
        if (in_array('words', $tabs) or in_array('write', $tabs)) {
            $this->renderWriteTab();
        }
        if (in_array('practice', $tabs)) {
            $this->renderPracticeTab();
        }
        if (in_array('spinner', $tabs) or in_array('spell', $tabs)) {
            $this->renderSpellTab();
        }
        if (in_array('mastery', $tabs)) {
            $this->renderMasteryTab();
        }
        if (in_array('fluency', $tabs)) {
            $this->renderFluencyTab();
        }
        if (in_array('test', $tabs)) {
            $this->renderTestTab();
        }

        return $this->page->lessonRender($this->initialTabName);
    }

    public function displayLesson(): void
    {
        echo $this->renderLessonHtml();
    }

}
