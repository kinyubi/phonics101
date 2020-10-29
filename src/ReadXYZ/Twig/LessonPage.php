<?php


namespace App\ReadXYZ\Twig;

use App\ReadXYZ\Helpers\ScreenCookie;


class LessonPage extends Page
{
    private string $studentName;

    public function __construct(string $lessonName, string $studentName)
    {
        parent::__construct($lessonName);
        $this->studentName = $studentName;
    }

}
