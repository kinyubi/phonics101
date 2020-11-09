<?php

use App\ReadXYZ\Data\GroupData;
use App\ReadXYZ\JSON\UnifiedLessons;

require dirname(__DIR__) . '/src/ReadXYZ/autoload.php';

$unifiedLessons = new UnifiedLessons();

$groupsFromJson = $unifiedLessons::getDataAsStdClass()->groups;
$groupsTable = new GroupData();
$ordinal = 1;
$successes = 0;
foreach ($groupsFromJson as $group) {
    $result = $groupsTable->insertOrUpdate($group, $ordinal++);
    if ($result->failed()) {
        printf("Error update %s. %s\n", $group->groupName, $result->getErrorMessage());
    } else {
        $successes++;
    }
}
$attempts = $ordinal - 1;
printf("%d records successfully updated.\n", $attempts);

