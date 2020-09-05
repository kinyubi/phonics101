<?php

namespace ReadXYZ\POPO;

use ReadXYZ\Helpers\Util;

/**
 * Class TabTypesPOPO - class to generate the tabTypes.json file used by the Lessons\TabTypes class.
 * To generate the tabTypes.json file, run this file standalone.
 *
 * @package ReadXYZ\POPO
 */
class TabTypesPOPO
{
    /** @var TabTypePOPO[] */
    private array $tabs;

    public function __construct()
    {
        // $this->tabs[] = new TabTypePOPO('all', 'All', 'WordList', false, 3, false, true);
        // $this->tabs[] = new TabTypePOPO('contrast', 'Contrast', 'Contrast');
        // $this->tabs[] = new TabTypePOPO('earlier', 'Earlier', 'WordList', false, 3, false, true);
        // $this->tabs[] = new TabTypePOPO('recent', 'Recent', 'WordList', false, 3, false, true);
        // $this->tabs[] = new TabTypePOPO('harder', 'Harder', 'WordList', false, 3);
        // $this->tabs[] = new TabTypePOPO('page1', 'Page 1', 'InstructionPage');
        // $this->tabs[] = new TabTypePOPO('page2', 'Page 2', 'InstructionPage');
        // $this->tabs[] = new TabTypePOPO('page3', 'Page 3', 'InstructionPage');
        // $this->tabs[] = new TabTypePOPO('page4', 'Page 4', 'InstructionPage');
        // $this->tabs[] = new TabTypePOPO('page5', 'Page 5', 'InstructionPage');
        $this->tabs[] = new TabTypePOPO('instructions', 'Instructions', 'InstructionPage');
        $this->tabs[] = new TabTypePOPO('practice', 'Practice', 'WordList', false, 3, false, false, true);
        $this->tabs[] = new TabTypePOPO('spell', 'Spell', 'WordSpinner', true, 1);
        $this->tabs[] = new TabTypePOPO('intro', 'Intro', 'WordList', true, 1, false, false, true);
        $this->tabs[] = new TabTypePOPO('test', 'Test', 'WordListTimed', false, 1, false, false, true);
        $this->tabs[] = new TabTypePOPO('write', 'Write', 'WordList', true, 1, false, false, true);
        $this->tabs[] = new TabTypePOPO('fluency', 'Fluency', '', false, 0, true, false, true);
        $this->tabs[] = new TabTypePOPO('mastery', 'Mastery', '', false, 0, true);
    }

    public function write(string $filename): void
    {
        $json = json_encode($this->tabs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($filename, $json);
    }
}

// This is only run if invoked directly
if (!count(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))) {
    require dirname(__DIR__) . '/autoload.php';
    $tabTypes = new TabTypesPOPO();
    $tabTypes->write(Util::getReadXyzSourcePath('resources/tabTypes.json'));
}
