<?php

use App\ReadXYZ\Data\UserMastery;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\BoolWithMessage;
use App\ReadXYZ\Models\Cookie;
use App\ReadXYZ\Models\Student;

require 'autoload.php';

if (Util::isLocal()) {
    error_reporting(E_ALL | E_STRICT);
}
(new Cookie())->tryContinueSession();


function updateMastery(): BoolWithMessage
{
    $studentID = Student::getInstance()->studentID;
    $presentedWordList = $_REQUEST['wordlist'];
    $masteredWords = $_REQUEST['word1'] ?? [];
    $userMastery = new UserMastery();
    return $userMastery->update($studentID, $presentedWordList, $masteredWords);
}

function sendResponse(int $http_code = 200, string $msg = 'OK'): void
{
    http_response_code($http_code);
    echo json_encode(['code' => $http_code, 'msg' => $msg]);
}


$result = updateMastery();

if ($result->wasSuccessful()) {
    sendResponse(200, 'Update successful');
} else {
    $msg = $result->getErrorMessage();
    error_log($msg);
    sendResponse(500, $msg);
}
