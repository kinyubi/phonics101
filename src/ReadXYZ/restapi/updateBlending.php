<?php

require_once dirname(__DIR__) . '/autoload.php';

use ReadXYZ\Helpers\Util;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');

$error_message = '';

function sendResponse(int $http_code = 200, string $msg = 'OK'): void
{
    http_response_code($http_code);
    echo json_encode(['code' => $http_code, 'msg' => $msg]);
}

/**
 * @param mysqli $conn
 * @param string $query
 *
 * @return bool
 *
 * @throws Exception
 */
function hasRecords($conn, $query): bool
{
    if ($result = $conn->query($query)) {
        $exists = ($result->num_rows > 0);
        $result->close();

        return $exists;
    } else {
        throw new Exception("Query unexpectedly failed ({$conn->error}): $query");
    }
}

/**
 * target function for an AJAX update of the warmup Blending screen.
 * $_REQUEST['studentID']) the student we are updating
 * $_REQUEST['allLessons'] a list of ids from  abc_warmuptext to be considered
 * $_REQUEST['wordcheck'] the list of ids that we want to mark as checked off.
 *
 * Any record in abc_warmupsubmit for the student and in the list of alllessons is deleted
 * Records for the student with a warmId in the wordcheck array is added.
 *
 * @param mysqli $conn mysqli connection
 *
 * @return bool true on success
 */
function updateWarmup(mysqli $conn): bool
{
    global $error_message;

    $studentID = $_REQUEST['studentID'];
    $ids = $_REQUEST['allLessons'];
    //take out existing for the student so we don't add duplicates
    $query = "DELETE FROM abc_warmupsubmit WHERE studentID = '$studentID' AND warmID IN ($ids)";
    if (!$conn->query($query)) {
        $error_message = "Query failed: {$conn->error} ::: $query";

        return false;
    }
    foreach ($_REQUEST['wordcheck'] as $warmID) {
        $query = "INSERT INTO abc_warmupsubmit (studentID, warmID) VALUES ('$studentID', '$warmID')";
        if (!$conn->query($query)) {
            $error_message = "Query failed: {$conn->error} ::: $query";

            return false;
        }
    }

    return true;
}

/**
 * target function for an AJAX update of the mastery Blending screen
 * $_REQUEST['studentID'] the student we are updating
 * $_REQUEST['wordlist'] a CSV string list of all words in the lesson
 * $_REQUEST['word1'] an array of words from the wordlist that have been mastered
 * Since mastered words may have been added or removed, we delete any words in the wordlist and that
 * add a record for each word in the word1 arrayy which are our mastered words.
 *
 * @see https://www.php.net/manual/en/function.mysql-real-escape-string.php
 * @see https://itsolutionstuff.com/post/php-jquery-ajax-post-request-exampleexample.html
 *
 * @param mysqli $conn mysqli connection
 *
 * @return bool true on success
 */
function updateMastery(mysqli $conn): bool
{
    global $error_message;

    $studentID = $_REQUEST['studentID'];
    $wordList = Util::quoteList($_REQUEST['wordlist']);

    $query = "DELETE FROM abc_usermastery WHERE studentID = '$studentID' AND word IN ($wordList)";
    if (!$conn->query($query)) {
        $error_message = "Query failed: {$conn->error} ::: $query";

        return false;
    }
    foreach ($_REQUEST['word1'] as $word1) {
        $query = "INSERT INTO abc_usermastery (studentID, word) VALUES ('$studentID', '$word1')";
        if (true !== $conn->query($query)) {
            $error_message = "Query failed: {$conn->error} ::: $query";

            return false;
        }
    }

    return true;
}

if (Util::contains($_SERVER['HTTP_HOST'] ?? '', '.test')) {
    error_reporting(E_ALL);
}

//$old_error_reporting = error_reporting(0);
$dbName = 'readxyz0_1';
$conn = new \mysqli('localhost', 'readxyz0_admin', 'doc123', $dbName);

if ($conn->connect_errno) {
    sendResponse(500, 'Connect failed' . $conn->connect_error);
    exit;
}

//decide which method to call based on $_REQUEST parameters
$actionFound = false;
if (array_key_exists('action', $_REQUEST)) {
    $conn->autocommit(false);
    if ('updateWarmup' == $_REQUEST['action']) {
        $result = updateWarmup($conn);
        $actionFound = true;
    } elseif ('updateMastery' == $_REQUEST['action']) {
        $result = updateMastery($conn);
        $actionFound = true;
    }
} else {
    $result = false;
}

// tell the caller what happened
if ($result) {
    $msg = 'Update successful';
    $conn->commit();
    $conn->close();
    sendResponse(200, $msg);
} else {
    if ($actionFound) {
        $msg = "Error: transaction rolled back. $error_message";
        $conn->rollback();
        sendResponse(500, $msg);
    } else {
        sendResponse(400, 'Unknown method.');
    }
    $conn->close();
}

//error_reporting($old_error_reporting);
