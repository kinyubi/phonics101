<?php

namespace ReadXYZ\POPO;

use ReadXYZ\Helpers\Location;
use ReadXYZ\Helpers\Util;

/**
 * Class GameTypesPOPO - We use this class to create the GameTypes.json file. The Lessons\GameTypes class will use the json file.
 * By running this file standalone, it will create the json file and put it in the proper location.
 *
 * @package ReadXYZ\POPO
 */
class GameTypesPOPO
{
    /** @var GameTypePOPO[] */
    private array $games = [];

    public function __construct()
    {
        $name = 'word-cards';
        $this->games[] = new GameTypePOPO('random', 'Word Cards', 'intro', 1);
        $name = 'word-builder';
        $this->games[] = new GameTypePOPO($name, 'Word Builder', 'intro', 2);

        $name = 'sound-box';
        $game = new GameTypePOPO($name, 'Sound Boxes', 'write', 1, true);
        $game->universalGameUrl = Location::SOUND_BOX_GAME;
        $this->games[] = $game;

        $name = 'tic-tac-toe';
        $game = new GameTypePOPO($name, 'Tic-Tac-Toe', 'practice', 1, true);
        $game->universalGameUrl = Location::TIC_TAC_TOE_GAME;
        $this->games[] = $game;

        // $name = 'advanced-spell';
        // $game = new GameTypePOPO($name, 'Advanced Spell', 'spell', 2, true);
        // $game->universalGameUrl = 'https://www.reallygreatreading.com/lettertiles/';
        // $this->games[] = $game;

        $name = 'whack-a-mole';
        $this->games[] = new GameTypePOPO($name, 'Whack-A-Mole', 'practice', 2);
        $name = 'rhyme-sort';
        $this->games[] = new GameTypePOPO('sort', 'Rhyme Sort', 'practice', 3);
        $name = 'alien';
        $this->games[] = new GameTypePOPO('alien', 'Alien', 'practice', 4);
        $name = 'word-scramble';
        $this->games[] = new GameTypePOPO('scramble', 'Word Scramble', 'spell', 1);
        $name = 'mastery-flip';
        $this->games[] = new GameTypePOPO('mastery', 'Mastery Flip', 'mastery', 1);
        $name = 'missing-word';
        $this->games[] = new GameTypePOPO('sentences', 'Missing Word', 'fluency', 0);
    }

    public function write(string $filename): void
    {
        $json = json_encode($this->games, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($filename, $json);
    }
}

// This is only run if invoked directly
if (!count(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))) {
    require dirname(__DIR__) . '/autoload.php';
    $gameTypes = new GameTypesPOPO();
    $gameTypes->write(Util::getReadXyzSourcePath('resources/gameTypes.json'));
}
