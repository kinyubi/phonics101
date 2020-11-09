<?php

use App\ReadXYZ\Data\LessonsData;
use App\ReadXYZ\JSON\UnifiedLessons;

require dirname(__DIR__) . '/src/ReadXYZ/autoload.php';

$unifiedLessons = new UnifiedLessons();

printf("Usage: php updateLessonsTableFromUnifiedLessons.php [all] | [<lessonName>]");

$whichLesson = $argv[1] ?? '';
if (empty($whichLesson)) {
    printf("Press <Enter> to update all records or enter a lesson name and Press <Enter>: ");
    $whichLesson = readline();
}


$lessonsFromJson = $unifiedLessons::getDataAsStdClass()->lessons->blending;
$lessonsTable = new LessonsData();
$ordinal = 1;
$errors = 0;
$prevGroupCode = '';
$ordinal = 0;
$attempted = 0;
foreach ($lessonsFromJson as $lesson) {
    if ($prevGroupCode != $lesson->groupName) {
        $ordinal = 1;
        $prevGroupCode = $lesson->groupName;
    } else {
        $ordinal++;
    }
    if (empty($whichLesson) || $whichLesson == $lesson->lessonName) {
        $result = $lessonsTable->insertOrUpdate($lesson, $ordinal);
        $attempted++;
        if ($result->failed()) {
            printf("Error updating %s. %s\n", $lesson->lessonName, $result->getErrorMessage());
            $errors++;
        }
    }
}
if (empty($whichLesson)) {
    printf("%d records updated with %d errors.\n", $attempted, $errors);
} else {
    if ($errors == 0) {
        printf("%s lesson updated successfully.\n", $whichLesson);
    } else {
        printf("Error attempting to update %s lesson\n", $whichLesson);
        exit(1);
    }
}

