<?php


namespace App\ReadXYZ\Data;

use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Enum\DbVersion;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Lessons\Lessons;
use stdClass;
use App\ReadXYZ\Helpers\PhonicsException;

/**
 * Class StudentData
 * @package App\ReadXYZ\Data
 * Provides routines to interact with abc_Students table
 */
class OldStudentData extends AbstractData
{
    public function __construct()
    {
        parent::__construct('abc_students', 'studentid', DbVersion::READXYZ0_1);
    }

    public function _create()
    {
        $query = <<<EOT
CREATE TABLE `abc_student` (
	`studentid` VARCHAR(32) NOT NULL,
	`cargo` TEXT NULL,
	`StudentName` VARCHAR(32) NULL DEFAULT NULL,
	`project` VARCHAR(32) NULL DEFAULT NULL,
	`trainer1` VARCHAR(64) NULL DEFAULT NULL,
	`trainer2` VARCHAR(32) NULL DEFAULT NULL,
	`trainer3` VARCHAR(32) NULL DEFAULT NULL,
	`created` INT(10) UNSIGNED NULL DEFAULT '0',
	`createdhuman` VARCHAR(32) NULL DEFAULT NULL,
	`lastupdate` INT(10) UNSIGNED NULL DEFAULT '0',
	`lastbackup` INT(10) UNSIGNED NULL DEFAULT '0',
	PRIMARY KEY (`studentid`)
) COMMENT='True name: abc_Student' COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $result = $this->db->queryStatement($query);
        if ($result->failed()) {
            throw new PhonicsException($this->db->getErrorMessage());
        }
    }

    /**
     * Gets the userId associated with a given user name. Returns empty string if not found
     * @param string $username
     * @param string $studentName
     * @return string the userId if found, otherwise the empty string
     */
    public function getStudentId(string $username, string $studentName): string
    {
        $query = "SELECT studentid FROM abc_Student WHERE StudentName = '$studentName' AND trainer1 = '$username'";
        $result = $this->db->queryAndGetScalar($query);
        if ($result->wasSuccessful()) return $result->getResult();
        throw new PhonicsException('Error: ' . $result->getErrorMessage() . '. ' . $query);
    }

    /**
     * Retrieve an array of studentId's
     * @return array
     */
    public function getAllStudentIds(): array
    {
        $query = 'SELECT studentid FROM abc_Student';
        $result = $this->db->queryAndGetScalarArray($query);
        return ($result->wasSuccessful()) ? $result->getResult() : [];
    }


    public function getStudentName(string $studentId): string
    {
        $query = 'SELECT StudentName FROM abc_Student WHERE studentid = ?';
        $statement = $this->db->getPreparedStatement($query);
        $statement->bind_param('s', $studentId);
        $statement->execute();
        $statement->bind_result($studentName);
        $statement->fetch();
        $statement->close();
        return $studentName ?? ''; // fetch returns null if nothing found
    }


    public function getStudents(string $username): array
    {
        $query = 'SELECT studentid, StudentName FROM abc_Student WHERE trainer1 = ?';
        $statement = $this->db->getPreparedStatement($query);
        $statement->bind_param('s', $username);
        $statement->execute();
        $result = $statement->get_result();
        $students = $result->fetch_all(MYSQLI_ASSOC);
        $statement->close();
        return $students;
    }

    /**
     * get all trainers that are assigned to student
     * @return array
     */
    public function getStudentTrainers(): array
    {
        $query = "SELECT trainer1, studentid, StudentName, cargo, 'trainer' AS trainerType  FROM abc_Student";
        return $this->throwableQuery($query, QueryType::STDCLASS_OBJECTS);
    }

    /**
     * @return string[] list of unique usernames
     * @throws PhonicsException on ill-formed SQL
     */
    public function getUniqueTrainers(): array
    {
        $query = "SELECT DISTINCT trainer1 FROM abc_student";
        return $this->throwableQuery($query, QueryType::SCALAR_ARRAY);
    }

    /**
     * validates that $userId is actually trainer1 for $studentId
     * @param string $studentId
     * @param string $userId
     * @return bool
     * @throws PhonicsException on ill-formed SQL
     */
    public function studentHasTeacher(string $studentId, string $userId): bool
    {
        $query = 'SELECT S.studentid FROM abc_Student as S ' .
                 'INNER JOIN abc_Users AS U ON S.trainer1 = U.UserName ' .
                 'WHERE S.studentid = ? AND U.uuid = ?';
        $statement = $this->db->getPreparedStatement($query);
        $statement->bind_param('ss', $studentId, $userId);
        $statement->execute();
        $statement->bind_result($returnedId);
        $statement->fetch();
        $statement->close();
        return ! empty($returnedId);
    }

    /**
     * @param string $lessonName
     * @param array $info
     * @return stdClass|null
     */
    private function harvestMastery(array $info)
    {
        $timesPresented = $info['timesPresented'] ?? 0;
        $val = $info['mastery'] ?? 0;
        $mastery = ($val > 1) ? 'mastered' : (($val == 1) ? 'advancing' : 'none');
        $fluencyCurves = array_values($info['learningCurve'] ?? []);
        $testCurves = array_values($info['testCurve'] ?? []);
        $lastPresented = Util::dbDate($info['lastPresented']) ?? '';
        return (object)[
            'timesPresented' => $timesPresented,
            'fluencyCurves'  => empty($fluencyCurves) ? null : $fluencyCurves,
            'testCurves'     => empty($testCurves) ? null : $testCurves,
            'mastery'        => $mastery,
            'lastPresented'  => $lastPresented
        ];
    }

    private function replaceExisting($existing, $new): bool
    {
        return ($new->mastery > $existing->mastery);
    }

    public function getData(string $studentId): ?stdClass
    {
        $query = "SELECT * FROM abc_student WHERE studentid='$studentId'";
        $result = $this->db->queryRecord($query);
        if ($result->failed()) {
            return null;
        }
        $data = $result->getResult();
        $serializedCargo = $data['cargo'];
        $cargo = unserialize($serializedCargo);
        $cargoInfo = $this->getCargoInfo($cargo);
        return (object)[
            'studentId'     => $data['studentid'],
            'studentName'   => $data['StudentName'],
            'trainer'       => $data['trainer1'],
            'currentLesson' => $cargoInfo->currentLesson,
            'lessonMastery' => $cargoInfo->mastery
        ];
    }

    public function getCargoInfo(array $cargo) {

        $currentLesson = Lessons::getInstance()->getRealLessonName($cargo['currentLesson'] ?? '');
        $lessonsData = [];
        $lessons = Lessons::getInstance();
        $lessonTypes = ['currentLessons', 'masteredLessons'];
        $usableMasteryDataFound = false;
        foreach ($lessonTypes as $lessonType) {
            if ($cargo[$lessonType]) {
                foreach ($cargo[$lessonType] as $lessonKey => $info) {
                    $lessonName = $lessons->getRealLessonName($lessonKey);
                    if (empty($lessonName)) {
                        continue;
                    }
                    $masteryData = $this->harvestMastery($info);
                    if (isset($lessonsData[$lessonName])) {
                        if ($masteryData->mastery > $lessonsData[$lessonName]->mastery) {
                            $lessonData[$lessonName] = $masteryData;
                            $usableMasteryDataFound = true;
                        }
                    } else {
                        $lessonsData[$lessonName] = $masteryData;
                        $usableMasteryDataFound = true;
                    }
                }
            }
        }
        return (object) [
            'currentLesson' => $currentLesson,
            'mastery' => $lessonsData,
            'usableData' => $usableMasteryDataFound
        ];
    }
}
