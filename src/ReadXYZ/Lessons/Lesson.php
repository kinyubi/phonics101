<?php

namespace App\ReadXYZ\Lessons;

use App\ReadXYZ\CSV\CSV;
use App\ReadXYZ\Helpers\Location;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\JSON\GameTypesJson;
use App\ReadXYZ\JSON\WarmupsJson;
use App\ReadXYZ\Models\Log;
use stdClass;

class Lesson
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
    public string       $lessonId;
    public string       $lessonName;
    public array        $alternateNames;
    public string       $groupName;
    public string       $groupCode;
    public ?array       $stretchList;
    /** @var string[] */
    public ?array       $fluencySentences;
    /** @var stdClass[] */
    public ?array       $games = [];
    /** @var string[] */
    public ?array       $tabNames;
    public ?Spinner     $spinner;
    public string       $pronounceImage; // plain array
    public string       $pronounceImageThumb;
    public string       $chain;
    /** @var string[] */
    public ?array       $contrastImages;
    public int          $ordinal = 0;
    public ?array       $wordLists;
    public array        $notes;
    public array        $allWords;
    public string       $soundLetters;
    public string       $book;
    private string      $lessonCode;
    private ?array      $wordList;
    private ?array      $supplementalWordList;


    /**
     * Lesson constructor. The input is a stdClass object from the abc_lessons table in readxyz0_phonics database
     *
     * @param stdClass|null $lesson
     */
    public function __construct(?stdClass $lesson = null)
    {
        if ($lesson == null) {
            return;
        } // handle the caching instance
        // $start = Debug::startTimer();
        $this->lessonId       = $lesson->lessonId;
        $this->lessonCode     = $lesson->lessonId;
        $this->lessonName     = $lesson->lessonName;
        $this->alternateNames = $lesson->alternateNames;
        $this->groupCode      = $lesson->groupCode;
        $this->ordinal        = $lesson->ordinal ?? 0;
        $this->wordList             = CSV::listToArray($lesson->wordList) ?? [];
        $this->supplementalWordList = CSV::listToArray($lesson->supplementalWordList ?? '') ?? [];
        $this->allWords             = array_merge($this->wordList, $this->supplementalWordList);
        if (isset($lesson->contrastImages)) {
            $array = $lesson->contrastImages;
            if ($array) {
                $this->contrastImages = $array;
            }
        }
        $this->chain            = $lesson->chain ?? '';
        if ($this->chain) {
            $this->chain = str_replace(',', '   ', $this->chain);
            //$this->chain = '<p>' . $this->chain . '</p>';
        }
        $this->soundLetters     = $lesson->soundLetters ?? 'abcdefghijklmnopqrstubwxyz';
        $this->stretchList      = is_string($lesson->stretchList) ? CSV::stretchListToArray($lesson->stretchList) : $lesson->stretchList;
        $this->fluencySentences = $lesson->fluencySentences;
        $this->games            = [];
        $this->book             = $lesson->book ?? '';
        $this->spinner          = null;
        $spinnerObject          = $lesson->spinner ?? null;
        if ($spinnerObject) {
            $this->spinner = new Spinner($spinnerObject->prefixList, $spinnerObject->vowel ?? '', $spinnerObject->suffixList ?? '');
        }

        // get the universal games added to the lesson
        $gameTypesJson  = GameTypesJson::getInstance();
        $universalGames = $gameTypesJson->getUniversal();
        for ($i = 0; $i < count($universalGames); $i++) {
            $this->games[$universalGames[$i]->belongsOnTab][] = $universalGames[$i];
        }

        // add the games in abc_lessons.json
        $games = $lesson->games;
        foreach ($games as $game) {
            if (empty($game->url)) {
                continue;
            }
            $gameType = $gameTypesJson->get($game->gameTypeId);
            if ( ! $gameType) {
                continue;
            }
            $url = $game->url;
            $gameType->url                          = $url;
            $tab = $gameType->belongsOnTab;
            $this->games[$tab][] = clone $gameType;
        }
        $this->tabNames = [];
        $warmupJson = WarmupsJson::getInstance();
        if ($warmupJson->exists($this->lessonId)) {
            $this->tabNames[] = 'warmup';
        }
        $this->tabNames = array_merge($this->tabNames, ['intro', 'write', 'practice']);
        if ($this->spinner) {
            $this->tabNames[] = 'spell';
        }
        $this->tabNames = array_merge($this->tabNames, ['mastery', 'fluency', 'test', 'email']);
        // if ($this->book) {
        //     $this->tabNames[] = 'book';
        // }

        $this->pronounceImage      = Location::getPronounceImage($lesson->pronounceImage ?? '');
        $this->pronounceImageThumb = Location::getPronounceImageThumb($lesson->pronounceImage ?? '');

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

// ======================== STATIC METHODS =====================
    public static function __set_state($array)
    {
        $lesson                       = new Lesson();
        $lesson->lessonId             = $array['lessonId'];
        $lesson->lessonName           = $array['lessonName'];
        $lesson->lessonCode           = $lesson->lessonId;
        $lesson->alternateNames       = $array['alternateNames'];
        $lesson->groupCode            = $array['groupCode'];
        $lesson->stretchList          = $array['stretchList'];
        $lesson->fluencySentences     = $array['fluencySentences'];
        $lesson->games                = $array['games'];
        $lesson->tabNames             = $array['tabNames'];
        $lesson->spinner              = $array['spinner'];
        $lesson->pronounceImage       = $array['pronounceImage'];
        $lesson->pronounceImageThumb  = $array['pronounceImageThumb'];
        $lesson->ordinal              = $array['ordinal'];
        $lesson->wordLists            = $array['wordLists'];
        $lesson->allWords             = $array['allWords'];
        $lesson->book                 = $array['book'];
        $lesson->wordList             = $array['wordList'];
        $lesson->supplementalWordList = $array['supplementalWordList'];
        $lesson->soundLetters         = $array['soundLetters'] ?? '';
        return $lesson;
    }

// ======================== PRIVATE METHODS =====================


    public function getTicTacToeWords(): array
    {
        $initialWords = $this->wordList;
        $words        = $initialWords;
        while (count($words) < 9) {
            $words = array_merge($words, $initialWords);
        }
        $slice = array_slice($words, 0, 9);
        shuffle($slice);
        return $slice;
    }

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
        $isPractice      = Util::contains_ci('prac', $tabName);
        $useSupplemental = (Util::contains_ci('test', $tabName) || $isPractice);
        $arraySize       = $isPractice ? 21 : 9;
        $initialWords    = ($useSupplemental) ? $this->allWords : $this->wordList;
        $words           = $initialWords;
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
        shuffle($words);
        $slice = array_slice($words, $offset, $arraySize);
        shuffle($slice);
        return $slice;
    }
}
