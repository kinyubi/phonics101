<?php


namespace App\ReadXYZ\Twig;


class LoginTemplate
{

    private Page $page;

    public function __construct(string $errorMessage='')
    {
        $this->page = new Page('ReadXYZ Login');
        if ($errorMessage) {
            $this->page->addError($errorMessage);
        }
    }

    public function display(string $errorMessage=''): void
    {
        if ($errorMessage) {
            $this->page->addError($errorMessage);
        }
        $args = ['page' => $this->page];
        echo TwigFactory::getInstance()->renderTemplate('login.html.twig', $args);
    }

}
