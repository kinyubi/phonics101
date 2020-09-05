<?php


namespace ReadXYZ\Models;


use ReadXYZ\Database\StudentTable;
use ReadXYZ\Helpers\Util;
use ReadXYZ\Twig\Twigs;

class RouteMe
{

public static function generatePassword() {
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $strength = rand(10, 20);
    $lastPos = strlen($chars) - 1;
    $randomWord = '';
    for($i = 0; $i < $strength; $i++) {
        $randomLetter = $chars[mt_rand(0, $lastPos)];
        $randomWord .= $randomLetter;
    }

    return $randomWord;
}

    public static function autoLogin()
    {
        $twigs = Twigs::getInstance();
        Util::sessionContinue();
        $identity = Identity::getInstance();
        $studentTable = StudentTable::getInstance();
        $allStudents = $studentTable->GetAllStudents();
        if ($identity->hasMultipleStudents()) {
            echo $twigs->renderStudentList($allStudents);
        } else {
            $studentId = $allStudents[0]['studentID'];
            $identity->setStudent($studentId);
            $identity->savePersistentState();
            $cookie = Cookie::getInstance();
            $args = [];
            $args['mostRecentLesson'] = $cookie->getCurrentLesson();
            $args["mostRecentTab"] = $cookie->getCurrentTab();
            echo $twigs->renderLessonList($args);
        }
    }
}
