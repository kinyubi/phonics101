<?php


namespace App\ReadXYZ\Twig;


use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Page\Page;

class CacheTemplate
{
    private Page   $page;
    private string $before = '';
    private string $after  = '';
    private int    $beforeCount = -1;
    private int    $afterCount = -1;

    public function __construct(string $errorMessage = '')
    {
        $_SESSION['TwigClear'] = true;
        $this->page = new Page('ReadXYZ Cache Status');
        if ($errorMessage) {
            $this->page->addError($errorMessage);
        }
    }

    private function recursiveGlob($pattern, $flags = 0) {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->recursiveGlob($dir.'/'.basename($pattern), $flags));
        }
        return $files;
    }

    // ======================== PUBLIC METHODS =====================

    public function clearCacheAndGenerated(): void
    {
        $this->clearTwigCache(true);
        $this->clearGenerated();
    }

    public function clearTwigCache(bool $force = false): void
    {
        $cacheFlag = Util::getPublicPath('clear_cache.txt');
        $flagExists = file_exists($cacheFlag) && !Util::isLocal();
        if ($flagExists || $force) {
            $cachePath = Util::getReadXyzSourcePath('cache');
            $files = $this->recursiveGlob("$cachePath/*/*.php");
            $count = count($files);
            $this->before = "Attempting to delete $count Twig cache files.\n";
            foreach ($files as $file) {unlink($file);}
            $afterFiles = $this->recursiveGlob("$cachePath/*/*.php");
            $afterCount = count($afterFiles);
            $deletedCount = $count - $afterCount;
            $this->after = "$deletedCount Twig cache files where successfully deleted.";
            $this->afterCount = $afterCount;
            $this->beforeCount = $count;
        }
        // only delete if local
        if ($flagExists) unlink($cacheFlag);

    }

    public function clearGenerated()
    {
        $generatedPath = Util::getPublicPath('generated');
        $files = $this->recursiveGlob("$generatedPath/*.*") ;
        foreach ($files as $file) {unlink($file);}
    }

    /**
     * @param string $errorMessage
     * @throws PhonicsException
     */
    public function display(string $errorMessage = ''): void
    {
        if ($errorMessage) {
            $this->page->addError($errorMessage);
        }

        $args = ['page' => $this->page, 'before' => $this->before, 'after' => $this->after];
        echo TwigFactory::getInstance()->renderTemplate('cache_status.html.twig', $args);
    }

    public function returnCounts(): array
    {
        return ['before' => $this->beforeCount, 'after' => $this->afterCount, 'message' => $this->before . $this->after];
    }
}
