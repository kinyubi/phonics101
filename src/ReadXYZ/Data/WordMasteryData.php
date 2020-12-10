<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\BoolWithMessage;
use App\ReadXYZ\Models\Log;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Helpers\PhonicsException;

class WordMasteryData extends AbstractData
{


    public function __construct()
    {
        parent::__construct('abc_word_mastery', 'id');
    }

    /**
     * @throws PhonicsException on ill-formed SQL
     */
    public function _create()
    {
        $query = <<<EOT
        CREATE TABLE `abc_word_mastery` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `studentCode` VARCHAR(32) NOT NULL,
            `word` VARCHAR(16) NOT NULL,
            `twice` TINYINT(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            INDEX `studentCode` (`studentCode`)
        ) COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    public function update(string $studentCode, string $presentedWordList, array $masteredWords): BoolWithMessage
    {
        $conn = $this->db->getConnection();
        $quotedList = Util::quoteList($presentedWordList);
        $query = "DELETE FROM abc_word_mastery WHERE studentCode = '$studentCode' AND word IN ($quotedList)";
        $conn->begin_transaction();
        $result = $this->db->queryStatement($query);
        if ($result->failed()) {
            $error_message = "Query failed: {$this->db->getErrorMessage()} ::: $query";
            $conn->rollback();
            return BoolWithMessage::badResult($error_message);
        } else {
            foreach ($masteredWords as $word) {
                $query = "INSERT INTO abc_word_mastery (studentCode, word) VALUES ('$studentCode', '$word')";
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

    /**
     * @return array
     * @throws PhonicsException on ill-formed SQL
     */
    public function getMasteredWords(): array
    {
        $session = new Session();
        if (!$session->hasLesson()) {
            throw new PhonicsException('Cannot get mastered words without an active lesson.');
        }
        $studentCode = $this->smartQuotes($session->getStudentCode());
        $query = "SELECT word from abc_word_mastery WHERE studentCode = $studentCode";
        return $this->throwableQuery($query, QueryType::SCALAR_ARRAY);
    }

}
