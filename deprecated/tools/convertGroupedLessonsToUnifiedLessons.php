<?php

// using the compare.csv file as a base, this combines the information in the csv spreadsheet
// with the LessonInfo and BlendingInfo data

use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Lessons\OldBlendingInfo;
use App\ReadXYZ\Lessons\CsvList;
use App\ReadXYZ\Lessons\OldLessonInfo;
use App\ReadXYZ\POPO\gamePOPO;
use App\ReadXYZ\POPO\LessonPOPO;
use App\ReadXYZ\POPO\SpellSpinner;

require 'autoload.php';

function getNewLessonName(string $lessonName, array $csvMap): string
{
    $trimmedName = trim($lessonName);

    return $csvMap['realName'][$trimmedName] ?? $trimmedName;
}

try {
    $csvInfo = CsvList::getInstance();
} catch (Throwable $ex) {
    exit($ex->getMessage());
}

$blendingInfo = OldBlendingInfo::getInstance();
$lessonInfo = OldLessonInfo::getInstance();
$oldLessons = $lessonInfo->getAllLessons();
$csvLessons = $csvInfo->getArray();

$lessons = ['lessons' => ['blending' => []]];
$urlPattern = '/^.*(http.+templateId=\d+).*$/';

foreach ($csvLessons as $csvLesson) {
    $deletedLesson = 'TRUE' === $csvLesson['Delete'];
    $futureLesson = 'TRUE' === $csvLesson['Future'];
    if ($deletedLesson or $futureLesson) {
        continue;
    }
    $newLesson = 'TRUE' === $csvLesson['New'];
    $lesson = new LessonPOPO();
    $realName = trim($csvLesson['NewLessonName']);

    $lesson->lessonId = trim($csvLesson['ID']);
    $lesson->lessonName = $realName;
    $lesson->visible = true;
    $lesson->lessonDisplayAs = $realName;
    $lesson->alternateNames = $csvInfo->getAlternateNames($realName);
    if (false == $newLesson) {
        $blendingLesson = $blendingInfo->findLesson($lesson->alternateNames);
        $foundLessonName = $lessonInfo->findLessonName($lesson->alternateNames); // sets current lesson
        if ($foundLessonName) {
            $oldLesson = $lessonInfo->getCurrentLesson();
        }
    } else {
        $blendingLesson = false;
    }
    $lesson->lessonKey = 'Blending.' . $realName;
    $lesson->groupName = trim($csvLesson['Group']);
    $lesson->script = 'Blending';
    $lesson->wordList = $csvLesson['PrimaryWordList'];
    $lesson->supplementalWordList = $csvLesson['SupplementalWordList'];
    if ($csvLesson['StretchList']) {
        $lesson->stretchList = $csvLesson['StretchList'];
    } else {
        $lesson->stretchList = $oldLesson['stretchList'] ?? '';
    }

    // Look for a spinner in all the known places
    if ($csvLesson['Prefix'] or $csvLesson['Vowel'] or $csvLesson['Suffix']) {
        $lesson->spinner = new SpellSpinner($csvLesson['Prefix'] ?? '', $csvLesson['Vowel'] ?? '', $csvLesson['Suffix'] ?? '');
    } else {
        if (isset($oldLesson['spinner'])) {
            $spinner = $oldLesson['spinner'];
            $lesson->spinner = new SpellSpinner($spinner['prefixes'], $spinner['vowel'], $spinner['suffix']);
        } elseif ($blendingLesson ) {
            $spinner = $blendingInfo->getSpinner($blendingLesson['lessonKey']);
            if ($spinner) {
                $lesson->spinner = new SpellSpinner($spinner['prefixes'], $spinner['vowel'], $spinner['suffix']);
            } else {
                $lesson->spinner = null;
            }
        } else {
            $lesson->spinner = null;
        }
    }

    // Look for fluency in all the known places
    if ($csvLesson['Fluency']) {
        $lesson->fluencySentences = explode(',', $csvLesson['Fluency']);
    } elseif (isset($oldLesson['fluency'])) {
        $lesson->fluencySentences = $oldLesson['fluency'];
    }
    // SideImage, ContrastImage and games are only available for existing lessons
    if (not($newLesson)) {
        if (isset($oldLesson['sideImage'])) {
            $lesson->pronounceImage = $oldLesson['sideImage'];
        }
        if (isset($oldLesson['contrastImages'])) {
            $lesson->contrastImages = $oldLesson['contrastImages'];
        }
        if (isset($oldLesson['games'])) {
            foreach ($oldLesson['games'] as $game) {
                $iframe = $game['iframe'] ?? '';
                if (!$iframe) {
                    printf("No iframe for $foundLessonName for game " . $game['game']. PHP_EOL);
                    $url = '/404.html';
                } else {
                    $url = preg_replace($urlPattern, '$1', $iframe);
                }
                $lesson->games[] = new gamePOPO($game['game'], $url);
            }
        }
    }

    $tabs = [];
    if ($lesson->stretchList) {
        $tabs[] = 'stretch';
    }
    array_push($tabs, 'words', 'practice');
    if (null != $lesson->spinner) {
        $tabs[] = 'spinner';
    }
    $tabs[] = 'mastery';
    if ($lesson->fluencySentences) {
        $tabs[] = 'fluency';
    }
    $tabs[] = 'test';
    $lesson->tabs = $tabs;
    $lessons['lessons']['blending'][$realName] = $lesson;
}

$json = json_encode($lessons, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$outputFile = Util::getReadXyzSourcePath('resources/unifiedLessons_candidate.json');
file_put_contents($outputFile, $json);
// $tabTypes = new TabTypesPOPO();
// $tabTypes->write(Util::getReadXyzSourcePath('resources/tabTypes.json'));
// $gameTypes = new GameTypesPOPO();
// $gameTypes->write(Util::getReadXyzSourcePath('resources/gameTypes.json'));
