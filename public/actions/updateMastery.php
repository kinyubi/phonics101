<?php

use ReadXYZ\Helpers\Util;
use ReadXYZ\Models\Cookie;
use ReadXYZ\Models\Student;
use ReadXYZ\Twig\Twigs;

require 'autoload.php';

if (Util::isLocal()) {
    error_reporting(E_ALL | E_STRICT);
}
Cookie::getInstance()->tryContinueSession();

$error_message = '';

function updateMastery(mysqli $conn): bool
{
    global $error_message;

    $studentID = Student::getInstance()->studentID;
    $wordList = Util::quoteList($_REQUEST['wordlist']);
    $conn->begin_transaction();
    $query = "DELETE FROM abc_usermastery WHERE studentID = '$studentID' AND word IN ($wordList)";
    if (!$conn->query($query)) {
        $error_message = "Query failed: {$conn->error} ::: $query";
        $conn->rollback();
        return false;
    }
    foreach ($_REQUEST['word1'] as $word1) {
        $query = "INSERT INTO abc_usermastery (studentID, word) VALUES ('$studentID', '$word1')";
        if (true !== $conn->query($query)) {
            $error_message = "Query failed: {$conn->error} ::: $query";
            $conn->rollback();
            return false;
        }
    }
    $conn->commit();
    return true;
}

function sendResponse(int $http_code = 200, string $msg = 'OK'): void
{
    http_response_code($http_code);
    echo json_encode(['code' => $http_code, 'msg' => $msg]);
}


$twigs = Twigs::getInstance();
$conn = Util::dbConnect();

if ($conn->connect_errno) {
    error_log('UpdateMastery failed to connect.');
    sendResponse(500, 'Connect failed' . $conn->connect_error);
    exit;
}

$result = updateMastery($conn);
$conn->close();

if ($result) {
    sendResponse(200, 'Update successful');
} else {
    $msg = "Error: transaction rolled back. $error_message";
    error_log($msg);
    sendResponse(500, $msg);
}
