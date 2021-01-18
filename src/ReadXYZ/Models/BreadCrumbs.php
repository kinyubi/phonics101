<?php


namespace App\ReadXYZ\Models;


use App\ReadXYZ\Data\TrainersData;
use App\ReadXYZ\Enum\TrainerType;
use App\ReadXYZ\Helpers\PhonicsException;

class BreadCrumbs
{
    private ?object $trainer;
    private ?object $student;
    private array $breadCrumbs = [];

    /**
     * BreadCrumbs constructor.
     * @throws PhonicsException
     */
    public function __construct()
    {
        $this->student = Session::getStudentObject();
        $this->trainer = Session::getUserObject();

        if ($this->trainer) {
            if (! isset($this->trainer->trainerType)) {
                $this->trainer->trainerType = (new TrainersData())->getTrainerType($this->trainer->trainerCode);
            }
            if ($this->trainer->trainerType == TrainerType::ADMIN) {
                $this->breadCrumbs[] = ['text' => 'Admin', 'link' => '#'];
            } elseif ($this->trainer->trainerType == TrainerType::STAFF) {
                $this->breadCrumbs[] = ['text' => 'Staff', 'link' => '#'];
            }
            if ($this->trainer->studentCt== 1) {
                $this->breadCrumbs[] = ['text' => $this->student->studentName, 'link' => '#'];
            } elseif ($this->trainer->studentCt > 1) {
                $this->breadCrumbs[] = ['text' => 'students', 'link' => '/studentlist'];
                $this->breadCrumbs[] = ['text' => $this->student->studentName, 'link' => '#'];
            }
        }
    }

    public function getPrevious(string $currentCrumb): array
    {
        if ($currentCrumb == 'lesson') {
            $this->breadCrumbs[] = ['text' => 'lessons', 'link' => '/lessons'];
        }
        return $this->breadCrumbs;
    }

}
