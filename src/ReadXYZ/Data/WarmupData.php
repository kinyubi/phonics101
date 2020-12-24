<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\DbVersion;
use App\ReadXYZ\Enum\JsonDecode;
use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Helpers\PhonicsException;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Lessons\Lessons;
use App\ReadXYZ\POPO\Warmup;
use App\ReadXYZ\POPO\WarmupItem;

class WarmupData extends AbstractData
{
    public function __construct(string $dbVersion=DbVersion::READXYZ0_PHONICS)
    {
        parent::__construct('abc_warmups', 'id', $dbVersion);
        $this->jsonFields = ['parts'];
    }

    /**
     * @throws PhonicsException
     */
    public function _create(): void
    {
        $query = <<<EOT
CREATE TABLE `abc_warmups` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`lessonCode` VARCHAR(32) NULL DEFAULT NULL COMMENT 'If the lesson is deleted, we will just set lessonCode to null for those records in this table. Updates to the lessonCode in abc_lessons will be updated here as well.',
	`ordinal` TINYINT(4) NOT NULL,
	`instructions` VARCHAR(1024) NOT NULL DEFAULT '',
	`parts` MEDIUMTEXT NULL COMMENT '[{\'directions\': \'xx\', \'parts\': [\'xx\', \'xx\', ...]}]',
	PRIMARY KEY (`id`),
	INDEX `fk_warmup__lessonCode` (`lessonCode`),
	CONSTRAINT `fk_warmup__lessonCode` FOREIGN KEY (`lessonCode`) REFERENCES `abc_lessons` (`lessonCode`) ON UPDATE CASCADE ON DELETE SET NULL
) COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    /**
     * import warmups from a json file
     * @param string $fileName
     * @throws PhonicsException
     */
    public function importJson(string $fileName = '')
    {
        $lessonWarmups = [];
        if (empty($fileName)) $fileName = Util::getReadXyzSourcePath('resources/warmups.json');
        $str = file_get_contents($fileName, false, null);
        $shell = json_decode($str, true);
        foreach($shell['lessons'] as $lesson) {
            $warmups = [];

            foreach ($lesson['warmups'] as $warmup) {
                $warmups[] = new WarmupItem($warmup['directions'], $warmup['parts']);
            }
            $lessonId = $lesson['lessonId'];
            $lessonWarmups[$lessonId] = new Warmup($lessonId, $lesson['instructions'] ?? '', $warmups );
        }
        $lessons = Lessons::getInstance();
        $lessonsData = new LessonsData();

        // We always want to totally replace these values
        $this->truncate();
        foreach($lessonWarmups as $warmup) {
            $lessonName = $lessons->getRealLessonName($warmup->lessonName);
            if (empty($lessonName)) {
                printf("A warmup is being added for %s even though the lesson doesn't yet exist.\n", $warmup->lessonName);
            }
            $lessonCode = $lessonsData->getLessonCode($lessonName);
            $json = $this->encodeJsonQuoted($warmup->warmupItems);
            $this->insert($lessonCode,  $warmup->instructions, $json);
        }
    }

    /**
     * @param string $lessonCode
     * @param string $instructions
     * @param string $parts
     * @throws PhonicsException
     */
    public function insert(string $lessonCode, string $instructions, string $parts)
    {
        $lesson = empty($lessonCode) ? 'NULL' : $this->smartQuotes($lessonCode);
        $instruct = $this->smartQuotes($instructions);
        $query = <<<EOT
        INSERT INTO abc_warmups(lessonCode, instructions, parts)
        VALUES ($lesson,  $instruct, $parts)
EOT;
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    /**
     * gets the requested lesson
     * @param string $lessonCode
     * @return Warmup|null
     * @throws PhonicsException
     */
    public function get(string $lessonCode): ?Warmup
    {
        $query = "SELECT * FROM abc_warmups WHERE lessonCode = '$lessonCode'";
        $result = $this->query($query, QueryType::SINGLE_RECORD);

        if ($result->wasSuccessful()) {
            $record = $result->getResult();
            if (empty($result)) return null;
            $partsJson = $record['parts'];
            $parts = JsonDecode::decode($partsJson, JsonDecode::RETURN_ASSOCIATIVE_ARRAY);
            $items = [];
            foreach($parts as $item) {
                $items[] = new WarmupItem($item['directions'], $item['parts']);
            }
            return new Warmup($record['lessonCode'], $record['instructions'], $items);
        } else {
            return null;
        }
    }

    public function exists(string $lessonCode):bool
    {
        $query = "SELECT * FROM abc_warmups WHERE lessonCode = '$lessonCode'";
        return $this->throwableQuery($query, QueryType::EXISTS);
    }
}
