<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\DbVersion;
use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Enum\ActiveType;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Lessons\Lesson;
use stdClass;

class LessonsData extends AbstractData
{

    public function __construct(string $dbVersion = DbVersion::READXYZ0_PHONICS)
    {
        parent::__construct('abc_lessons', 'lessonCode', $dbVersion);
        $this->booleanFields = ['active'];
        $this->jsonFields = ['alternateNames', 'games', 'spinner', 'contrastImages'];
    }

    /**
     * @throws PhonicsException
     */
    public function _create(): void
    {
        $query = <<<EOT
CREATE TABLE `abc_lessons` (
	`lessonCode` VARCHAR(32) NOT NULL COMMENT 'Format: G01L01',
	`lessonName` VARCHAR(128) NOT NULL,
	`lessonDisplayAs` VARCHAR(128) NOT NULL,
	`groupCode` VARCHAR(32) NULL DEFAULT NULL COMMENT 'IF group is changed  in abc_groups, change it here. If group is deleted in abc_groups, set this to null.',
	`lessonContent` MEDIUMTEXT NULL COMMENT 'Unused',
	`wordList` VARCHAR(1024) NULL DEFAULT NULL,
	`supplementalWordList` VARCHAR(1024) NULL DEFAULT NULL,
	`stretchList` VARCHAR(1024) NULL DEFAULT NULL,
	`flipBook` VARCHAR(50) NOT NULL DEFAULT '',
	`active` ENUM('Y','N') NOT NULL DEFAULT 'Y',
	`alternateNames` MEDIUMTEXT NULL,
	`fluencySentences` MEDIUMTEXT NULL,
	`games` MEDIUMTEXT NULL,
	`spinner` MEDIUMTEXT NULL,
	`contrastImages` MEDIUMTEXT NULL,
	`ordinal` TINYINT(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (`lessonCode`),
	INDEX `fk_groups__groupCode` (`groupCode`),
	CONSTRAINT `fk_groups__groupCode` FOREIGN KEY (`groupCode`) REFERENCES `abc_groups` (`groupCode`) 
	ON UPDATE CASCADE ON DELETE SET NULL ) COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    /**
     * @param stdClass $lesson
     * @param int $ordinal
     * @return DbResult
     * @throws PhonicsException
     */
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
        $active = isset($lesson->active) ? $this->boolToEnum($lesson->active) :  ActiveType::IS_ACTIVE;
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

    /**
     * @param string $lesson
     * @return Lesson
     * @throws PhonicsException
     */
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
     * @throws PhonicsException
     */
    public function getLessonsWithGroupFields(): array
    {
        $query = "SELECT * FROM vw_lessons_with_group_fields ORDER BY lessonCode";
        return $this->throwableQuery($query, QueryType::STDCLASS_OBJECTS);
    }

    /**
     * returns lessonCode associated with lessonName.
     * @param string $lessonName
     * @return string
     * @throws PhonicsException
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
     * @throws PhonicsException
     */
    public function getLessonDisplayAs(string $lesson): string
    {
        $value = $this->smartQuotes($lesson);
        $query = "SELECT lessonDisplayAs FROM abc_lessons WHERE lessonCode = $value OR lessonName = $value";
        return $this->throwableQuery($query, QueryType::SCALAR);
    }


}
