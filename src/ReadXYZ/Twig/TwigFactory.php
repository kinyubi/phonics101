<?php

namespace App\ReadXYZ\Twig;

use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\ScreenCookie;
use App\ReadXYZ\Helpers\Util;
use Throwable;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TemplateWrapper;

class TwigFactory
{
    private static ?TwigFactory $instance;

    private Environment $twigEnvironment;
    private string $error = '';

    /**
     * @var TemplateWrapper[]
     */
    private array $templates = [];

    private function __construct()
    {
        $options = [];
        $options['debug'] =  Util::isLocal();
        $options['auto_reload'] = Util::isLocal();
        $options['cache'] = Util::getReadXyzSourcePath('cache');
        // $options['cache'] = false;
        $options['autoescape'] = false;
        $options['optimizations'] = 0;
        $path = Util::getProjectPath('templates');
        $loader = new FilesystemLoader([$path, "$path/parts", "$path/tabs", "$path/forms"]);
        $this->twigEnvironment = new Environment($loader, $options);
        $this->twigEnvironment->addGlobal('session', $_SESSION);
        $screenCookie = ScreenCookie::getInstance();
        $this->twigEnvironment->addGlobal('screen', $screenCookie);
        $this->twigEnvironment->addGlobal('smaller', $screenCookie->isScreenSizeSmall());
        if (Util::isLocal()) {
            $this->twigEnvironment->addExtension(new DebugExtension());
        }
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new TwigFactory();
        }

        return self::$instance;
    }

    public static function destroyInstance()
    {
        self::$instance = null;
    }


    /**
     * html.twig suffix is optional
     * @param string $templateName the name of a template (no path, .html.twig optional)
     *
     * @return TemplateWrapper
     *
     * @throws PhonicsException
     */
    private function loadTemplate(string $templateName): TemplateWrapper
    {
        $ext = '.html.twig';
        $partDir = 'parts/';

        $realName = $templateName;

        // remove parts subdirectory if its part of template name
        if (Util::contains($partDir, $realName)) {
            $realName = str_replace($partDir, '', $realName);
        }
        // remove .html.twig extension if its part of template name
        if (!Util::contains($ext, $realName)) {
            $realName .= $ext;
        }
        // creates loader for this template if it doesn't exist
        $baseName = str_replace($ext, '', $realName);
        if (!key_exists($baseName, $this->templates)) {
            try {
                $this->templates[$baseName] = $this->twigEnvironment->load($realName);
            } catch (LoaderError | RuntimeError | SyntaxError $ex) {
                throw new PhonicsException("Unexpected Twig Error loading template.", 0, $ex);
            }

        }
        // return the loader
        return $this->templates[$baseName];
    }

    /**
     * @param string $templateName
     * @param array $args
     * @return string
     * @throws PhonicsException
     */
    public function renderTemplate(string $templateName, array $args = [])
    {
        $template = $this->loadTemplate($templateName);
        return $template->render($args);
    }

    /**
     * @param string $templateName
     * @param string $blockName
     * @param array $args
     * @return string
     * @throws PhonicsException
     */
    public function renderBlock(string $templateName, string $blockName, array $args = [])
    {
        $template = $this->loadTemplate($templateName);
        try {
            return $template->renderBlock($blockName, $args);
        } catch (Throwable $ex) {
            throw new PhonicsException("Unexpected Twig Error rendering block.", 0, $ex);
        }
    }
}
