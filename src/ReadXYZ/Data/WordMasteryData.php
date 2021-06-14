<?php


namespace App\ReadXYZ\Data;

use App\ReadXYZ\CSV\CSV;
use App\ReadXYZ\Enum\DbVersion;
use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Models\BoolWithMessage;
use App\ReadXYZ\Models\Log;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Helpers\PhonicsException;

class WordMasteryData extends AbstractData
{


    public function __construct(string $dbVersion=DbVersion::READXYZ0_PHONICS)
    {
        parent::__construct('abc_word_mastery', 'id', $dbVersion);
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
	`dateMastered` DATE NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `studentCode` (`studentCode`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    public function update(string $studentCode, string $presentedWordList, array $masteredWords): BoolWithMessage
    {
        $conn = $this->db->getConnection();
        $quotedList = CSV::getInstance()->quoteList($presentedWordList);
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
        if (!Session::hasLesson()) {
            throw new PhonicsException('Cannot get mastered words without an active lesson.');
        }
        $studentCode = $this->smartQuotes(Session::getStudentCode());
        $query = "SELECT word from abc_word_mastery WHERE studentCode = $studentCode";
        return $this->throwableQuery($query, QueryType::SCALAR_ARRAY);
    }

    /**
     * @param string $studentId
     * @param string $word
     * @return bool
     * @throws PhonicsException
     */
    public function exists(string $studentId, string $word): bool
    {
        $query = "SELECT * FROM abc_word_mastery WHERE studentCode = '$studentId' AND word = '$word'";
        return $this->throwableQuery($query, QueryType::EXISTS);
    }

    /**
     * @param string $studentId
     * @param $words
     * @throws PhonicsException
     */
    public function add(string $studentId, $words): void
    {
        if (!is_array($words)) $words = [$words];
        foreach($words as $word) {
            if (!$this->exists($studentId, $word)) {
                $smartWord = $this->smartQuotes($word);
                $query = "INSERT INTO abc_word_mastery(studentCode,word) VALUES('$studentId', $smartWord)";
                $this->throwableQuery($query, QueryType::STATEMENT);
            }
        }

    }

}
