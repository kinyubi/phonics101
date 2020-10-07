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
    private array $tabs = [];

    public function __construct()
    {

        $instructions = new TabTypePOPO('instructions', 'Instructions', 'InstructionPage');
        $practice = new TabTypePOPO('practice', 'Word Practice', 'WordList', false, 3, );
        $spell = new TabTypePOPO('spell', 'Word Chain', 'WordSpinner', true);
        $intro = new TabTypePOPO('intro', 'Stretch', 'WordList', true);
        $test= new TabTypePOPO('test', 'Lesson Test', 'WordListTimed');
        $write = new TabTypePOPO('write', 'Tap &amp; Write', 'WordList', true);
        $fluency = new TabTypePOPO('fluency', 'Repeated Reading', '', false, 0, true);
        $mastery = new TabTypePOPO('mastery', 'Mastery Check', '', false, 0, true);
        $warmup = new TabTypePOPO('warmup', 'Phonological Awareness', '', false, 0, true);

        $practice->setScript('Read each word out loud. We will use the timer and play the games in the sidebar for additional practice.');
        $write->setScript('I will choose 4 words from the lesson. For each word, you will tap the sounds and then write the word.');
        $spell->setScript('Click the letters to spell <say a word>. Now change the first letter to <pick a letter>. What word did you spell?');
        $mastery->setScript('I will point to a word. If you read it correctly, I will check the box. If not, it is ok. We will practice it at the end.');
        $fluency->setScript('You will read this 3 times. I will use the timer, but focus on reading the words smoothly and correctly.');
        $test->setScript("Let's see how well you have learned the words in this lesson. (Mastery Goal: all words correct in 10 - 15 seconds.)");
        $intro->setScript("Today we are learning words that end with /at/. Let's read the words together.");
        $warmup->setScript("I am going to ask you some questions. During this exercise, you will watch me and listen.");
        $this->tabs = [$instructions, $practice, $spell, $intro, $test, $write, $fluency, $mastery, $warmup];
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
