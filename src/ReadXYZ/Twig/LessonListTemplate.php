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


use App\ReadXYZ\Twig\BaseTemplate;

class LessonListTemplate extends BaseTemplate
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

        $mostRecentLessonCode = Session::getCurrentLessonCode();
        $mostRecentLessonName = Session::getCurrentLessonName();
        $mostRecentLessonUnit = 0;
        $willGetMostRecentLessonUnit = false;
        $hasFoundMostRecentLessonUnit = false;

        if(isset($mostRecentLessonCode)){
            $willGetMostRecentLessonUnit = true;
            $mostRecentLessonUnit = 1;
        }

        $lessonsList = $lessons->getAccordionWithMastery();

        $lessonMasteries = array();
        $lessonMasteriesCount = array();

        $completedCount = 0;
        $totalCount = 0;

        foreach($lessonsList as $lessonArray){
            $isCompleted = true;
            $isMastered = true;

            $lessonCompletedCount = 0;
            $lessonTotalCount = 0;

            foreach($lessonArray["lessons"] as $exercise){
                if($willGetMostRecentLessonUnit){
                    if($exercise["lessonName"] == $mostRecentLessonName)
                        $hasFoundMostRecentLessonUnit = true;
                }
                $totalCount++;
                $lessonTotalCount++;
                if($exercise["mastery"] != 0){
                    $completedCount++;
                    $lessonCompletedCount++;
                }
                if($exercise["mastery"] == 0){
                    $isCompleted = false;
                    $isMastered = false;
                }
                else if($exercise["mastery"] == 1){
                    $isMastered = false;
                }
            }


            $mastery = 0;
            if($isMastered)
                $mastery = 2;
            else if($isCompleted)
                $mastery = 1;

            $lessonsTotalProgress = round((($lessonCompletedCount / $lessonTotalCount) * 100), 1);
            array_push($lessonMasteriesCount, $lessonsTotalProgress);

            array_push($lessonMasteries, $mastery);

            if($willGetMostRecentLessonUnit && !$hasFoundMostRecentLessonUnit)
                $mostRecentLessonUnit++;
        }


        $lessonsProgress = round((($completedCount / $totalCount) * 100), 1);
        $args = [
            'accordion'         => $lessonsList,
            'studentName'       => $studentName,
            'mostRecentLessonUnit'  => $mostRecentLessonUnit,
            'mostRecentLessonCode'  => Session::getCurrentLessonCode(),
            'mostRecentLessonName'  => Session::getCurrentLessonName(),
            'keychainAnimals'   => KeyChainJson::getInstance()->getAll(), //sync with twig
            'this_crumb'        => $currentCrumb,
            'zooUrl'            => $zooTemplate->getZooUrl(),
            'LessonsJson'       => $lessons,
            'lessonsCompleted'  => $completedCount,
            'lessonsTotal'      => $totalCount,
            'lessonsProgress'   => $lessonsProgress,
            'lessonMasteries'   => $lessonMasteries,
            'lessonMasteriesCount' => $lessonMasteriesCount,
            'animals'           => $zooAnimals->getStudentAnimalSet($student->studentCode),
            'animalIndex'       => $zooAnimals->getIndex($student->studentCode),
            'isLocal' => Util::isLocal(),
            'studentList'       => parent::getStudentList()
        ];

        if (!empty($crumbs)) {
            $args['previous_crumbs'] = $crumbs;
        }

        $page = new Page($studentName);
        $page->addArguments($args);

        $page->display('lesson_list');
    }
}
