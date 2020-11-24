<?php

namespace App\ReadXYZ\POPO;

use App\ReadXYZ\Data\GroupData;
use App\ReadXYZ\Helpers\Location;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Lessons\GameTypes;
use App\ReadXYZ\Lessons\Groups;
use App\ReadXYZ\Lessons\Spinner;
use App\ReadXYZ\Lessons\Warmups;
use App\ReadXYZ\Models\Log;
use JsonSerializable;
use stdClass;

class Lesson implements JsonSerializable
{

    public string $lessonId;
    public string $lessonName;
    public string $lessonKey;
    public string $script;
    public array $alternateNames;
    public string $groupName;
    public string $groupCode;
    public string $lessonDisplayAs;
    public ?array $wordList;
    public ?array $supplementalWordList;
    public ?array $stretchList;
    /** @var string[] */
    public ?array $fluencySentences;
    /** @var Game[] */
    public ?array $games; // plain array
    /** @var string[] */
    public ?array  $tabNames;
    public ?Spinner $spinner;
    public string $pronounceImage;
    public string $pronounceImageThumb;
    /** @var string[] */
    public ?array $contrastImages;
    public int $ordering = 0;
    public bool $visible;
    public ?array $wordLists;
    public array $wordListIndexForTab;
    public array $notes;
    public array $allWords;
    public string $book;
    public array $lengths;
    public ?Warmup $warmup;

    /**
     * Lesson constructor. Called by the Lessons class when building an associative array of lessons.
     * Lessons class reads in a json file. When json_decode is performed each lesson becomes a stdClass object.
     *
     * @param stdClass $lesson
     */
    public function __construct(stdClass $lesson)
    {
        $this->lessonId = $lesson->lessonCode;
        $this->lessonName = $lesson->lessonName;
        $this->lessonKey = $lesson->lessonName;
        $this->script = 'Blending';
        $this->alternateNames = $lesson->alternateNames;
        $this->groupCode = $lesson->groupCode;
        $this->groupName = Groups::getInstance()->getGroupName($lesson->groupCode);
        $this->lessonDisplayAs = $lesson->lessonDisplayAs;

        $this->wordList = is_string($lesson->wordList) ? Util::csvStringToArray($lesson->wordList) : $lesson->wordList;
        $this->supplementalWordList = is_string($lesson->supplementalWordList) ? Util::csvStringToArray($lesson->supplementalWordList) : $lesson->supplementalWordList;
        $this->allWords = array_merge($this->wordList ?? [], $this->supplementalWordList ?? []);
        if (isset($lesson->contrastImages)) {
            $this->contrastImages = $lesson->contrastImages;
        } else if (isset($lesson->contrastList)) {
            $this->contrastImages = Util::csvStringToArray($lesson->contrastList);
        }

        $this->stretchList = is_string($lesson->stretchList) ? Util::stretchListToArray($lesson->stretchList) : $lesson->stretchList;
        $this->fluencySentences = $lesson->fluencySentences;
        $this->games = [];
        $this->book = $lesson->flipBook ?? '';
        $this->warmup = Warmups::getInstance()->getLessonWarmup('warmup');
        $this->spinner = null;
        $this->spinner = new Spinner($lesson->spinner->prefixList, $lesson->spinner->vowel ?? '', $lesson->spinner->suffixList ?? '');

        // get the universal games added to the lesson
        $gameTypes = GameTypes::getInstance();
        $universalGames = $gameTypes->getUniversalGameTypes();
        foreach ($universalGames as $game) {
            $url = $game->universalGameUrl;
            if ($game->gameTypeId == 'tic-tac-toe') {
                $wordlist = join('_', $this->getTicTacToeWords());
                $url .= '?wordlist=' . $wordlist;
            }
            $this->games[$game->belongsOnTab][] = new Game(
                $game->gameTypeId,
                $game->gameDisplayAs,
                $game->thumbNailUrl,
                $game->belongsOnTab,
                $url
            );
        }
        // combine the GameTypes information with the games array in the lesson to build the games array
        foreach ($lesson->games as $game) {
            if (empty($game->url)) {
                continue;
            }
            $gameType = $gameTypes->getGameInfo($game->gameTypeId);
            if ( ! $gameType) {
                continue;
            }
            $gameTag = $gameType->belongsOnTab;
            $this->games[$gameTag][] = new Game(
                $gameType->gameTypeId,
                $gameType->gameDisplayAs,
                $gameType->thumbNailUrl,
                $gameType->belongsOnTab,
                $game->url
            );
        }
        $this->tabNames = [];
        if ($this->warmup) {
            $this->tabNames[] = 'warmup';
        }
        $this->tabNames = array_merge($this->tabNames, ['intro', 'write', 'practice']);
        if ($this->spinner) {
            $this->tabNames[] = 'spell';
        }
        $this->tabNames = array_merge($this->tabNames, ['mastery', 'fluency', 'test']);
        if ($this->book) {
            $this->tabNames[] = 'book';
        }

        $this->pronounceImage = Location::getPronounceImage($lesson->pronounceImage ?? '');
        $this->pronounceImageThumb = Location::getPronounceImageThumb($lesson->pronounceImage ?? '');

        $this->contrastImages = $lesson->contrastImages ?? null;
        $this->visible = $lesson->active != 'N';

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
    }


// ======================== PUBLIC METHODS =====================
    /**
     * used by json_encode to create the json equivalent of this class. Required to implement JsonSerializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'lessonId'             => $this->lessonId,
            'lessonName'           => $this->lessonName,
            'lessonKey'            => $this->lessonKey,
            'script'               => $this->script,
            'alternateNames'       => $this->alternateNames,
            'groupName'            => $this->groupName,
            'lessonDisplayAs'      => $this->lessonDisplayAs,
            'wordLists'            => $this->wordLists,
            'supplementalWordList' => $this->supplementalWordList,
            'stretchList'          => $this->stretchList,
            'fluencySentences'     => $this->fluencySentences,
            'gameUrls'             => $this->games,
            'tabs'                 => $this->tabNames,
            'spinner'              => $this->spinner,
            'pronounceImage'       => $this->pronounceImage,
            'contrastImages'       => $this->contrastImages,
            'ordering'             => $this->ordering,
            'visible'              => $this->visible,
            'notes'                => $this->notes,
            'wordListIndexForTabs' => $this->wordListIndexForTab
        ];
    }

// ======================== PRIVATE METHODS =====================
    private function getTicTacToeWords(): array
    {
        $initialWords = $this->wordList;
        $words = $initialWords;
        while (count($words) < 9) {
            $words = array_merge($words, $initialWords);
        }
        $slice = array_slice($words, 0, 9);
        shuffle($slice);
        return $slice;
    }

    // =========== PROTECTED/PUBLIC METHODS

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
            LOG::error("No wordlist for tab $tabName in lesson {$this->lessonName}.");
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
}
