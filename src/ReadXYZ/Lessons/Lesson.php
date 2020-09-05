<?php

namespace ReadXYZ\Lessons;

use JsonSerializable;
use ReadXYZ\Helpers\Util;
use ReadXYZ\Traits\LessonTraits;
use stdClass;

class Lesson implements JsonSerializable
{
    use LessonTraits;

    private string $lessonId;
    private string $lessonName;
    private string $lessonKey;
    private string $script;
    private array $alternateNames;
    private string $groupId;
    private string $lessonDisplayAs;
    private ?array $wordList;
    private ?array $supplementalWordList;
    private ?array $contrastList;
    private ?array $stretchList;
    /** @var string[] */
    private ?array $fluencySentences;
    /** @var Game[] */
    private ?array $games; // plain array
    /** @var string[] */
    private ?array  $tabNames;
    private ?Spinner $spinner;
    private string $pronounceImage;
    /** @var string[] */
    private ?array $contrastImages;
    private int $ordering;
    private bool $visible;
    private ?array $wordLists;
    private array $wordListIndexForTab;
    private array $notes;

    /**
     * Lesson constructor. Called by the Lessons class when building an associative array of lessons.
     * Lessons class reads in a json file. When json_decode is performed each lesson becomes a stdClass object.
     *
     * @param stdClass $lesson
     */
    public function __construct(stdClass $lesson)
    {
        $this->lessonId = $lesson->lessonId;
        $this->lessonName = $lesson->lessonName;
        $this->lessonKey = $lesson->lessonKey;
        $this->script = $lesson->script ?? 'Blending';
        $this->alternateNames = $lesson->alternateNames ?? [$lesson->lessonName => $lesson->lessonName];
        $this->groupId = $lesson->groupId;
        $this->lessonDisplayAs = $lesson->lessonDisplayAs ?? $lesson->lessonId;
        $this->wordList = Util::csvStringToArray($lesson->wordList);
        $this->supplementalWordList = Util::csvStringToArray($lesson->supplementalWordList);
        $this->contrastList = Util::csvStringToArray($lesson->contrastList);
        $this->stretchList = Util::stretchListToArray($lesson->stretchList);
        $this->fluencySentences = $lesson->fluencySentences ?? null;

        // get the universal games added to the lesson
        $gameTypes = GameTypes::getInstance();
        $universalGames = $gameTypes->getUniversalGameTypes();
        foreach ($universalGames as $game) {
            $this->games[$game->getBelongsOnTab()][] = new Game(
                $game->getGameTypeId(),
                $game->getGameDisplayAs(),
                $game->getThumbNailUrl(),
                $game->getBelongsOnTab(),
                $game->getUniversalGameUrl()
            );
        }
        // combine the GameTypes information with the games array in the lesson to build the games array
        foreach ($lesson->games as $game) {
            if (empty($game->url)) {
                continue;
            }
            $gameType = $gameTypes->getGameInfo($game->gameTypeId);
            if (null == $gameType) {
                continue;
            }
            $this->games[$gameType->getBelongsOnTab()][] = new Game(
                $gameType->getGameTypeId(),
                $gameType->getGameDisplayAs(),
                $gameType->getThumbNailUrl(),
                $gameType->getBelongsOnTab(),
                $game->url
            );
        }
        $this->tabNames = [];
        foreach ($lesson->tabs as $tab) {
            $this->tabNames[] = Util::fixTabName($tab);
        }
        $this->spinner = null;
        $this->spinner = new Spinner($lesson->spinner->prefixList, $lesson->spinner->vowel ?? '', $lesson->spinner->suffixList ?? '');

        $this->pronounceImage = $lesson->pronounceImage ?? '';
        $this->contrastImages = $lesson->contrastImages ?? null;
        $this->ordering = $lesson->ordering ?? 0;
        $this->visible = true;

        $this->wordLists = (null === $this->wordList) ? null : [];
        if ($this->tabNames && (null !== $this->wordList)) {
            foreach ($this->tabNames as $tabName) {
                if (in_array($tabName, ['fluency', 'spinner', 'spell', 'mastery'])) {
                    continue;
                }
                $this->wordLists[$tabName] = $this->make3Lists($tabName);
                $this->wordListIndexForTab[$tabName] = 0;
            }
        }
        $this->notes = [];
        if (isset($lesson->notes)) {
            foreach ($this->notes as $note) {
                $this->notes[$note['tab']] = $note['note'];
            }
        }
    }

    /**
     * The lessons.json file only contains the gameTypeId and url for each game in a lesson. The GameTypes class is a
     * collection of GameType objects that provide information about all games of this type. This includes thumbnail
     * information, ordering, the tab the game is displayed on, etc.
     *
     * @param string $gameTypeId
     *
     * @return GameType|null
     */
    public static function getGameInfo(string $gameTypeId): ?GameType
    {
        GameTypes::getInstance()->getGameInfo($gameTypeId);
    }

    /**
     * There are a limited number of tab types that are incorporated in the program. The tab type information
     * tells us the class we use to display the tab and other relevant information.
     *
     * @param string $tabTypeId
     *
     * @return TabType|null
     */
    public static function getTabInfo(string $tabTypeId): ?TabType
    {
        TabTypes::getInstance()->getTabInfo($tabTypeId);
    }

