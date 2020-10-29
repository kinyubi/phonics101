<?php


namespace App\ReadXYZ\Twig;


use App\ReadXYZ\Database\StudentTable;
use App\ReadXYZ\Helpers\Util;

class StudentListTemplate
{

    private Page $page;

    public function __construct()
    {
    }

    public function display(): void
    {
        $allStudents = StudentTable::getInstance()->GetAllStudents();
        $page = new Page('Select a student');
        $studentLinks = [];
        foreach ($allStudents as $student) {
            $studentLinks[] = [
                'url'   => Util::buildActionsLink('processStudentSelection', ['P1' => $student['studentID']]),
                'title' => ucfirst($student['enrollForm']['StudentName'])
            ];
        }
        $args = ['page' => $page, 'studentLinks' => $studentLinks];
        echo TwigFactory::getInstance()->renderTemplate('students.html.twig', $args);
    }

}
