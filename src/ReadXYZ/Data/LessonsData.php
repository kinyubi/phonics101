<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Models\BoolWithMessage;
use RuntimeException;
use stdClass;

class LessonsData extends AbstractData
{

    public function __construct()
    {
        parent::__construct('abc_lessons');
    }

    public function create(): void
    {
        $query = <<<EOT
CREATE TABLE `abc_lessons` (
	`lessonCode` VARCHAR(32) NOT NULL COMMENT 'Format: G01L01',
	`lessonName` VARCHAR(128) NOT NULL,
	`lessonDisplayAs` VARCHAR(128) NOT NULL,
	`groupCode` VARCHAR(32) NULL DEFAULT NULL,
	`lessonContent` JSON NULL DEFAULT NULL,
	`wordList` VARCHAR(1024) NULL DEFAULT NULL,
	`supplementalWordList` VARCHAR(1024) NULL DEFAULT NULL,
	`stretchList` VARCHAR(1024) NULL DEFAULT NULL,
	`flipBook` VARCHAR(50) NOT NULL DEFAULT '',
	`active` ENUM('Y','N') NOT NULL DEFAULT 'Y',
	`alternateNames` JSON NULL DEFAULT NULL,
	`fluencySentences` JSON NULL DEFAULT NULL,
	`games` JSON NULL DEFAULT NULL,
	`spinner` JSON NULL DEFAULT NULL,
	`contrastImages` JSON NULL DEFAULT NULL,
	PRIMARY KEY (`lessonCode`),
	INDEX `fk_groups__groupCode` (`groupCode`),
	CONSTRAINT `fk_groups__groupCode` FOREIGN KEY (`groupCode`) REFERENCES `abc_groups` (`groupCode`) ON UPDATE CASCADE ON DELETE SET NULL
) COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $result = $this->db->queryStatement($query);
        if ($result->failed()) {
            throw new RuntimeException($this->db->getErrorMessage());
        }
    }

    public function insertOrUpdate(stdClass $lesson, int $ordinal): BoolWithMessage
    {
        $groupTable = new GroupData();
        $result = $groupTable->getGroupCode($lesson->groupName);
        if ($result->failed()) throw new RuntimeException("Unable to find group code for {$lesson->groupName}.");
        $groupCode = $result->getResult();
        $lessonCode = $groupCode . 'L' . str_pad(strval($ordinal), 2, '0', STR_PAD_LEFT);
        $flipbook = $lesson->book ?? '';
        $wordlist = isset($lesson->wordList) ? $this->encodeJsonQuoted($lesson->wordList) : 'NULL';
        $supplemental = isset($lesson->supplementalWordList) ? $this->encodeJsonQuoted($lesson->supplementalWordList) : 'NULL';
        $stretch = isset($lesson->stretchList) ? $this->encodeJsonQuoted($lesson->stretchList) : 'NULL';
        $alternateNames = isset($lesson->alternateNames) ? $this->encodeJsonQuoted($lesson->alternateNames) : 'NULL';
        $fluencySentences = isset($lesson->fluencySentences) ? $this->encodeJsonQuoted($lesson->fluencySentences) : 'NULL';
        $games = isset($lesson->games) ? $this->encodeJsonQuoted($lesson->games) : 'NULL';
        $spinner = isset($lesson->spinner) ? $this->encodeJsonQuoted($lesson->spinner) : 'NULL';
        $contrastImages = isset($lesson->contrastImages) ? $this->encodeJsonQuoted($lesson->contrastImages) : 'NULL';

        $query = <<<EOT
    INSERT INTO abc_lessons(lessonCode, lessonName, lessonDisplayAs, groupCode, lessonContent, wordList, supplementalWordList, stretchList, flipbook, alternateNames, fluencySentences, games, spinner, contrastImages, ordinal)
        VALUES('$lessonCode', '{$lesson->lessonId}', '{$lesson->lessonDisplayAs}', '$groupCode', NULL,
            $wordlist, $supplemental, $stretch, '$flipbook', $alternateNames, $fluencySentences, $games,
            $spinner, $contrastImages, $ordinal)
        ON DUPLICATE KEY UPDATE 
        lessonName = '{$lesson->lessonId}',
        lessonDisplayAs = '{$lesson->lessonDisplayAs}',
        groupCode = '$groupCode',
        lessonContent = NULL,
        wordlist = $wordlist,
        supplementalWordList = $supplemental,
        stretchList = $stretch,
        flipBook = '$flipbook',
        alternateNames = $alternateNames,
        fluencySentences = $fluencySentences,
        games = $games,
        spinner = $spinner,
        contrastImages = $contrastImages,                                 
        ordinal = $ordinal
EOT;
        return $this->db->queryStatement($query);
    }

    public function getLessonCode(string $lessonName): string
    {
        $query = "SELECT lessonCode FROM abc_lessons WHERE lessonName = '$lessonName'";
        $result = $this->db->queryAndGetScalar($query);
        if ($result->failed()) {
            throw new RuntimeException("Attempt to get lesson code for $lessonName failed. " . $result->getMessage());
        }
        return $result->getResult();
    }
}
