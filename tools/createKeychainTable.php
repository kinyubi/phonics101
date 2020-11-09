<?php

use App\ReadXYZ\Data\KeychainData;
use App\ReadXYZ\Lessons\Lessons;

require dirname(__DIR__) . '/src/ReadXYZ/autoload.php';

$answer = readline("Truncate only(y/n): ");

$keychainData = new KeychainData();
$count = $keychainData->getCount();
if ($count > 0) {
    $result = $keychainData->truncate();
    if ($result->failed()) {
        exit( $result->getErrorMessage());
    }
}


if ('y' == $answer) exit();

$result = $keychainData->populate();
if ($result->failed()) {
    printf("%s\n", $result->getErrorMessage());
} else {
    printf("abc_keychain successfully populated with %d records.\n", $keychainData->getCount());
}
