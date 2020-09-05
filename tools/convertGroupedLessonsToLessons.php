<?php

// using the compare.csv file as a base, this combines the information in the csv spreadsheet
// with the LessonInfo and BlendingInfo data

use ReadXYZ\Helpers\Util;
use ReadXYZ\Lessons\BlendingInfo;
use ReadXYZ\Lessons\CsvList;
use ReadXYZ\Lessons\LessonInfo;
use ReadXYZ\POPO\gamePOPO;
use ReadXYZ\POPO\LessonPOPO;
use ReadXYZ\POPO\SpellSpinner;

require dirname(__DIR__) . 'src/ReadXYZ/autoload.php';

function getNewLessonName(string $lessonName, array $csvMap): string
{
    $trimmedName = trim($lessonName);

    return $csvMap['realName'][$trimmedName] ?? $trimmedName;
}

function findCsvEntry($lessonName, $csvArray)
{
    foreach ($csvArray as &$item) {
        if ($item['newLessonName'] == $lessonName) {
            return $item;
        }
    }

    return false;
}

/**
 * @param string $blendingLessonName
 *
 * @return bool|string[]
 */
function getFluencyLessonsFromDatabase(string $blendingLessonName)
{
    $conn = Util::dbConnect();
    if ($conn) {
        $query = "SELECT * FROM abc_testcontent WHERE lessonKey = '$blendingLessonName' ";
        $queryResult = mysqli_query($conn, $query);
        if ($queryResult) {
            $res = mysqli_fetch_array($queryResult);
            if ($res) {
                $pattern = ['#<p[^>]+>([^<]+)</p>#', '/\n/' . '/\r/'];
                $strippedResult = preg_replace($pattern, '', $res['content']);
                $strippedResult = str_replace('.', '.,', $strippedResult);
                $array = explode(',', $strippedResult);
                array_pop($array);

                return $array;
            }
        }
    }

    return false;
}

try {
    $csvInfo = CsvList::getInstance();
} catch (Throwable $ex) {
    exit($ex->getMessage());
}

Util::fakeLogin();
$blendingInfo = BlendingInfo::getInstance();
$lessonInfo = LessonInfo::getInstance();
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
    $lesson->lessonDisplayAs = Util::addSoundClassToLessonName($realName);
    $lesson->alternateNames = $csvInfo->getAlternateNames($realName);
    if (false == $newLesson) {
        $blendingLesson = $blendingInfo->findLesson($lesson->alternateNames);
        $foundLessonName = $lessonInfo->findLessonName($lesson->alternateNames); // sets current lesson
        if ($foundLessonName) {
            $oldLesson = $lessonInfo->getCurrentLesson();
        }
    }
    $lesson->lessonKey = 'Blending.' . $realName;
    $lesson->groupId = trim($csvLesson['Group']);
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
        } elseif ($blendingLesson ?? false) {
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
    } elseif (isset($blendingLesson) && $blendingLesson) {
        $result = getFluencyLessonsFromDatabase($blendingLesson['lessonName']);
        if (false !== $result) {
            $lesson->fluencySentences = $result;
        }
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
                    exit("No iframe for $foundLessonName for game " . $game['game']);
                }
                $url = preg_replace($urlPattern, '$1', $iframe);
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
$outputFile = Util::getReadXyzSourcePath('resources/unifiedLessons.json');
file_put_contents($outputFile, $json);
// $tabTypes = new TabTypesPOPO();
// $tabTypes->write(Util::getReadXyzSourcePath('resources/tabTypes.json'));
// $gameTypes = new GameTypesPOPO();
// $gameTypes->write(Util::getReadXyzSourcePath('resources/gameTypes.json'));
