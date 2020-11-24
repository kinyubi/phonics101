<?php


namespace App\ReadXYZ\Twig;


use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Helpers\Util;

class StudentListTemplate
{

    public function display(): void
    {
        $allStudents = (new StudentsData())->getStudentsForUser();
        $page = new Page('Select a student');
        $studentLinks = [];
        foreach ($allStudents as $student) {
            $studentLinks[] = [
                'url'   => Util::buildActionsLink('processStudentSelection', ['P1' => $student['studentCode']]),
                'title' => ucfirst($student['studentName'])
            ];
        }
        $args = ['page' => $page, 'studentLinks' => $studentLinks];
        echo TwigFactory::getInstance()->renderTemplate('students.html.twig', $args);
    }

}
