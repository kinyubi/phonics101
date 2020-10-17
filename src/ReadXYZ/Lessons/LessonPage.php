<?php


namespace ReadXYZ\Lessons;


use ReadXYZ\Twig\Page;

class LessonPage extends Page
{
    private string $studentName;
    public function __construct(string $lessonName, string $studentName)
    {
        parent::__construct($lessonName);
        $this->studentName = $studentName;
    }

    /**
     * Renders a lesson's tabs
     * @param string $initialTabName
     * @return string
     */
    public function lessonRender(string $initialTabName = ''): string
    {
        $pageArgs = [];
        $pageArgs['studentName'] = $this->studentName;
        $pageArgs['pageTitle'] = $this->pageTitle;
        $pageArgs['bodyBackgroundClass'] = 'bg-readlite';
        $pageArgs['tabs'] = $this->tabs;
        if ($initialTabName) {
            $pageArgs['initialTabName'] = $initialTabName;
        }
        return $this->defaultBodyRender($pageArgs);
    }
}
