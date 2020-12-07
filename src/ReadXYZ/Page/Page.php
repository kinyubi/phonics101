<?php

namespace App\ReadXYZ\Page;

use App\ReadXYZ\Helpers\ScreenCookie;
use App\ReadXYZ\POPO\Game;
use App\ReadXYZ\Models\KeyValuePair;
use App\ReadXYZ\Twig\TwigFactory;

class Page
{
    public string   $pageTitle;        // sets the name of the browser tab
    public array    $navBar;           // key/value pairs of title => link
    public string   $errors;           // error HTML added by addError()
    public array    $arguments;        // arguments added by addArguments()

    /** @var Game[] */
    public array $games;

    public function __construct(string $title)
    {
        $this->pageTitle = $title;
        $this->errors = '';
        $this->navBar = [];
        $this->arguments = [];
    }

    /**
     * Add argument(s) that will be passed to 'default_body.html.twig' template or its derived twigs.
     *
     * @param array $args
     */
    public function addArguments(array $args): void
    {
        if (isAssociative($args)) {
            foreach ($args as $name => $value) {
                $this->arguments[$name] = $value;
            }
        }
    }


    public function addError(string $message): void
    {
        $this->errors .= $message . '<br/>';
    }

    /**
     * Add a link name and a target url into the 'navBar' argument
     * @param KeyValuePair $pair
     */
    public function addNavPair(KeyValuePair $pair)
    {
        $pair->addToArray($this->navBar);
    }

    public function setPageTitle(string $title): void
    {
        $this->pageTitle = $title;
    }

    public function display(string $template): void
    {
        if (!empty($this->pageTitle))   $this->arguments['pageTitle']   = $this->pageTitle;
        if (!empty($this->errors))      $this->arguments['errors']      = $this->errors;
        if (!empty($this->navBar))      $this->arguments['navBar']      = $this->navBar;

        $this->arguments['isSmallScreen'] = ScreenCookie::isScreenSizeSmall();
        echo TwigFactory::getInstance()->renderTemplate($template, $this->arguments);
    }

}
