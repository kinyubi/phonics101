<?php

namespace ReadXYZ\POPO;

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
    private array $games;

    public function __construct()
    {
        $loc = '/images/thumbs';

        $name = 'word-cards';
        $this->games[] = new GameTypePOPO('random', 'Word Cards', "$loc/$name.jpg", 'intro', 1);
        $name = 'word-builder';
        $this->games[] = new GameTypePOPO($name, 'Word Builder', "$loc/$name.jpg", 'intro', 2);

        $name = 'sound-boxes';
        $game = new GameTypePOPO($name, 'Sound Boxes', "$loc/$name.jpg", 'write', 1, true);
        $game->universalGameUrl = 'https://toytheater.com/elkonin-boxes';
        $this->games[] = $game;

        $name = 'tic-tac-toe';
        $game = new GameTypePOPO($name, 'Tic-Tac-Toe', "$loc/$name.jpg", 'practice', 1, true);
        $game->universalGameUrl = '/tictactoe/tictac.php';
        $this->games[] = $game;

        $name = 'advanced-spell';
        $game = new GameTypePOPO($name, 'Advanced Spell', "$loc/$name.jpg", 'spell', 2, true);
        $game->universalGameUrl = 'https://www.reallygreatreading.com/lettertiles/';
        $this->games[] = $game;

        $name = 'whack-a-mole';
        $this->games[] = new GameTypePOPO($name, 'Whack-A-Mole', "$loc/$name.jpg", 'practice', 2);
        $name = 'rhyme-sort';
        $this->games[] = new GameTypePOPO('sort', 'Rhyme Sort', "$loc/$name.jpg", 'practice', 3);
        $name = 'alien';
        $this->games[] = new GameTypePOPO('alien', 'Alien', "$loc/$name.jpg", 'practice', 4);
        $name = 'word-scramble';
        $this->games[] = new GameTypePOPO('scramble', 'Word Scramble', "$loc/$name.jpg", 'spell', 1);
        $name = 'mastery-flip';
        $this->games[] = new GameTypePOPO('mastery', 'Mastery Flip', "$loc/$name.jpg", 'mastery', 1);
        $name = 'missing-word';
        $this->games[] = new GameTypePOPO('sentences', 'Missing Word', "$loc/$name.jpg", 'fluency', 0);
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
    $gameTypes->write(Util::getPublicPath('resources/gameTypes.json'));
}
