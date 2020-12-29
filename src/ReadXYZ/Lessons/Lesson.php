<?php

namespace App\ReadXYZ\Lessons;

use App\ReadXYZ\Data\LessonsData;
use App\ReadXYZ\Data\WarmupData;
use App\ReadXYZ\Helpers\Debug;
use App\ReadXYZ\Helpers\Location;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;

use App\ReadXYZ\Models\Log;
use App\ReadXYZ\POPO\Game;
use JsonSerializable;
use stdClass;

class Lesson implements JsonSerializable
{

    /*
     * Lists used:
     *      Intro Tab:      stretch list
     *      Write Tab:      el-konin boxes
     *      Practice Tab:   7x3 extended word list
     *      Spell Tab:      spinner object
     *      Mastery Tab:    9x1 basic word list
     *      Fluency Tab:    fluency sentences
     *      Test Tab:       9x1 extended word list
     */
    private ?array $wordList;
    private ?array $supplementalWordList;

    public string $lessonId;
    public string $lessonName;
    public string $lessonKey;
    public string $script;
    public array $alternateNames;
    public string $groupName;
    public string $groupCode;
    public string $lessonDisplayAs;


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
    public array $notes;
    public array $allWords;
    public string $book;
    public array $lengths;

    /**
     * Lesson constructor. The input is a stdClass object from the abc_lessons table in readxyz0_phonics database
     *
     * @param stdClass $lesson
     * @throws PhonicsException
     */
    public function __construct(stdClass $lesson)
    {
        // $start = Debug::startTimer();
        $this->lessonId = $lesson->lessonCode;
        $this->lessonName = $lesson->lessonName;
        $this->lessonKey = $lesson->lessonName;
        $this->script = 'Blending';
        $this->alternateNames = $this->decodeArray($lesson->alternateNames);
        $this->groupCode = $lesson->groupCode;
        $this->groupName = Groups::getInstance()->getGroupName($lesson->groupCode);
        $this->lessonDisplayAs = $lesson->lessonDisplayAs;

        $this->wordList = is_string($lesson->wordList) ? Util::csvStringToArray($lesson->wordList) : $lesson->wordList;
        $this->supplementalWordList = is_string($lesson->supplementalWordList) ? Util::csvStringToArray($lesson->supplementalWordList) : $lesson->supplementalWordList;
        $this->allWords = array_merge($this->wordList ?? [], $this->supplementalWordList ?? []);
        if (isset($lesson->contrastImages)) {
            $this->contrastImages = $this->decodeArray($lesson->contrastImages);
        } else if (isset($lesson->contrastList)) {
            $this->contrastImages = Util::csvStringToArray($lesson->contrastList);
        }

        $this->stretchList = is_string($lesson->stretchList) ? Util::stretchListToArray($lesson->stretchList) : $lesson->stretchList;
        $this->fluencySentences = $this->decodeArray($lesson->fluencySentences);
        $this->games = [];
        $this->book = $lesson->flipBook ?? '';
        $this->spinner = null;
        $spinnerObject = $this->decodeObject($lesson->spinner);
        if ($spinnerObject) {
            $this->spinner = new Spinner($spinnerObject->prefixList, $spinnerObject->vowel ?? '', $spinnerObject->suffixList ?? '');
        }

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
        $games = $this->decodeArray($lesson->games);
        foreach ($games as $game) {
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
        if ((new WarmupData())->exists($this->lessonId)) {
            $this->tabNames[] = 'warmup';
        }
        $this->tabNames = array_merge($this->tabNames, ['intro', 'write', 'practice']);
        if ($this->spinner) {
            $this->tabNames[] = 'spell';
        }
        $this->tabNames = array_merge($this->tabNames, ['mastery', 'fluency', 'test']);
        // if ($this->book) {
        //     $this->tabNames[] = 'book';
        // }

        $this->pronounceImage = Location::getPronounceImage($lesson->pronounceImage ?? '');
        $this->pronounceImageThumb = Location::getPronounceImageThumb($lesson->pronounceImage ?? '');

        $this->visible = $lesson->active != 'N';

        $this->wordLists = (null === $this->wordList) ? null : [];
        if ($this->tabNames && (null !== $this->wordList)) {
            foreach ($this->tabNames as $tabName) {
                if (in_array($tabName, ['practice', 'mastery', 'test'])) {
                    $this->wordLists[$tabName] = $this->makeWordLists($tabName);
                }
            }
        }
        // Debug::logElapsedTime($start, 'Lesson::__construct(' . $lesson->lessonName . ')');
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
            'wordLists'            => $this->wordLists, // map for each tab that needs it
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
            'notes'                => $this->notes
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
     *
     * @param string $tabName The tab we are building the word list for
     *
     * @return array a 3 x n array where n is 21 for practice tab and 9 for other tabs
     */
    private function makeWordLists(string $tabName): array
    {
        $isPractice = Util::contains_ci('prac', $tabName);
        $useSupplemental = (Util::contains_ci('test', $tabName ) || $isPractice);
        $arraySize = $isPractice ? 21 : 9;
        $hasSupplemental = not(empty($this->supplementalWordList));
        $initialWords = ($useSupplemental && $hasSupplemental) ? array_merge($this->wordList, $this->supplementalWordList) : $this->wordList;
        $words = $initialWords;
        if (not(is_array($words))) {
            LOG::error("No wordlist for tab $tabName in lesson {$this->lessonName}.");
        }
        while (count($words) < $arraySize) {
            $words = array_merge($words, $initialWords);
        }
        if (count($words) == $arraySize) {
            $offset = 0;
        } else {
            $offset = rand(0, count($words) - $arraySize);
        }
        $slice = array_slice($words, $offset, $arraySize);
        shuffle($slice);
        return $slice;
    }

    private function decodeArray(?string $json): array
    {
        return ($json != null) ? json_decode($json) : [];
    }

    private function decodeObject(?string $json): ?object
    {
        return ($json != null) ? json_decode($json) : null;
    }
}
