<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\POPO\GameType;
use stdClass;

class GameTypesData extends AbstractData
{
    public function __construct()
    {
        parent::__construct('abc_students', 'gameTypeId');
        $this->booleanFields = ['active', 'isUniversal'];
    }

    public function _create()
    {
        $query = <<<EOT
CREATE TABLE `abc_gametypes` (
	`gameTypeId` VARCHAR(32) NOT NULL,
	`gameDisplayAs` VARCHAR(32) NOT NULL,
	`thumbNailUrl` VARCHAR(64) NOT NULL,
	`cssClass` VARCHAR(32) NOT NULL DEFAULT '',
	`belongsOnTab` VARCHAR(32) NOT NULL,
	`isUniversal` ENUM('Y','N') NOT NULL DEFAULT 'N',
	`universalGameUrl` VARCHAR(128) NOT NULL DEFAULT '',
	`active` ENUM('Y','N') NOT NULL DEFAULT 'Y',
	PRIMARY KEY (`gameTypeId`),
	INDEX `fk__tabTypeId` (`belongsOnTab`),
	CONSTRAINT `fk__tabTypeId` FOREIGN KEY (`belongsOnTab`) REFERENCES `abc_tabtypes` (`tabTypeId`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    public function insertOrUpdateStd(stdClass $gameType): DbResult
    {
        $id = $this->smartQuotes($gameType->gameTypeId);
        $display = $this->smartQuotes($gameType->gameDisplayAs);
        $thumb = $this->smartQuotes($gameType->thumbNailUrl);
        $tab = $this->smartQuotes($gameType->belongsOnTab);
        $universal = $this->smartQuotes($gameType->isUniversal);
        $url = $this->smartQuotes($gameType->universalGameUrl);
        $css = $this->smartQuotes($gameType->cssClass ?? '');
        $active = $this->smartQuotes($this->boolToEnum($gameType->active));
        $query = <<<EOT
        INSERT INTO abc_gametypes (gameTypeId, gameDisplayAs, thumbNailUrl, cssClass, tabTypeId, isUniversal, universalGameUrl, active)
            VALUES($id, $display, $thumb, $css, $tab, $universal, $url, $active)
            ON DUPLICATE KEY UPDATE
            gameDisplayAs = $display,
            thumbNailUrl = $url,
            cssClass = $css,
            tabTypeId = $tab,
            isUniversal = $universal,
            universalGameUrl = $url,
            active = $active
EOT;
        return $this->query($query, QueryType::AFFECTED_COUNT);
    }

    /**
     * @param string $whereClause
     * @return GameType[]
     */
    public function getAll(string $whereClause = '')
    {
        if (not(empty($whereClause)) and not(Util::startsWith_ci($whereClause, 'where'))) {
            $whereClause = 'WHERE ' .$whereClause;
        }
        $records = $this->throwableQuery("SELECT * FROM abc_gametypes $whereClause", QueryType::STDCLASS_OBJECTS);

        $gameTypes = [];
        foreach ($records as $record) {
            $id = $record->gameTypeId;
            $gameTypes[$id] = new GameType($record);
        }
        return $gameTypes;
    }

    /**
     * @return GameType[]
     */
    public function getAllActive(): array
    {
        return $this->getAll("WHERE active = 'Y'");
    }

    public function get(string $gameTypeId): GameType
    {
        $lowerId = strtolower($gameTypeId);
        $query = "SELECT * FROM abc_gametypes WHERE gameTypeId = '$lowerId' ";
        return new GameType($this->throwableQuery($query, QueryType::SINGLE_OBJECT));
    }
}
