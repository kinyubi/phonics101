<?php

namespace Tests\ReadXYZ\Twig;

use App\ReadXYZ\Twig\CacheTemplate;
use PHPUnit\Framework\TestCase;

class CacheTemplateTest extends TestCase
{

    public function testDisplay()
    {
    }

    public function testClearTwigCache()
    {
        $template = new CacheTemplate();
        $results = $template->returnCounts();
        $this->assertEquals(0, $results['after']);
        $this->assertNotEquals(-1, $results['before']);
    }
}
