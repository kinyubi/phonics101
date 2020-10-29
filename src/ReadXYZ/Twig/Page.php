<?php

namespace App\ReadXYZ\Twig;

use App\ReadXYZ\Helpers\ScreenCookie;
use InvalidArgumentException;
use App\ReadXYZ\Lessons\Game;
use App\ReadXYZ\Lessons\TabType;
use App\ReadXYZ\Models\KeyValuePair;
use stdClass;

class Page
{
    protected string $pageTitle;
    protected string $initialTabName;
    protected array $navBar;  // key/value pairs of title => link
    protected array $tabs;    // key value pairs of title => html
    protected string $errors;
    protected array $arguments;
    protected array $baseArguments;
    /** @var Game[] */
    protected array $games;

    public function __construct(string $title)
    {
        $this->pageTitle = $title;
        $this->errors = '';
        $this->initialTabName = 'Main';
        $this->navBar = [];
        $this->tabs = [];
        $this->arguments = [];
        $this->baseArguments = [];
    }

    /**
     * add an action in the actions folder.
     *
     * @param string $title  the title to display in the link
     * @param string $action the base file name (no .php extension)
     * @param array  $parms  if there are any parameters. pass them as key/value pairs here
     */
    public function addActionsLink(string $title, string $action, array $parms = []): void
    {
        if (empty($title)) {
            throw new InvalidArgumentException('Title parameter cannot be null.');
        }
        $time = strval(time());
        $link = "/actions/$action.php?time=$time";
        if ($parms) {
            $link .= '&' . http_build_query($parms);
        }
        $this->navBar[$title] = $link;
    }

    /**
     * Add argument(s) that will be passed to 'default_body.html.twig' template.
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

    /**
     * Add argument(s) that will be passed to 'base.html.twig' template.
     *
     * @param array $args
     */
    public function addBaseArguments(array $args): void
    {
        if (isAssociative($args)) {
            foreach ($args as $name => $value) {
                $this->baseArguments[$name] = $value;
            }
        }
    }

    public function addError(string $message): void
    {
        addAssociative($this->baseArguments, 'modalErrorHtml', $message);
        $this->addBaseArguments(['otherJqueryReady' => '$("#modalError").modal("show");']);
    }

    public function addNavLink(string $title, string $link, array $parms = []): void
    {
        if (empty($title)) {
            throw new InvalidArgumentException('Title parameter cannot be null.');
        }
        $firstTime = true;
        $url = $link;
        foreach ($parms as $name => $value) {
            $url .= ($firstTime ? '?' : '&');
            $url .= "$name=" . urlencode($value);
            $firstTime = false;
        }
        $this->navBar[$title] = $url;
    }

    public function addNavPair(KeyValuePair $pair)
    {
        $pair->addToArray($this->navBar);
    }

    public function addTab(TabType $tabInfo, string $html): void
    {
        if (!$tabInfo) {
            throw new InvalidArgumentException('Tab info parameter cannot be null.');
        }
        $id = $tabInfo->tabTypeId;
        if (isset($this->tabs[$id])) {
            $this->tabs[$id]->html .= $html;
        } else {
            $tabInfo->html = $html;
            $this->tabs[$id] = $tabInfo;
        }
    }

    public function setInitialTab(string $tabName): void
    {
        $this->initialTabName = $tabName;
    }

    public function setPageTitle(string $title): void
    {
        $this->pageTitle = $title;
    }

    public function display(string $template): void
    {
        $this->arguments['page'] = $this;
        $this->arguments['isSmallScreen'] = ScreenCookie::isScreenSizeSmall();
        echo TwigFactory::getInstance()->renderTemplate($template, $this->arguments);
    }

}
