<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\ActiveType;
use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Models\Session;

/**
 * Class Views implements the views in database readxyz0_phonics.
 * @package App\ReadXYZ\Data
 */
class Views
{
    private static Views $instance;
    private GeneralData $viewData;

    private function __construct()
    {
        $this->viewData = new GeneralData();
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Views();
        }

        return self::$instance;
    }

    /**
     * @param string $user
     * @return bool  true if given user is a known trainerCode or userName
     * @throws PhonicsException
     */
    public function doesTrainerHaveStudents(string $user): bool
    {
        $query = "SELECT * FROM vw_students_with_username WHERE trainerCode = '$user' OR userName = '$user'";
        return $this->viewData->throwableQuery($query, QueryType::EXISTS);
    }

    /**
     * @param string $user
     * @param string $student
     * @return bool  true if trainerCode/userName and studentCode/studentName are related
     * @throws PhonicsException
     */
    public function isValidStudentTrainerPair(string $user, string $student): bool
    {
        $where = "(studentCode = '$student' OR studentName = '$student') AND (userName = '$user' OR trainerCode = '$user')";
        $query = "SELECT * FROM vw_students_with_username WHERE $where";
        return $this->viewData->throwableQuery($query, QueryType::EXISTS);
    }

    /**
     * @param string $user trainerCode or userName
     * @return string[] an array of studentNames
     * @throws PhonicsException on ill-formed SQL
     */
    public function getStudentNamesForUser(string $user): array
    {
        // We user the user of the current session if no user specified.
        if (empty($user)) {
            $user = Session::getTrainerCode();
            if ( ! $user) {
                throw new PhonicsException("User cannot be empty when no user present in session.");
            }
        }
        $where = "(active = 'Y') AND (userName = '$user' OR trainerCode = '$user')";
        $query = "SELECT studentName FROM vw_students_with_username WHERE $where";
        return $this->viewData->throwableQuery($query, QueryType::SCALAR_ARRAY);
    }

    /**
     * @param string $user trainerCode or userName
     * @return array an associative array of studentName => studentCode
     * @throws PhonicsException on ill-formed SQL
     */
    public function getMapOfStudentsForUser($user = ''): array
    {
        // We user the user of the current session if no user specified.
        if (empty($user)) {
            $user = Session::getTrainerCode();
            if ( ! $user) {
                throw new PhonicsException("User cannot be empty when no user present in session.");
            }
        }
        $active = ActiveType::IS_ACTIVE;
        $where = "(active = '$active') AND (userName = '$user' OR trainerCode = '$user')";
        $query = "SELECT studentCode, studentName FROM vw_students_with_username WHERE $where";
        $students = $this->viewData->throwableQuery($query, QueryType::STDCLASS_OBJECTS);
        $studentMap = [];
        foreach ($students as $student) {$studentMap[$student->studentName] = $student->studentCode;}
        return $studentMap;
    }


}
