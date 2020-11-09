<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Models\BoolWithMessage;
use RuntimeException;

class ZooAnimalData extends AbstractData
{
    public function __construct()
    {
        parent::__construct('abc_zoo_animals');
    }

    public function create(): void
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
	CONSTRAINT `FK_animal__lesson` FOREIGN KEY (`associatedLesson`) REFERENCES `abc_lessons` (`lessonCode`) ON UPDATE CASCADE ON DELETE SET NULL
) COLLATE='utf8_general_ci' ENGINE=InnoDB
EOT;
        $result = $this->db->queryStatement($query);
        if ($result->failed()) {
            throw new RuntimeException($this->db->getErrorMessage());
        }
    }


    public function insert(string $animalCode, string $friendlyName): BoolWithMessage
    {
        $query = 'INSERT INTO abc_zoo_animals(animalCode, fileName, grayFileName, friendlyName) VALUES(?,?,?,?)';
        $statement = $this->db->getPreparedStatement($query);
        $fileName = $animalCode . '.jpg';
        $grayFileName = $animalCode . '_gray.jpg';
        $statement->bind_param('ssss', $animalCode, $fileName, $grayFileName, $friendlyName);
        $result = $statement->execute();
        if ($result === false) {
            return BoolWithMessage::badResult($statement->error);
        } else {
            return BoolWithMessage::goodResult();
        }
    }
}
