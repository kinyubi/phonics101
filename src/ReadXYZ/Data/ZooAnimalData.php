<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\DbVersion;
use App\ReadXYZ\Enum\QueryType;

class ZooAnimalData extends AbstractData
{
    public function __construct(string $dbVersion=DbVersion::READXYZ0_PHONICS)
    {
        parent::__construct('abc_zoo_animals', 'animalCode', $dbVersion);
    }

    public function _create(): void
    {
        $query = <<<EOT
        CREATE TABLE `abc_zoo_animals` (
            `animalCode` VARCHAR(32) NOT NULL,
            `fileName` VARCHAR(32) NOT NULL,
            `grayFileName` VARCHAR(32) NOT NULL,
            `friendlyName` VARCHAR(32) NOT NULL,
            `associatedLesson` VARCHAR(32) NULL DEFAULT NULL,
            PRIMARY KEY (`animalCode`),
            INDEX `FK_animal__lesson` (`associatedLesson`),
            CONSTRAINT `FK_animal__lesson` FOREIGN KEY (`associatedLesson`) 
                REFERENCES `abc_lessons` (`lessonCode`) ON UPDATE CASCADE ON DELETE SET NULL
        ) COLLATE='utf8_general_ci' ENGINE=InnoDB
EOT;
        $this->throwableQuery($query, QueryType::STATEMENT);
    }


    public function insert(string $animalCode, string $friendlyName): void
    {
        $code = $this->smartQuotes($animalCode);
        $name = $this->smartQuotes($friendlyName);
        $fileName = $this->smartQuotes($animalCode . '.jpg');
        $grayFileName = $this->smartQuotes($animalCode . '_gray.jpg');
        $query = <<<EOT
        INSERT INTO abc_zoo_animals(animalCode, fileName, grayFileName, friendlyName) 
        VALUES($code, $fileName, $grayFileName, $name)
EOT;

        $this->throwableQuery($query, QueryType::STATEMENT);
    }
}
