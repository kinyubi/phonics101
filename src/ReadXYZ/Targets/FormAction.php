<?php


namespace App\ReadXYZ\Targets;


use App\ReadXYZ\Data\StudentLessonsData;
use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Data\TrainersData;
use App\ReadXYZ\Data\WordMasteryData;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\Log;
use App\ReadXYZ\Models\RouteMe;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Rest\Membership;
use App\ReadXYZ\Twig\LessonListTemplate;
use App\ReadXYZ\Twig\LessonTemplate;
use App\ReadXYZ\Twig\LoginTemplate;
use App\ReadXYZ\Helpers\PhonicsException;

class FormAction
{
    private Session $session;
    private int     $previousErrorReporting;

    public function __construct()
    {
        if (Util::isLocal()) {
            $this->previousErrorReporting =  error_reporting(E_ALL | E_STRICT);
        }
        $this->session = new Session();
    }

    public function __destruct()
    {
        if (Util::isLocal()) {
            error_reporting($this->previousErrorReporting);
        }
    }

    /**
     * processes form generated by login.html.twig in LoginTemplate class
     * @throws PhonicsException on ill-formed SQL
     */
    public function loginHandler(): void
    {
        $this->session->clearSession();
        $userName = $_POST['username'] ??  '';
        $password = $_POST['password'] ??  '';

        if (empty($userName) or empty($password)) {
            (new LoginTemplate())->display('Username and password must both be provided.');
            exit;
        }
        // $user: s2Member stdClass object
        $user = (new Membership())->getUser($userName);
        $s2students = $user->students; //string[]
        $studentData = new StudentsData();
        $ourStudents = $studentData->getMapOfStudentsForUser($user->userEmail);
        $ourStudentNames = array_keys($ourStudents);
        foreach ($s2students as $studentName) {
            if (!in_array($studentName, $ourStudentNames)) {
                $studentData->add($studentName, $userName);
            }
        }
        if (!$user->valid) {
            (new LoginTemplate())->display('Invalid user name or password.');
            exit;
        }
        $this->session->updateUser($userName);
        RouteMe::autoLoginDisplay();
    }

    /**
     * @param array $postParameters
     * @param array $routeParts
     * @throws PhonicsException on ill-formed SQL
     */
    public function lessonSelectionHandler(array $postParameters, array $routeParts): void
    {
        $lessonName     = $routeParts[0] ?? $postParameters['lessonName'] ?? $this->session->getCurrentLessonName() ?? '';
        $initialTabName = $routeParts[1] ?? $postParameters['initialTabName'] ?? '';
        (new Session())->updateLesson($lessonName);
        (new LessonTemplate($lessonName, $initialTabName))->display();
    }

    /**
     * @param $studentCode
     * @throws PhonicsException on ill-formed SQL
     */
    public function studentSelectionHandler($studentCode): void
    {
        if (empty($studentCode)) {
            throw new PhonicsException('You should not arrive here without student id set.');
        }
        $this->session->updateStudent($studentCode);

        $lessonList = new LessonListTemplate();
        $lessonList->display();
    }

    /**
     * @throws PhonicsException on ill-formed SQL
     */
    public function timersHandler(): void
    {
        if (!$this->session->hasLesson()) {
            throw new PhonicsException('Cannot update user mastery without an active lesson.');
        }
        $source = $_POST['source'] ?? 'unknown';
        $seconds = intval($_POST['seconds'] ?? '0');
        $timeStamp = intval($_POST['timestamp'] ?? '0');
        $tab = ('fluency' == $source) ? 'fluency' : 'test';
        $lessonName = $this->session->getCurrentLessonName();
        $lessonTemplate = new LessonTemplate($lessonName, $tab);
        $studentLessonData = new StudentLessonsData();
        if (('fluency' == $source) || ('test' == $source)) {
            $studentLessonData->updateTimedTest($source, $seconds, $timeStamp);
            $lessonTemplate->display($source);
        } elseif ('testMastery' == $source) {
            // masteryType is 'Advancing' or 'Mastered'
            $masteryType = $_POST['masteryType'];
            $studentLessonData->updateMastery($masteryType);
            $lessonTemplate->display('test');

        } else {
            $message = "Call to timers.php with unrecognized source $source";
            Log::error($message);
            echo Util::redBox($message);
        }
    }

    /**
     * for processing mastery tab submit
     * @throws PhonicsException on ill-formed SQL
     */
    public function wordMasteryFormHandler(): void
    {
        if (!$this->session->hasLesson()) {
            throw new PhonicsException('Cannot update user mastery without an active lesson.');
        }
        $studentCode = $this->session->getStudentCode();
        $presentedWordList = $_POST['wordlist'];
        $masteredWords = $_POST['word1'] ?? [];
        $wordMasteryData = new WordMasteryData();
        $result = $wordMasteryData->update($studentCode, $presentedWordList, $masteredWords);

        if ($result->wasSuccessful()) {
            $this->sendResponse(200, 'Update successful');
        } else {
            $msg = $result->getErrorMessage();
            Log::error($msg);
            $this->sendResponse(500, $msg);
        }
    }

    /**
     * @param int $http_code the http code we want the response to send
     * @param string $msg the message we want the response to return (default: OK)
     */
    protected function sendResponse(int $http_code = 200, string $msg = 'OK'): void
    {
        header('Content-Type: application/json');
        http_response_code($http_code);
        echo json_encode(['code' => $http_code, 'msg' => $msg]);
    }
}
