<?php

namespace ReadXYZ\Lessons;

use ReadXYZ\Helpers\Util;

class GameTypes
{
    private static GameTypes $instance;

    /** @var GameType[] */
    private array $gameTypes;

    private function __construct()
    {
        $json = file_get_contents(Util::getReadXyzSourcePath('resources/gameTypes.json'));
        $gameTypes = json_decode($json);
        foreach ($gameTypes as $gameType) {
            $this->gameTypes[$gameType->gameTypeId] = new GameType($gameType);
        }
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new GameTypes();
        }

        return self::$instance;
    }

    /**
     * @param string $gameTypeId
     *
     * @return GameType|null
     */
    public function getGameInfo(string $gameTypeId): ?GameType
    {
        $gameType = $this->gameTypes[$gameTypeId] ?? null;

        return $gameType;
    }

    /**
     * returns tic-tac-toe and sound-boxes gameTypes (this may change).
     *
     * @return GameType[] GameType instances of games that are used in every lesson
     */
    public function getUniversalGameTypes(): array
    {
        $games = [];
        foreach ($this->gameTypes as $gameType) {
            if ($gameType->isUniversal()) {
                $games[] = $gameType;
            }
        }

        return $games;
    }
}
