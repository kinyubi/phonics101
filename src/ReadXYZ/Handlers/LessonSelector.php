<?php


namespace App\ReadXYZ\Handlers;


use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Twig\LessonTemplate;

class LessonSelector extends AbstractHandler
{
    /**
     * Lesson list page (lesson_list.html.twig displays links for each lesson. This routes the lesson selection
     * @param array $postParameters
     * @param array $routeParts
     * @throws PhonicsException on ill-formed SQL
     */
    public static function route(array $postParameters, array $routeParts): void
    {
        self::fullLocalErrorReportingOn();
        try {
            $lessonName     = $routeParts[0] ?? $postParameters['lessonName'] ?? Session::getCurrentLessonName() ?? '';
            $initialTabName = $routeParts[1] ?? $postParameters['initialTabName'] ?? '';
            Session::updateLesson($lessonName);
            (new LessonTemplate($lessonName, $initialTabName))->display();
        } finally {
            self::fullLocalErrorReportingOff();
        }
    }
}
