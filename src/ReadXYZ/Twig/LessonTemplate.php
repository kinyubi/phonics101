<?php


namespace App\ReadXYZ\Twig;


use App\ReadXYZ\Database\StudentTable;
use App\ReadXYZ\Helpers\ScreenCookie;
use App\ReadXYZ\Display\LearningCurve;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Lessons\GameTypes;
use App\ReadXYZ\Lessons\Lesson;
use App\ReadXYZ\Lessons\Lessons;
use App\ReadXYZ\Lessons\SideNote;
use App\ReadXYZ\Lessons\TabTypes;
use App\ReadXYZ\Lessons\Warmups;
use App\ReadXYZ\Models\Cookie;
use App\ReadXYZ\Models\Student;
use RuntimeException;

class LessonTemplate
{
    private static LessonTemplate $instance;


    private ?Lesson $lesson;
    private LessonPage $page;
    private TwigFactory $factory;
    private string $lessonName;
    private string $initialTabName;

    public function __construct(string $lessonName = '', string $initialTabName = '')
    {
        $cookie = new Cookie();

        if (!$cookie->tryContinueSession()) {
            throw new RuntimeException("Session not found.\n" . $cookie->getCookieString());
        }
        $this->factory = TwigFactory::getInstance();
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

    public function display(): void
    {
        LearningCurve::cleanUpOldGraphics();
        $args = [];
        $args['students'] = StudentTable::getInstance()->GetAllStudents();
        $args['warmups'] = Warmups::getInstance()->getLessonWarmup($this->lessonName);
        $args['page'] = $this->page;
        $args['lesson'] = $this->lesson;
        $args['tabTypes'] = TabTypes::getInstance();
        $args['gameTypes'] = GameTypes::getInstance();
        $args['cookie'] = new Cookie();
        $args['isSmallScreen'] = ScreenCookie::isScreenSizeSmall();
        $args['sideNote'] = SideNote::getInstance();
        $args['masteredWords'] = Student::getInstance()->getMasteredWords();

        echo TwigFactory::getInstance()->renderTemplate('lesson', $args);
    }

}
