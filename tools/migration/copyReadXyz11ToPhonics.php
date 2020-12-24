<?php

use App\ReadXYZ\Data\StudentsData;
use App\ReadXYZ\Data\TrainersData;
use App\ReadXYZ\Enum\DbVersion;


require '../autoload.php';

$oldTrainerData = new TrainersData(DbVersion::READXYZ1_1);
$newTrainerData = new TrainersData();

$oldStudentData = new StudentsData(DbVersion::READXYZ1_1);
$newStudentData = new StudentsData();

// try {
    $oldTrainers = $oldTrainerData->getWhere();
    foreach ($oldTrainers as $trainer) {
        $newTrainerData->addObject($trainer);
    }

    $oldStudents = $oldStudentData->getWhere();
    foreach($oldStudents as $student) {
        $newStudentData->addObject($student);
    }
// } catch (PhonicsException $ex) {
//     printf("%s\n%s\n", $ex->getMessage(), $ex->getTraceAsString());
// }


