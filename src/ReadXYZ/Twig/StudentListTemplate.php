<?php


namespace App\ReadXYZ\Twig;


use App\ReadXYZ\Data\Views;
use App\ReadXYZ\Data\TrainersData;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Page\Page;
use App\ReadXYZ\Models\BreadCrumbs;
use App\ReadXYZ\Models\Session;

class StudentListTemplate
{

    /**
     * @throws PhonicsException on ill-formed SQL
     */
    public function display(): void
    {

        $currentCrumb = 'students';
        $crumbs = (new BreadCrumbs())->getPrevious($currentCrumb);


        $trainerCode = Session::getTrainerCode();
        $trainersData = new TrainersData();
        $trainer = $trainersData->get($trainerCode);
        $displayName = ucfirst($trainer->displayName);


        $allStudents = Views::getInstance()->getMapOfStudentsForUser();
        $page = new Page('Select a student');
        $studentLinks = [];
        foreach ($allStudents as $name => $code) {
            $studentLinks[] = [
                'url'   => "/handler/student/$code",
                'title' => ucfirst($name)
            ];
        }
        $args = [
            'page' => $page,
            'studentLinks' => $studentLinks,
            'displayName' => $displayName,
            'this_crumb' => $currentCrumb
        ];

        if (!empty($crumbs)) {
            $args['previous_crumbs'] = $crumbs;
        }

        echo TwigFactory::getInstance()->renderTemplate('students.html.twig', $args);
    }

}
