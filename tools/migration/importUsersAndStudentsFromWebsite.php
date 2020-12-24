<?php

use App\ReadXYZ\Data\OldStudentData;
use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Data\TrainersData;
use App\ReadXYZ\Data\WordMasteryData;
use App\ReadXYZ\Enum\JsonDecode;
use App\ReadXYZ\Enum\Regex;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;

require '../autoload.php';
error_reporting(E_ALL);

function processOldStudentRecord(OldStudentData $studentData, array $record, array $wordMastery)
{
    $cargo       = unserialize($record['cargo']);
    $cargoInfo   = $studentData->getCargoInfo($cargo);
    $trainer     = $record['trainer1'];
    $parseResult = Regex::parseCompositeEmail($trainer);

    $words          = $wordMastery[$record['studentid']] ?? [];
    $trainerEmail   = $parseResult->success ? $parseResult->email : $trainer;
    $hasSomeMastery = false;
    foreach ($cargoInfo->mastery as $lesson => $info) {
        if ($info->mastery != 'none') {
            $hasSomeMastery = true;
            break;
        }
    }
    return (object)[
        'studentId'      => $record['studentid'],
        'studentName'    => ucfirst($record['StudentName']),
        'trainerEmail'   => $trainerEmail,
        'compositeEmail' => $record['trainer1'],
        'currentLesson'  => $cargoInfo->currentLesson,
        'lessonMastery'  => $cargoInfo->mastery,
        'usableData'     => $cargoInfo->usableData,
        'validEmail'     => $parseResult->success,
        'wordMastery'    => $words,
        'hasSomeMastery' => $hasSomeMastery,
        'isComposite'    => ($trainerEmail != $trainer)
    ];
}

// contains studentid, cargo, StudentName, trainer1
$shell       = JsonDecode::decodeFile('abc_Student.json', JsonDecode::RETURN_ASSOCIATIVE_ARRAY);
$oldStudents = $shell[2]['data'];

// contains studentID, word
$shell          = JsonDecode::decodeFile('abc_usermastery.json', JsonDecode::RETURN_ASSOCIATIVE_ARRAY);
$oldWordMastery = $shell[2]['data'];
$wordMastery    = [];
foreach ($oldWordMastery as $record) {
    $id   = $record['studentID'];
    $word = $record['word'];
    if ( ! isset($wordMastery[$id])) {
        $wordMastery[$id] = [];
    }
    $wordMastery[$id][] = $word;
}

$oldStudentData = new OldStudentData();

$students    = [];
$trainerData = new TrainersData();
$studentData = new StudentsData();
$wordMasteryData = new WordMasteryData();

foreach ($oldStudents as $student) {
    $studentInfo = processOldStudentRecord($oldStudentData, $student, $wordMastery);
    if ( ! $studentInfo->usableData) {
        continue;
    }
    $newStudentId            = Util::oldUniqueIdToNew($studentInfo->studentId);
    $students[$newStudentId] = $studentInfo;
}


    $count = 0;
    $wordCount = 0;
try {
    foreach ($students as $studentCode => $info) {
        $userName = $info->trainerEmail;
        if ( ! $trainerData->exists($userName)) {
            $result = $trainerData->add($userName);
            if ($result->failed()) {throw new PhonicsException($result->getErrorMessage());}
        }
        if ( ! $studentData->doesStudentExist($studentCode)) {
            $result =$studentData->add($info->studentName, $userName, $studentCode);
            if ($result->failed()) {throw new PhonicsException($result->getErrorMessage());}
        }
        $wordMasteryData->add($studentCode, $info->wordMastery);
        $count++;
        $wordCount += count($info->wordMastery);
    }
} catch (PhonicsException $ex) {
    $previous = $ex->getPrevious();
    $prevMessage = $previous->getMessage() ?? '';
    printf("%s\n%s\n%s\n", $ex->getMessage(), $prevMessage, $ex->getTraceAsString());
    exit(1);
}
printf("%d records processed. %d mastered words entered.", $count, $wordCount);

