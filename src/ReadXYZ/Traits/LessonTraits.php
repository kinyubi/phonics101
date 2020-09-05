<?php

namespace ReadXYZ\Traits;

use ReadXYZ\Twig\TwigFactory;

trait LessonTraits
{
    /**
     * @param string $blockName The name of the block template we want to use in lesson_blocks.html.twig.
     * @param array  $args      the arguments for the twig block
     *
     * @return string
     */
    private function getTwigBlock(string $blockName, array $args = []): string
    {
        return TwigFactory::getInstance()->renderBlock('lesson_blocks', $blockName, $args);
    }
}
