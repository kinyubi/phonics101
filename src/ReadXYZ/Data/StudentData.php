<?php


namespace App\ReadXYZ\Data;

use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Lessons\Lessons;
use stdClass;
use RuntimeException;

/**
 * Class StudentData
 * @package App\ReadXYZ\Data
 * Provides routines to interact with abc_Students table
 */
class StudentData extends AbstractData
{
    public function __construct()
    {
        parent::__construct('abc_students');
    }

    public function create()
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
            throw new RuntimeException($this->db->getErrorMessage());
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
        throw new RuntimeException('Error: ' . $result->getMessage() . '. ' . $query);
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
     * validates that $userId is actually trainer1 for $studentId
     * @param string $studentId
     * @param string $userId
     * @return bool
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
     * @param string $lessonKey
     * @param array $cargo
     * @return stdClass|null
     */
    private function harvestMastery(string $lessonName, array $info)
    {
        $timesPresented = $info['timesPresented'] ?? 0;
        $val = $info['mastery'] ?? 0;
        $mastery = ($val > 1) ? 'mastered' : (($val == 1) ? 'advancing' : 'none');
        $fluencyCurves = array_values($info['learningCurve'] ?? []);
        $testCurves = array_values($info['testCurve'] ?? []);
        $lastPresented = Util::dbDate($info['lastPresented']) ?? '';
        return (object)[
            'timesPresented' => min($timesPresented, 1),
            'fluencyCurves'  => $fluencyCurves,
            'testCurves'     => $testCurves,
            'mastery'        => $mastery,
            'lastPresented'  => $lastPresented
        ];
    }

    private function replaceExisting($existing, $new): bool
    {
        return ($new->mastery > $existing->mastery);
    }

    public function getData(string $studentId): stdClass
    {
        $query = "SELECT * FROM abc_student WHERE studentid='$studentId'";
        $result = $this->db->queryRecord($query);
        if ($result->failed()) {
            throw new \RuntimeException($result->getMessage());
        }
        $data = $result->getResult();
        $cargo = unserialize($data['cargo']);
        $currentLesson = Lessons::getInstance()->getRealLessonName($cargo['currentLesson'] ?? '');
        $lessonsData = [];
        $lessons = Lessons::getInstance();
        $lessonTypes = ['currentLessons', 'masteredLessons'];
        foreach ($lessonTypes as $lessonType) {
            if ($cargo[$lessonType]) {
                foreach ($cargo[$lessonType] as $lessonKey => $info) {
                    $lessonName = $lessons->getRealLessonName($lessonKey);
                    if (empty($lessonName)) {
                        continue;
                    }
                    $masteryData = $this->harvestMastery($lessonName, $info);
                    if (isset($lessonsData[$lessonName])) {
                        if ($masteryData->mastery > $lessonsData[$lessonName]->mastery) {
                            $lessonData[$lessonName] = $masteryData;
                        }
                    } else {
                        $lessonsData[$lessonName] = $masteryData;
                    }
                }
            }
        }

        return (object)[
            'studentId'     => $data['studentid'],
            'studentName'   => $data['StudentName'],
            'trainer'       => $data['trainer1'],
            'currentLesson' => $currentLesson,
            'lessonData'    => $lessonsData
        ];
    }
}
