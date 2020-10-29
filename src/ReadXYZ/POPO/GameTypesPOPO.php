<?php

namespace App\ReadXYZ\POPO;

use App\ReadXYZ\Helpers\Location;
use App\ReadXYZ\Helpers\Util;

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
        $random = new GameTypePOPO('random', 'Word Cards', 'intro', 1);
        $wordBuilder = new GameTypePOPO('word-builder', 'Word Builder', 'intro', 2);

        $soundBox = new GameTypePOPO('sound-box', 'Sound Boxes', 'write', 1, true);
        $soundBox->universalGameUrl = Location::SOUND_BOX_GAME;

        $ticTacToe = new GameTypePOPO('tic-tac-toe', 'Tic-Tac-Toe', 'practice', 1, true);
        $ticTacToe->universalGameUrl = Location::TIC_TAC_TOE_GAME;

        $whack = new GameTypePOPO('whack-a-mole', 'Whack-A-Mole', 'practice', 2);
        $sort = new GameTypePOPO('sort', 'Rhyme Sort', 'practice', 3);
        $alien = new GameTypePOPO('alien', 'Alien', 'practice', 4);
        $scramble = new GameTypePOPO('scramble', 'Word Scramble', 'spell', 1);
        $mastery = new GameTypePOPO('mastery', 'Mastery Flip', 'mastery', 1);
        $sentences = new GameTypePOPO('sentences', 'Missing Word', 'fluency', 0);

        $this->games = [$random, $wordBuilder, $soundBox, $ticTacToe, $whack, $sort, $alien, $scramble, $mastery, $sentences];
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
