<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Enum\Sql;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Lessons\Lesson;
use stdClass;

class LessonsData extends AbstractData
{

    public function __construct()
    {
        parent::__construct('abc_lessons', 'lessonCode');
        $this->booleanFields = ['active'];
        $this->jsonFields = ['alternateNames', 'games', 'spinner', 'contrastImages'];
    }

    public function _create(): void
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
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    public function insertOrUpdateFromDb(stdClass $lesson, int $ordinal): DbResult
    {
        $groupTable = new GroupData();
        $groupCode = $groupTable->getGroupCode($lesson->groupName);
        $lessonCode = $groupCode . Util::paddedNumber('L', $ordinal);
        $flipbook = $lesson->book ?? '';
        $wordlist = isset($lesson->wordList) ? $this->encodeJsonQuoted($lesson->wordList) : 'NULL';
        $supplemental = isset($lesson->supplementalWordList) ? $this->encodeJsonQuoted($lesson->supplementalWordList) : 'NULL';
        $stretch = isset($lesson->stretchList) ? $this->encodeJsonQuoted($lesson->stretchList) : 'NULL';
        $alternateNames = isset($lesson->alternateNames) ? $this->encodeJsonQuoted($lesson->alternateNames) : 'NULL';
        $fluencySentences = isset($lesson->fluencySentences) ? $this->encodeJsonQuoted($lesson->fluencySentences) : 'NULL';
        $games = isset($lesson->games) ? $this->encodeJsonQuoted($lesson->games) : 'NULL';
        $spinner = isset($lesson->spinner) ? $this->encodeJsonQuoted($lesson->spinner) : 'NULL';
        $contrastImages = isset($lesson->contrastImages) ? $this->encodeJsonQuoted($lesson->contrastImages) : 'NULL';
        $active = isset($lesson->active) ? $this->boolToEnum($lesson->active) :  Sql::ACTIVE;
        $query = <<<EOT
    INSERT INTO abc_lessons(lessonCode, lessonName, lessonDisplayAs, groupCode, lessonContent, wordList, supplementalWordList, stretchList, flipbook, alternateNames, fluencySentences, games, spinner, contrastImages, ordinal, active)
        VALUES('$lessonCode', '{$lesson->lessonId}', '{$lesson->lessonDisplayAs}', '$groupCode', NULL,
            $wordlist, $supplemental, $stretch, '$flipbook', $alternateNames, $fluencySentences, $games,
            $spinner, $contrastImages, $ordinal, '$active')
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
        ordinal = $ordinal,
        active = '$active'
EOT;
        return $this->query($query, QueryType::AFFECTED_COUNT);
    }

    public function get(string $lesson): Lesson
    {
        $x = $this->smartQuotes($lesson);
        $query = "SELECT * FROM abc_lessons WHERE lessonCode = $x OR lessonName = $x OR lessonDisplayAs = $x";
        $object = $this->throwableQuery($query, QueryType::SINGLE_OBJECT);
        $jsonFields = ['alternateNames', 'fluencySentences', 'games','spinner', 'contrastImages'];

        foreach($jsonFields as $field) {
            $json = $object->$field;
            $object->$field = ($json != null) ? json_decode($json) : null;
        }
        return new Lesson($object);
    }

    /**
     * object contains lessonCode, lessonName, lessonDisplayAs, groupCode, groupName, groupDisplayAs
     * @return stdClass[]
     */
    public function getLessonsWithGroupFields(): array
    {
        $query = "SELECT * FROM vw_lessons_with_group_fields";
        return $this->throwableQuery($query, QueryType::STDCLASS_OBJECTS);
    }

    /**
     * returns lessonCode associated with lessonName.
     * @param string $lessonName
     * @return string
     */
    public function getLessonCode(string $lessonName): string
    {
        $query = "SELECT lessonCode FROM abc_lessons WHERE lessonName = '$lessonName'";
        return $this->throwableQuery($query, QueryType::SCALAR);
    }

    /**
     * returns lessonDisplayAs if query matches lessonCode or LessonName.
     * @param string $lesson
     * @return string
     */
    public function getLessonDisplayAs(string $lesson): string
    {
        $value = $this->smartQuotes($lesson);
        $query = "SELECT lessonDisplayAs FROM abc_lessons WHERE lessonCode = $value OR lessonName = $value";
        return $this->throwableQuery($query, QueryType::SCALAR);
    }

}
