<?php

namespace Tests\ReadXYZ\Twig;

use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Twig\CacheTemplate;
use PHPUnit\Framework\TestCase;

class CacheTemplateTest extends TestCase
{

    public function testDisplay()
    {
    }

    public function testRecursiveGlob()
    {
        $template = new CacheTemplate();
        $cachePath = Util::getReadXyzSourcePath('cache');
        $files = $template->recursiveGlob("$cachePath/*/*.php");
        $this->assertTrue(count($files) > 0);
    }
}
