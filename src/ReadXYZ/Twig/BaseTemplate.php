<?php
namespace App\ReadXYZ\Twig;

use App\ReadXYZ\Data\Views;

class BaseTemplate {
    public function getStudentList(){
        $studentList = [];
        $allStudents = Views::getInstance()->getMapOfStudentsForUser();
        foreach ($allStudents as $name => $code) {
            $studentList[] = [
                'url'   => "/handler/student/$code",
                'title' => ucfirst($name)
            ];
        }
        return $studentList;
    }
}



?>
