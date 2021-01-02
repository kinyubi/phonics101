<?php


namespace App\ReadXYZ\Handlers;


use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Twig\LessonListTemplate;

class StudentSelector extends AbstractHandler
{
    /**
     * @param $studentCode
     * @throws PhonicsException on ill-formed SQL
     */
    public static function route($studentCode): void
    {
        self::fullLocalErrorReportingOn();
        try {
            if (empty($studentCode)) {
                throw new PhonicsException('You should not arrive here without student id set.');
            }
            Session::updateStudent($studentCode);

            $lessonList = new LessonListTemplate();
            $lessonList->display();
        } finally {
            self::fullLocalErrorReportingOff();
        }
    }
}
