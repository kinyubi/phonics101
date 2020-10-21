<?php


namespace ReadXYZ\Twig;


use ReadXYZ\Database\StudentTable;
use ReadXYZ\Helpers\Util;

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
