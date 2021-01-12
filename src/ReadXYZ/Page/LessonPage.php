<?php


namespace App\ReadXYZ\Page;

use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\JSON\LessonsJson;
use App\ReadXYZ\Models\Session;


class LessonPage extends Page
{
    private string $studentName;

    /**
     * LessonPage constructor.
     * @param string $lessonName
     * @param string $studentName
     * @throws PhonicsException
     */
    public function __construct(string $lessonName, string $studentName)
    {
        $title = LessonsJson::getInstance()->getLessonName(Session::getCurrentLessonCode());
        parent::__construct($title);
        $this->studentName = Session::getStudentName();
    }

    /**
     * @throws PhonicsException
     */
    public function displayLesson(): void
    {
        parent::display('lesson');
    }

}
