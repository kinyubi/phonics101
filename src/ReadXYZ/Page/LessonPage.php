<?php


namespace App\ReadXYZ\Page;

use App\ReadXYZ\Data\LessonsData;
use App\ReadXYZ\Helpers\ScreenCookie;
use App\ReadXYZ\Models\Session;


class LessonPage extends Page
{
    private string $studentName;

    public function __construct(string $lessonName, string $studentName)
    {
        $session = new Session();
        $title = (new LessonsData())->getLessonDisplayAs($session->getCurrentLessonCode());
        parent::__construct($title);
        $this->studentName = $session->getStudentName();
    }

    public function displayLesson(): void
    {
        parent::display('lesson');
    }

}
