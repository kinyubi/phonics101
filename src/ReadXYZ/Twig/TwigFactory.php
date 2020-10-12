<?php

namespace ReadXYZ\Twig;

use ReadXYZ\Helpers\Util;
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
    private static TwigFactory $instance;

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
        $loader = new FilesystemLoader([$path, "$path/parts", "$path/tabs"]);
        $this->twigEnvironment = new Environment($loader, $options);
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


    /**
     * @param string $templateName the name of a template (no path, .html.twig optional)
     *
     * @return TemplateWrapper
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function loadTemplate(string $templateName): TemplateWrapper
    {
        $ext = '.html.twig';
        $partDir = 'parts/';

        $realName = $templateName;

        // remove parts subdirectory if its part of template name
        if (Util::contains($realName, $partDir)) {
            $realName = str_replace($partDir, '', $realName);
        }
        // remove .html.twig extension if its part of template name
        if (!Util::contains($realName, $ext)) {
            $realName .= $ext;
        }
        // creates loader for this template if it doesn't exist
        $baseName = str_replace($ext, '', $realName);
        if (!key_exists($baseName, $this->templates)) {
            $this->templates[$baseName] = $this->twigEnvironment->load($realName);
        }
        // return the loader
        return $this->templates[$baseName];
    }

    public function renderTemplate(string $templateName, array $args = [])
    {
        try {
            $template = $this->loadTemplate($templateName);
            //error_log("Render Template: $templateName", 0);
            $html = $template->render($args);
        } catch (Throwable $ex) {
            $html = Util::redBox("Twig template render error: $templateName.", $ex);
        }

        return $html;
    }

    public function renderBlock(string $templateName, string $blockName, array $args = [])
    {
        try {
            $template = $this->loadTemplate($templateName);
            $html = $template->renderBlock($blockName, $args);
        } catch (Throwable $e) {
            $html = Util::redBox("Twig template render error: $templateName.", $e);
        }

        return $html;
    }
}
