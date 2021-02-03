<?php

namespace App\ReadXYZ\Page;

use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\ScreenCookie;
use App\ReadXYZ\Models\KeyValuePair;
use App\ReadXYZ\Twig\TwigFactory;

class Page
{
    const PAGE_TITLE      = 'pageTitle';    //text for the browser tab
    const ERRORS          = 'errors';       //if errors were added, we'll get a modal along with the page
    const NAVBAR          = 'navBar';
    const IS_SMALL_SCREEN = 'isSmallScreen';
    const PREV_CRUMBS     = 'previous_crumbs';
    const THIS_CRUMB      = 'this_crumb';

    /**
     * @var string the name of the browser tab
     */
    private string   $pageTitle;
    /**
     * @var array key/value pairs of title => link
     */
    private array    $navBar = [];
    /**
     * @var string // error HTML added by addError()
     */
    private string   $errors = '';
    /**
     * @var array arguments added by addArguments()
     */
    private array    $arguments = [];
    /**
     * @var array crumbs leading up to this page
     */
    private array    $prevCrumbs = [];
    /**
     * @var string the current page's crumb name
     */
    private string   $crumb;

    public function __construct(string $title, string $crumbText = '')
    {
        $this->pageTitle = $title;
        $this->crumb     = $crumbText;
    }

// ======================== PUBLIC METHODS =====================

    /**
     * Add a template argument
     * @param string $key
     * @param mixed $value
     */
    public function addArgument(string $key, $value): void
    {
        if ($value) {
            $this->arguments[$key] = $value;
        }
    }

    /**
     * Add argument(s) that will be passed to 'default_body.html.twig' template or its derived twigs.
     *
     * @param array $args
     * @throws PhonicsException
     */
    public function addArguments(array $args): void
    {
        if (isAssociative($args)) {
            foreach ($args as $name => $value) {
                $this->addArgument($name, $value);
            }
        } else {
            throw new PhonicsException("Arguments must be an associative array.");
        }
    }

    public function addBreadcrumb(string $text, string $link = ''): void
    {
        $this->prevCrumbs[] = [$text => $link];
    }

    /**
     * @param array $crumbs
     * @throws PhonicsException
     */
    public function addBreadcrumbs(array $crumbs): void
    {
        if (isAssociative($crumbs)) {
            foreach ($crumbs as $name => $value) {
                $this->addBreadcrumb($name, $value);
            }
        } else {
            throw new PhonicsException("Arguments must be an associative array.");
        }
    }

    public function addError(string $message): void
    {
        if ($message) {
            $this->errors .= $this->paragraph($message);
        }
    }

    /**
     * Add a link name and a target url into the 'navBar' argument
     * @param KeyValuePair $pair
     */
    public function addNavPair(KeyValuePair $pair)
    {
        $pair->addToArray($this->navBar);
    }

    /**
     * Display the page using the specified twig template (html.twig suffix optional)
     * @param string $template
     * @throws PhonicsException
     */
    public function display(string $template): void
    {
        echo $this->getHtml($template);
    }

    /**
     * return the html for the page
     * @param string $template
     * @return string generated html
     * @throws PhonicsException
     */
    public function getHtml(string $template): string
    {
        $this->addArgument(self::PAGE_TITLE, $this->pageTitle);
        $this->addArgument(self::NAVBAR, $this->navBar);
        $this->addArgument(self::ERRORS, $this->errors);
        $this->addArgument(self::IS_SMALL_SCREEN, ScreenCookie::getInstance()->isScreenSizeSmall());
        $this->addArgument(self::PREV_CRUMBS, $this->prevCrumbs);
        $this->addArgument(self::THIS_CRUMB, $this->crumb);

        return TwigFactory::getInstance()->renderTemplate($template, $this->arguments);
    }

    public function setPageCrumb(string $crumb): void
    {
    }

    public function setPageTitle(string $title): void
    {
        $this->pageTitle = $title;
    }

// ======================== PRIVATE METHODS =====================
    private function paragraph(string $text): string
    {
        return '<p>' . $text . '</p>';
    }

}
