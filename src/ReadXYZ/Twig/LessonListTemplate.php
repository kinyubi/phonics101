<?php


namespace App\ReadXYZ\Twig;


use App\ReadXYZ\Data\GroupData;
use App\ReadXYZ\Enum\TrainerType;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Lessons\LearningCurve;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Lessons\Lessons;
use App\ReadXYZ\Models\BreadCrumbs;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Page\Page;

class LessonListTemplate
{

    public function __construct()
    {
        LearningCurve::cleanUpOldGraphics();
    }

    /**
     * @throws PhonicsException
     */
    public function display()
    {
        $lessons = Lessons::getInstance();
        $student = Session::getStudentObject();
        $studentName = $student->studentName;
        $currentCrumb = 'lessons';

        // make breadcrumbs
        $crumbs = (new BreadCrumbs())->getPrevious($currentCrumb);


        $args = [
            'accordion'         => $lessons->getAccordionList(),
            'studentName'       => $studentName,
            'mostRecentLesson'  => $student->lessonCode,
            'groupInfo'         => (new GroupData())->getGroupExtendedAssocArray(),
            'this_crumb'        => $currentCrumb,
            'lessonDisplayAs'   => $lessons->getLessonDisplayAs(), // [ [lessonCode => lessonDisplayAs] ]
            'isLocal' => Util::isLocal()
        ];

        if (!empty($crumbs)) {
            $args['previous_crumbs'] = $crumbs;
        }

        $page = new Page($studentName);
        $page->addArguments($args);

        $page->display('lesson_list');
    }


}
