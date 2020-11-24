<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Lessons\Lessons;
use App\ReadXYZ\POPO\Warmup;
use App\ReadXYZ\POPO\WarmupItem;

class WarmupData extends AbstractData
{
    public function __construct()
    {
        parent::__construct('abc_warmups', 'id');
        $this->jsonFields = ['parts'];
    }

    public function _create(): void
    {
        $query = <<<EOT
        CREATE TABLE `abc_warmups` (
            `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `lessonCode` VARCHAR(32) NOT NULL,
            `ordinal` TINYINT(4) NOT NULL,
            `instructions` VARCHAR(1024) NOT NULL DEFAULT '',
            `parts` JSON NULL DEFAULT NULL COMMENT '[{\'directions\': \'xx\', \'parts\': [\'xx\', \'xx\', ...]}]',
            PRIMARY KEY (`id`),
            INDEX `fk_warmup__lessonCode` (`lessonCode`),
            CONSTRAINT `fk_warmup__lessonCode` FOREIGN KEY (`lessonCode`) REFERENCES `abc_lessons` (`lessonCode`)
        ) COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

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
        $this->truncate();
        foreach($lessonWarmups as $warmup) {
            $lessonName = $lessons->getRealLessonName($warmup->lessonName);
            $lessonCode = $lessonsData->getLessonCode($lessonName);
            $ordinal = 1;
            foreach($warmup->warmupItems as $item) {
                $json = $this->encodeJsonQuoted($item);
                $this->insertOne($lessonCode, $ordinal, $warmup->instructions, $json);
                $ordinal++;
            }

        }
    }

    public function insertOne(string $lessonCode, int $ordinal, string $instructions, string $parts)
    {
        $lesson = empty($lessonCode) ? 'NULL' : $this->smartQuotes($lessonCode);
        $instruct = $this->smartQuotes($instructions);
        $query = <<<EOT
        INSERT INTO abc_warmups(lessonCode, ordinal, instructions, parts)
        VALUES ($lesson,  $ordinal, $instruct, $parts)
EOT;
        $this->throwableQuery($instructions, QueryType::STATEMENT);
    }
}
