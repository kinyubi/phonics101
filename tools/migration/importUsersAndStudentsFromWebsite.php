<?php


/*
populates abc_word_mastery and abc_student_lessons

    abc_student.json - created by doing a json save of the query on readxyz0_1 at readxyz.org:
        SELECT * FROM abc_Student
        WHERE lastupdate > 1540000000 AND StudentName IS NOT NULL AND trainer1 IN (SELECT UserName FROM abc_Users);

    abc_usermastery.json - created by doing a json save of the query on readxyz0_1 at readxyz.org:
        SELECT * FROM abc_usermastery WHERE studentID IN (SELECT studentid FROM abc_Student
        WHERE lastupdate > 1540000000 AND StudentName IS NOT NULL AND trainer1 N (SELECT UserName FROM abc_Users));
 */


use App\ReadXYZ\Data\OldStudentData;
use App\ReadXYZ\Data\StudentLessonsData;
use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Data\TrainersData;
use App\ReadXYZ\Data\WordMasteryData;
use App\ReadXYZ\Enum\JsonDecode;
use App\ReadXYZ\Enum\Regex;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\JSON\LessonsJson;

require '../autoload.php';


/**
 * Looks at each students cargo record and extracts information like word mastery and lesson mastery.
 * Also get the trainer and student information needed to be able to populate abc_students, atc_trainers,
 * abc_student_lessons, and abc_word_mastery.
 * @param array $record
 * @param array $wordMastery
 * @return object
 */
function processOldStudentRecord(array $record, array $wordMastery)
{
    $cargo       = unserialize($record['cargo']);
    $cargoInfo   = (new OldStudentData())->getCargoInfo($cargo);
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

function processOldWordMastery(): array
{
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
    return $wordMastery;
}

function collectAllOldStudentData(array $wordMastery): array
{
    $shell          = JsonDecode::decodeFile('abc_Student.json', JsonDecode::RETURN_ASSOCIATIVE_ARRAY);
    $oldStudents    = $shell[2]['data'];
    $oldStudentData = new OldStudentData();
    $students       = [];

    foreach ($oldStudents as $student) {
        $studentInfo = processOldStudentRecord($student, $wordMastery);
        if ( ! $studentInfo->usableData) {
            continue;
        }
        $newStudentId            = Util::oldUniqueIdToNew($studentInfo->studentId);
        $students[$newStudentId] = $studentInfo;
    }
    return $students;
}

/**
 * updates the student lesson records, creating new records as needed.
 * @param array $students
 * @return int  number of student lesson records created.
 */
function populateStudentLessonsData(array $students): int
{
    // we need each timestamp to be different. If timestamp matches the previous entry if will be ignored.
    // because it will be assumed to be a resubmission.
    $timeStamp  = time() - 10000;
    $count      = 0;
    foreach ($students as $studentCode => $info) {
        if ($info->usableData && $info->hasSomeMastery) {
            foreach ($info->lessonMastery as $lessonName => $masteryInfo) {
                try {
                    $lessonCode = LessonsJson::getInstance()->getLessonCode($lessonName);

                    $studentLessonData = new StudentLessonsData($studentCode, $lessonCode);
                    if ($masteryInfo->mastery != 'none') {
                        $studentLessonData->updateMastery($masteryInfo->mastery);
                    }
                    if (is_array($masteryInfo->fluencyCurves)) {
                        $studentLessonData->clearTimedTest('fluency');
                        foreach ($masteryInfo->fluencyCurves as $seconds) {
                            $studentLessonData->updateTimedTest('fluency', $seconds, $timeStamp++);
                        }
                    }
                    if (is_array($masteryInfo->testCurves)) {
                        $studentLessonData->clearTimedTest('test');
                        foreach ($masteryInfo->testCurves as $seconds) {
                            $studentLessonData->updateTimedTest('test', $seconds, $timeStamp++);
                        }
                    }
                    $count++;
                } catch (Throwable $e) {
                    echo $e->getMessage();
                    echo $e->getTraceAsString();
                    exit;
                }
            }
        }
    }
    return $count;
}

function populateWordMasteryData(array $students): int
{
    $trainerData     = new TrainersData();
    $studentData     = new StudentsData();
    $wordMasteryData = new WordMasteryData();
    $wordCount       = 0;
    try {
        foreach ($students as $studentCode => $info) {
            $userName = $info->trainerEmail;
            if ( ! $trainerData->exists($userName)) {
                $result = $trainerData->add($userName);
                if ($result->failed()) {
                    throw new PhonicsException($result->getErrorMessage());
                }
            }
            if ( ! $studentData->doesStudentExist($studentCode)) {
                $result = $studentData->add($info->studentName, $userName, $studentCode);
                if ($result->failed()) {
                    throw new PhonicsException($result->getErrorMessage());
                }
            }
            $wordMasteryData->add($studentCode, $info->wordMastery);
            $wordCount += count($info->wordMastery);
        }
        return $wordCount;
    } catch (PhonicsException $ex) {
        $previous    = $ex->getPrevious();
        $prevMessage = $previous->getMessage() ?? '';
        printf("%s\n%s\n%s\n", $ex->getMessage(), $prevMessage, $ex->getTraceAsString());
        exit(1);
    }
}

error_reporting(E_ALL);
// contains studentid, cargo, StudentName, trainer1
$array          = JsonDecode::decodeFile('abc_Student.json', JsonDecode::RETURN_ASSOCIATIVE_ARRAY);
$objects          = JsonDecode::decodeFile('abc_Student.json', JsonDecode::RETURN_STDCLASS);

$wordMastery = processOldWordMastery();
$students    = collectAllOldStudentData($wordMastery);

$studentLessonCount = populateStudentLessonsData($students);
$wordMasteryCount = populateWordMasteryData($students);

printf("%d student lessons entered.", $studentLessonCount);
printf("%d mastered words entered.", $wordMasteryCount);

