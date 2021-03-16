<?php


namespace App\ReadXYZ\Twig;


use App\ReadXYZ\Enum\GeneratedType;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\JSON\KeyChainJson;
use App\ReadXYZ\JSON\LessonsJson;
use App\ReadXYZ\JSON\ZooAnimalsAlt;
use App\ReadXYZ\Lessons\LearningCurve;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\BreadCrumbs;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Page\Page;

class LessonListTemplate
{

    public function __construct()
    {
        // LearningCurve::cleanUpOldGraphics();
    }

    /**
     * @throws PhonicsException
     */
    public function display()
    {
        $lessons = LessonsJson::getInstance();
        $student = Session::getStudentObject();
        $studentName = $student->studentName;
        $currentCrumb = 'lessons';

        // make breadcrumbs
        $crumbs = (new BreadCrumbs())->getPrevious($currentCrumb);
        $zooTemplate = new ZooTemplate($student->studentCode);
        $awardTemplate = new AwardTemplate($student->studentCode);
        $zooAnimals = ZooAnimalsAlt::getInstance();
        $args = [
            'accordion'         => $lessons->getAccordionWithMastery(),
            'studentName'       => $studentName,
            'mostRecentLessonCode'  => Session::getCurrentLessonCode(),
            'mostRecentLessonName'  => Session::getCurrentLessonName(),
            'keychainAnimals'   => KeyChainJson::getInstance()->getAll(), //sync with twig
            'this_crumb'        => $currentCrumb,
            'zooUrl'            => $zooTemplate->getZooUrl(),
            'LessonsJson'       => $lessons,
            'animals'           => $zooAnimals->getStudentAnimalSet($student->studentCode),
            'animalIndex'       => $zooAnimals->getIndex($student->studentCode),
            'awardUrl'          => $awardTemplate->getUrl(),
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
