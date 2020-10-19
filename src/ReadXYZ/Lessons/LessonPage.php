<?php


namespace ReadXYZ\Lessons;


use ReadXYZ\Helpers\ScreenCookie;
use ReadXYZ\Twig\Page;
use ReadXYZ\Twig\TwigFactory;

class LessonPage extends Page
{
    private string $studentName;
    public function __construct(string $lessonName, string $studentName)
    {
        parent::__construct($lessonName);
        $this->studentName = $studentName;
    }


    public function lessonRender(string $initialTabName = ''): string
    {
        $this->arguments['pageTitle'] = $this->pageTitle;
        $this->arguments['isSmallScreen'] = ScreenCookie::isScreenSizeSmall();
        if ($this->errors) {$this->arguments['errorMessage'] = $this->errors;}
        if ($this->navBar) {$this->arguments['menu'] = $this->navBar;}
        $this->arguments['studentName'] = $this->studentName;
        $this->arguments['tabs'] = $this->tabs;
        if ($initialTabName) {$this->arguments['initialTabName'] = $initialTabName;}
        echo TwigFactory::getInstance()->renderTemplate('lesson', $this->arguments);
    }
}
