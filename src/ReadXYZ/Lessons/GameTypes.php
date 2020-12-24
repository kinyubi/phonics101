<?php

namespace App\ReadXYZ\Lessons;

use App\ReadXYZ\Data\GameTypesData;
use App\ReadXYZ\POPO\GameType;

class GameTypes
{
    private static GameTypes $instance;

    /** @var GameType[] */
    private array $gameTypes;
    private array $universalGames = [];

    private function __construct()
    {
        $data = new GameTypesData();
        $this->gameTypes = $data->getAll();
        foreach ($this->gameTypes as $gameType) {
            if ($gameType->isUniversal) {
                $this->universalGames[] = $gameType;
            }
        }
    }

// ======================== STATIC METHODS =====================
    public static function getInstance()
    {
        if ( ! isset(self::$instance)) {
            self::$instance = new GameTypes();
        }

        return self::$instance;
    }

// ======================== PUBLIC METHODS =====================
    /**
     * @param string $gameTypeId
     *
     * @return GameType|null
     */
    public function getGameInfo(string $gameTypeId): ?GameType
    {
        $lowerGameType = strtolower($gameTypeId);
        return $this->gameTypes[$lowerGameType] ?? null;
    }

    /**
     * returns tic-tac-toe and sound-box gameTypes (this may change).
     *
     * @return GameType[] GameType instances of games that are used in every lesson
     */
    public function getUniversalGameTypes(): array
    {
        return $this->universalGames;
    }

    public function isValid(string $gameTypeId)
    {
        $lowerGameType = strtolower($gameTypeId);
        return array_key_exists($lowerGameType, $this->gameTypes);
    }
}
