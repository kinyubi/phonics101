<?php

use App\ReadXYZ\Data\LessonsData;
use App\ReadXYZ\Lessons\Lessons;

require dirname(__DIR__) . '/src/ReadXYZ/autoload.php';
$answer = readline("Truncate only(y/n): ");

$lessonsData = new LessonsData();
$count = $lessonsData->getCount();
if ($count > 0) {
    $result = $lessonsData->truncate();
    if ($result->failed()) {
        exit( $result->getErrorMessage());
    }
}

if ('y' == $answer) exit();

$lessons = Lessons::getInstance()->getAllLessons();

$count = $lessonsData->insertMany($lessons);
$expected = count($lessons);
if ($count != $expected) {
    exit("Expected to insert $expected but $count records actually inserted");
} else {
    printf("abc_lessons successfully populated with %d records.\n", $lessonsData->getCount());
}

