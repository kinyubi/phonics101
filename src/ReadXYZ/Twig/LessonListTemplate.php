<?php


namespace App\ReadXYZ\Twig;


use App\ReadXYZ\Data\GroupData;
use App\ReadXYZ\Lessons\LearningCurve;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Lessons\Lessons;
use App\ReadXYZ\Models\Session;

class LessonListTemplate
{

    public function __construct()
    {
        LearningCurve::cleanUpOldGraphics();
    }

    public function display()
    {
        $lessons = Lessons::getInstance();

        $session = new Session();
        $studentName = $session->getStudentName();
        $args = [
            'accordion'         => $lessons->getAccordionList(),
            'studentName'       => $studentName,
            'mostRecentLesson'  => $session->getCurrentLessonCode(),
            'groupInfo'         => (new GroupData())->getGroupExtendedAssocArray(),
            'lessonDisplayAs'   => $lessons->getLessonDisplayAs(),
            'isLocal' => Util::isLocal()
        ];

        $page = new Page($studentName);
        $page->addArguments($args);

        $page->display('lesson_list');
    }


}
