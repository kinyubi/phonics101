<?php


namespace App\ReadXYZ\Twig;


use App\ReadXYZ\Data\Views;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Page\Page;

class StudentListTemplate
{

    /**
     * @throws PhonicsException on ill-formed SQL
     */
    public function display(): void
    {
        $allStudents = Views::getInstance()->getMapOfStudentsForUser();
        $page = new Page('Select a student');
        $studentLinks = [];
        foreach ($allStudents as $name => $code) {
            $studentLinks[] = [
                'url'   => "/handler/student/$code",
                'title' => ucfirst($name)
            ];
        }
        $args = ['page' => $page, 'studentLinks' => $studentLinks];
        echo TwigFactory::getInstance()->renderTemplate('students.html.twig', $args);
    }

}
