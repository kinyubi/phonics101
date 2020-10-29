<?php


namespace App\ReadXYZ\Twig;


use App\ReadXYZ\Display\LearningCurve;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Lessons\Lessons;
use App\ReadXYZ\Models\Cookie;
use App\ReadXYZ\Models\Student;
use RuntimeException;

class LessonListTemplate
{

    public function __construct()
    {

    }

    public function display()
    {
        $cookie = new Cookie();
        LearningCurve::cleanUpOldGraphics();
        if (!$cookie->tryContinueSession()) {
            throw new RuntimeException("Session has expired.\n" . $cookie->getCookieString());
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
            'displayAs' => $displayAs,
            'mostRecentLesson' => $cookie->getCurrentLesson(),
            'mostRecentTab' => $cookie->getCurrentTab()
        ];

        $page = new Page($studentName);
        $page->addArguments($args);

        $page->display('lesson_list');
    }

}
