<?php


namespace App\ReadXYZ\Twig;


use App\ReadXYZ\Display\LearningCurve;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Lessons\Lessons;
use App\ReadXYZ\Models\Session;

class LessonListTemplate
{

    public function __construct()
    {

    }

    public function display()
    {
        LearningCurve::cleanUpOldGraphics();
        $session = new Session();

        $lessons = Lessons::getInstance();

        $accordion = $lessons->getAccordionList();
        $displayAs = [];


        $args = [
            'accordion' => $accordion,
            'studentName' => $session->getStudentName(),
            'isLocal' => Util::isLocal(),
            'displayAs' => $displayAs,
            'mostRecentLesson' => $session->getCurrentLessonName()
        ];

        $page = new Page($session->getStudentName());
        $page->addArguments($args);

        $page->display('lesson_list');
    }

}
