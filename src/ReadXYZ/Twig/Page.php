<?php

namespace ReadXYZ\Twig;

use App\ReadXYZ\Helpers\ScreenCookie;
use InvalidArgumentException;
use ReadXYZ\Helpers\Location;
use ReadXYZ\Lessons\Game;
use ReadXYZ\Models\Document;
use ReadXYZ\Models\KeyValuePair;

class Page
{
    private string $pageTitle;
    private string $initialTabName;
    private array $navBar;  // key/value pairs of title => link
    private array $tabs;    // key value pairs of title => html
    private string $errors;
    private array $arguments;
    private array $baseArguments;
    /** @var Game[] */
    private array $games;

    private function makeArgs(): string
    {
    }

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

    public function addTab(string $tabName, string $html): void
    {
        if (empty($tabName)) {
            throw new InvalidArgumentException('Tab name parameter cannot be null.');
        }
        if (isset($this->tabs[$tabName])) {
            $this->tabs[$tabName] .= $html;
        } else {
            $this->tabs[$tabName] = $html;
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

    private function baseRender(array $pageArgs): string
    {
        $twig = TwigFactory::getInstance();
        $pageArgs['pageTitle'] = $this->pageTitle;
        if ($this->errors) {
            $pageArgs['errorMessage'] = $this->errors;
        }
        if ($this->navBar) {
            $pageArgs['menu'] = $this->navBar;
        }
        $pageArgs['isSmallScreen'] = ScreenCookie::isScreenSizeSmall();
        $this->addArguments($pageArgs);
        $html = $twig->renderBlock('default_body', 'body', $this->arguments);
        $this->addBaseArguments(['content' => $html]);
        $this->addBaseArguments(['lightbox' => 'colorbox', 'jquery' => 'latest']);
        return $twig->renderTemplate('base', $this->baseArguments);
    }

    /**
     * @param string $template The template to use
     * @param string $block    The block to use in the template (default is body)
     * @param array  $args     The arguments associated with the template block
     *
     * @return string The rendered HTML code
     */
    public function simpleRender(string $template, string $block = 'body', array $args = []): string
    {
        $twig = TwigFactory::getInstance();
        $html = $twig->renderBlock($template, $block, $args);

        $pageArgs = [];
        $pageArgs['tabs'] = ['Main' => $html];
        $pageArgs['initialTabName'] = 'Main';
        return $this->baseRender($pageArgs);
    }

    public function render(string $initialTabName = ''): string
    {
        $pageArgs = [];
        $pageArgs['pageTitle'] = $this->pageTitle;
        $pageArgs['tabs'] = $this->tabs;
        if ($initialTabName) {
            $pageArgs['initialTabName'] = $initialTabName;
        }
        return $this->baseRender($pageArgs);
    }
}
