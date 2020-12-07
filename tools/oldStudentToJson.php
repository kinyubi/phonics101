<?php

use App\ReadXYZ\Data\OldStudentData;
use App\ReadXYZ\Data\OldUserData;

require 'autoload.php';

function convertToSimpleEmail(string $username): string
{
    $pos = strrpos($username, '-');
    if ($pos === false) return $username;
    return substr($username, 0, $pos);
}

$studentData = new OldStudentData();
$userData = new OldUserData();
$userObjects = $studentData->getStudentTrainers();
$users = [];
$aliases = [];

foreach ($userObjects as $object) {
    $simple = convertToSimpleEmail($object->trainer1);
    $users[] = $simple;
    $aliases[$object->trainer1] = $simple;
}
$uniqueUsers = array_unique($users);

$uniqueResults = [];


foreach ($uniqueUsers as $user) {
    $uniqueResults[$user] = (object) [
        'username' => $user,
        'trainerType' => 'trainer',
        'students' => []
    ];
}

foreach ($userObjects as $object) {
    $simple = $aliases[$object->trainer1];
    $studentId = $object->studentid;
    $data = $studentData->getData($studentId);
    $uniqueResults[$simple]->students[] = (object) [
        'studentId' => $studentId,
        'studentName' => $object->StudentName,
        'email' => $object->trainer1,
        'currentLesson' => $data->currentLesson,
        'mastery' => $data->lessonData
    ];
}


$staff = $userData->getUsersWithoutStudents();
foreach ($staff as $user) {
    $uniqueResults[$user] = (object) [
        'username' => $user,
        'trainerType' => 'staff',
        'students' => []
    ];
}
$simpleArray = array_values($uniqueResults);
$json = json_encode($simpleArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$outputFile = 'old_people.json';
file_put_contents($outputFile, $json);
printf("old_people.json successfully created.\n");