    // ============ GETTERS ===========

    /**
     * @return string[]
     */
    public function getAlternateNames()
    {
        return $this->alternateNames;
    }

    /**
     * @return string[]
     */
    public function getContrastImages(): array
    {
        return $this->contrastImages;
    }

    /**
     * @return string[]
     */
    public function getContrastList(): array
    {
        return $this->contrastList;
    }

    /**
     * @return string[]
     */
    public function getFluencySentences(): array
    {
        return $this->fluencySentences;
    }

    /**
     * @return string[]
     */
    public function getGames(): array
    {
        return $this->games;
    }

    /**
     * @param string $tabName
     *
     * @return array|Game[]
     */
    public function getGamesForTab(string $tabName): array
    {
        return $this->games[Util::fixTabName($tabName)] ?? [];
    }

    /**
     * @return string
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @return string
     */
    public function getLessonDisplayAs()
    {
        return $this->lessonDisplayAs;
    }

    /**
     * @return string
     */
    public function getLessonId()
    {
        return $this->lessonId;
    }

    /**
     * @return string
     */
    public function getLessonKey()
    {
        return $this->lessonKey;
    }

    /**
     * @return string
     */
    public function getLessonName()
    {
        return $this->lessonName;
    }

    /**
     * @param string $tabName the tab the note is for
     * @return string text or html to be displayed in the sidebar. Empty string if no note found
     */
    public function getNote(string $tabName): string
    {
        return $this->notes[$tabName] ?? '';
    }

    /**
     * @return int
     */
    public function getOrdering()
    {
        return $this->ordering;
    }

    /**
     * @return string
     */
    public function getPronounceImage()
    {
        return $this->pronounceImage;
    }

    /**
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * @return Spinner|null
     */
    public function getSpinner()
    {
        return $this->spinner;
    }

    /**
     * @return string[]
     */
    public function getStretchList(): array
    {
        return $this->stretchList;
    }

    /**
     * @return string[]
     */
    public function getSupplementalWordList()
    {
        return $this->supplementalWordList;
    }

    /**
     * @return string[]
     */
    public function getTabNames(): array
    {
        return $this->tabNames;
    }

    /**
     * @return bool
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * @return string[]
     */
    public function getWordList(): array
    {
        return $this->wordList;
    }

    /**
     * @param string $tabName
     *
     * @return array|null three arrays created for a specific tab
     */
    public function getWordLists(string $tabName = '', int $index = 0)
    {
        $wordList = $this->wordLists[$tabName][$index] ?? false;
        if (false === $wordList) {
            throw new \RuntimeException('Wordlist should exist for ');
        }

        return $wordList;
    }

    // =========== PRIVATE METHODS ====

    /**
     * creates three sets of words for a lesson tab. This allows refresh to display more non-repetitive word lists.
     * The practice tab has 7 rows of 3 words and other tabs have a single column of 9 words. The practice and test
     * tabs use both the primary and supplemental word lists. Other tabs use just the primary word list.
     *
     * @param string $tabName The tab we are building the word list for
     *
     * @return array a 3 x n array where n is 21 for practice tab and 9 for other tabs
     */
    private function make3Lists(string $tabName): array
    {
        $isPractice = Util::contains_ci($tabName, 'prac');
        $useSupplemental = (Util::contains_ci($tabName, 'test') || $isPractice);
        $arraySize = $isPractice ? 21 : 9;
        $tripleSize = 3 * $arraySize;
        $hasSupplemental = not(empty($this->supplementalWordList));
        $initialWords = ($useSupplemental && $hasSupplemental) ? array_merge($this->wordList, $this->supplementalWordList) : $this->wordList;
        $words = $initialWords;
        if (not(is_array($words))) {
            trigger_error('No wordlist');
        }
        while (count($words) < $tripleSize) {
            $words = array_merge($words, $initialWords);
        }

        $result = [];
        for ($i = 0; $i < 3; ++$i) {
            $slice = array_slice($words, $arraySize * $i, $arraySize);
            shuffle($slice);
            $result[$i] = $slice;
        }

        return $result;
    }

    // =========== PROTECTED/PUBLIC METHODS

    /**
     * used by json_encode to create the json equivalent of this class. Required to implement JsonSerializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'lessonId' => $this->lessonId,
            'lessonName' => $this->lessonName,
            'lessonKey' => $this->lessonKey,
            'script' => $this->script,
            'alternateNames' => $this->alternateNames,
            'groupId' => $this->groupId,
            'lessonDisplayAs' => $this->lessonDisplayAs,
            'wordLists' => $this->wordLists,
            'supplementalWordList' => $this->supplementalWordList,
            'contrastList' => $this->contrastList,
            'stretchList' => $this->stretchList,
            'fluencySentences' => $this->fluencySentences,
            'gameUrls' => $this->games,
            'tabs' => $this->tabNames,
            'spinner' => $this->spinner,
            'pronounceImage' => $this->pronounceImage,
            'contrastImages' => $this->contrastImages,
            'ordering' => $this->ordering,
            'visible' => $this->visible,
            'notes' => $this->notes,
            'wordListIndexForTabs' => $this->wordListIndexForTab
        ];
    }
}
