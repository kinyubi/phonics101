<?php


/**
 * Input file abc_Student.json is created by running the following query on readxyz0_1 from CPanel in readxyz.org:
 *  SELECT * FROM abc_Student WHERE lastupdate > 1540000000 AND StudentName IS NOT NULL AND trainer1 IN (SELECT UserName FROM abc_Users);
 */

namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Data\LessonsData;
use App\ReadXYZ\Data\OldStudentData;
use App\ReadXYZ\Data\StudentLessonsData;
use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Data\TrainersData;
use App\ReadXYZ\Enum\Regex;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Rest\Membership;

class OldStudents extends AbstractJson
{

    private array $students = [];
    private array $trainers;
    /**
     * builds an object with the student data we'll need for abc_students, abc_trainers and abc_studentLesson
     * abcStudentsFromOldStudents constructor.
     * @throws PhonicsException
     */
    public function __construct()
    {
        parent::__construct('abc_Students.json', 'studentId');
        $oldStudents = $this->importDataAsAssociativeArray();

        $trainers = [];
        foreach ($oldStudents as $student) {
            $studentInfo = $this->processOldStudentRecord($student);
            if ( ! $studentInfo->usableData) {
                continue;
            }
            $newStudentId            = Util::oldUniqueIdToNew($studentInfo->studentId);
            $this->students[$newStudentId] = $studentInfo;
            $trainers[] = $studentInfo->trainerEmail;
        }
        $this->trainers = array_values(array_unique($trainers));
    }

    public static function getInstance()
    {
        return parent::getInstanceBase(__CLASS__);
    }

    /**
     * Looks at each students cargo record and extracts information like word mastery and lesson mastery.
     * Also get the trainer and student information needed to be able to populate abc_students, atc_trainers,
     * abc_student_lessons, and abc_word_mastery.
     * @param array $record
     * @return object
     */
    private function processOldStudentRecord(array $record)
    {
        $cargo       = unserialize($record['cargo']);
        $cargoInfo   = (new OldStudentData())->getCargoInfo($cargo);
        $trainer     = $record['trainer1'];
        $uuid        =
        $parseResult = Regex::parseCompositeEmail($trainer);

        $trainerEmail   = $parseResult->success ? $parseResult->email : $trainer;
        $hasSomeMastery = false;
        foreach ($cargoInfo->mastery as $lesson => $info) {
            if ($info->mastery != 'none') {
                $hasSomeMastery = true;
                break;
            }
        }
        $studentName = ucfirst($record['StudentName']);
        $compositeEmail = $trainerEmail . '-' . $studentName;
        return (object)[
            'studentId'      => $record['studentid'],
            'studentName'    => $studentName,
            'trainerEmail'   => $trainerEmail,
            'compositeEmail' => $compositeEmail,
            'currentLesson'  => $cargoInfo->currentLesson,
            'lessonMastery'  => $cargoInfo->mastery,
            'usableData'     => $cargoInfo->usableData,
            'validEmail'     => $parseResult->success,
            'isComposite'    => ($trainerEmail != $trainer),
            'hasLessonMastery' => $hasSomeMastery
        ];
    }

    /**
     * Creates abc_trainers from abc_Students.json, iff abc_trainers was empty
     * @throws PhonicsException
     */
    public function populateAbcTrainers(): void
    {
        $trainersTable = new TrainersData();
        if (0 != $trainersTable->getCount()) {
            throw new PhonicsException("This function is only available is abc_trainers is empty.");
        }
        foreach ($this->trainers as $userName) {
            try {
                $trainersTable->add($userName);
            } catch (PhonicsException $e) {
                throw new PhonicsException("Unable to add trainer $userName.", 0, $e);
            }
        }
    }

    /**
     * Creates abc_students from abc_Students.json, iff abc_students was empty
     * @throws PhonicsException
     */
    public function populateAbcStudents(): void
    {
        $studentData     = new StudentsData();
        if (0 != $studentData->getCount()) {
            throw new PhonicsException("This function is only available is abc_students is empty.");
        }
        foreach ($this->students as $code => $info) {
            $result = $studentData->add($info->studentName, $info->trainerEmail, $code);
            if ($result->failed()) {
                throw new PhonicsException($result->getErrorMessage());
            }
        }
    }

    /**
     * @throws PhonicsException
     */
    public function populateAbcStudentLessons(): void
    {
        $count = (new StudentLessonsData())->getCount();
        if (0 != $count) {
            throw new PhonicsException("This function is only available is abc_student_lesson is empty.");
        }
        // we need each timestamp to be different. If timestamp matches the previous entry if will be ignored.
        // because it will be assumed to be a resubmission.
        $timeStamp  = time() - 10000;
        $lessonData = new LessonsData();
        $count      = 0;
        foreach ($this->students as $studentCode => $info) {
            if ($info->usableData && $info->hasSomeMastery) {
                foreach ($info->lessonMastery as $lessonName => $masteryInfo) {
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
                }
            }
        }
    }

}
