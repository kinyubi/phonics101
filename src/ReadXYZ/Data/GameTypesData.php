<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Lessons\GameType;
use App\ReadXYZ\Models\BoolWithMessage;
use RuntimeException;
use stdClass;

class GameTypesData extends AbstractData
{
    public function __construct()
    {
        parent::__construct('abc_students');
    }

    public function create()
    {
        $query = <<<EOT
CREATE TABLE `abc_gametypes` (
	`gameTypeId` VARCHAR(32) NOT NULL,
	`gameDisplayAs` VARCHAR(32) NOT NULL,
	`thumbNailUrl` VARCHAR(64) NOT NULL,
	`cssClass` VARCHAR(32) NOT NULL DEFAULT '',
	`tabTypeId` VARCHAR(32) NOT NULL,
	`isUniversal` ENUM('Y','N') NOT NULL DEFAULT 'N',
	`universalGameUrl` VARCHAR(128) NOT NULL DEFAULT '',
	PRIMARY KEY (`gameTypeId`),
	INDEX `fk__tabTypeId` (`tabTypeId`),
	CONSTRAINT `fk__tabTypeId` FOREIGN KEY (`tabTypeId`) REFERENCES `abc_tabtypes` (`tabTypeId`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $result = $this->db->queryStatement($query);
        if ($result->failed()) {
            throw new RuntimeException($this->db->getErrorMessage());
        }
    }

    public function insertOrUpdateStd(stdClass $gameType): BoolWithMessage
    {
        $id = $this->smartQuotes($gameType->gameTypeId);
        $display = $this->smartQuotes($gameType->gameDisplayAs);
        $thumb = $this->smartQuotes($gameType->thumbNailUrl);
        $tab = $this->smartQuotes($gameType->belongsOnTab);
        $universal = $this->smartQuotes($gameType->isUniversal ? 'Y' : 'N');
        $url = $this->smartQuotes($gameType->universalGameUrl);
        $css = $this->smartQuotes($gameType->cssClass ?? '');
        $query = <<<EOT
        INSERT INTO abc_gametypes (gameTypeId, gameDisplayAs, thumbNailUrl, cssClass, tabTypeId, isUniversal, universalGameUrl)
            VALUES($id, $display, $thumb, $css, $tab, $universal, $url)
            ON DUPLICATE KEY UPDATE
            gameDisplayAs = $display,
            thumbNailUrl = $url,
            cssClass = $css,
            tabTypeId = $tab,
            isUniversal = $universal,
            universalGameUrl = $url
EOT;
        return $this->db->queryStatement($query);
    }

    /**
     * @return GameType[]
     */
    public function getAll(): array
    {
        $result = $this->db->queryRows("SELECT * FROM abc_gametypes");
        if ($result->failed()) {
            throw new RuntimeException($this->db->getErrorMessage());
        }
        $records = $result->getResult();
        $gameTypes = [];
        foreach ($records as $record) {
            $id = $record['gameTypeId'];
            $gameTypes[$id] = $this->sqlToGameType($record);
        }
        return $gameTypes;
    }

    public function get(string $gameTypeId): GameType
    {
        $lowerId = strtolower($gameTypeId);
        $query = "SELECT * FROM abc_gametypes WHERE gameTypeId = '$lowerId' ";
        $result = $this->db->queryRecord($query);
        if ($result->failed()) {
            throw new RuntimeException($this->db->getErrorMessage());
        }
        return $this->sqlToGameType($result->getResult());
    }

    private function sqlToGameType(array $record): GameType
    {
        $object = (object)[
            'gameTypeId'        => strtolower($record['gameTypeId']),
            'gameDisplayAs'     => $record['gameDisplayAs'],
            'thumbNailUrl'      => $record['thumbNailUrl'],
            'cssClass'          => $record['cssClass'],
            'belongsOnTab'      => $record['belongsOnTab'],
            'isUniversal'       => $record['isUniversal'] == 'Y',
            'universalGameUrl'  => $record['universalGameUrl']
        ];
        return new GameType($object);
    }
}
