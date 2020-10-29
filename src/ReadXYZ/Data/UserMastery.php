<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\BoolWithMessage;

class UserMastery
{

    private PhonicsDb $db;

    public function __construct()
    {
        $this->db = new PhonicsDb();
    }

    public function update(string $studentId, string $presentedWordList, array $masteredWords): BoolWithMessage
    {
        $conn = $this->db->getConnection();
        $quotedList = Util::quoteList($presentedWordList);
        $query = "DELETE FROM abc_usermastery WHERE studentID = '$studentId' AND word IN ($quotedList)";
        $conn->begin_transaction();
        $result = $this->db->queryStatement($query);
        if ($result->failed()) {
            $error_message = "Query failed: {$this->db->getErrorMessage()} ::: $query";
            $conn->rollback();
            return BoolWithMessage::badResult($error_message);
        } else {
            foreach ($masteredWords as $word) {
                $query = "INSERT INTO abc_usermastery (studentID, word) VALUES ('$studentId', '$word')";
                $result =  $this->db->queryStatement($query);
                if ($result->failed()) {
                    $conn->rollback();
                    return $result;
                }
            }
            $conn->commit();
            return BoolWithMessage::goodResult();
        }
    }

}
