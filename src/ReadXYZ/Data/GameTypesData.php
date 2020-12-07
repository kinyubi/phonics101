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
        INSERT INTO abc_gametypes (gameTypeId, gameDisplayAs, thumbNailUrl, cssClass, belongsOnTab, isUniversal, universalGameUrl, active)
            VALUES($id, $display, $thumb, $css, $tab, $universal, $url, $active)
            ON DUPLICATE KEY UPDATE
            gameDisplayAs = $display,
            thumbNailUrl = $url,
            cssClass = $css,
            belongsOnTab = $tab,
            isUniversal = $universal,
            universalGameUrl = $url,
            active = $active
EOT;
        return $this->query($query, QueryType::AFFECTED_COUNT);
    }

    /**
     * @param mixed ...$params
     * @return GameType[]
     */
    public function getAll(...$params): array
    {
        $throwIfNotFound = $this->checkThrowable($params);
        $boolEnumTreatment = $this->checkBoolEnumTreatment($params);
        return $this->getSelect('', $throwIfNotFound, $boolEnumTreatment);
    }

    /**
     * @param mixed ...$params
     * @return GameType[]
     */
    public function getAllActive(...$params): array
    {
        $throwIfNotFound = $this->checkThrowable($params);
        $boolEnumTreatment = $this->checkBoolEnumTreatment($params);
        return $this->getSelect("WHERE active = 'Y'", $throwIfNotFound, $boolEnumTreatment);
    }

    public function get(string $gameTypeId, ...$params): GameType
    {
        $boolEnumTreatment = $this->checkBoolEnumTreatment($params);
        $throwOnNotFound = $this->checkThrowable($params);
        $lowerId = strtolower($gameTypeId);
        $query = "SELECT * FROM abc_gametypes WHERE gameTypeId = '$lowerId' ";
        return new GameType($this->throwableQuery($query, QueryType::SINGLE_OBJECT, $boolEnumTreatment, $throwOnNotFound));
    }

    /**
     * @param string $whereClause
     * @param mixed ...$params
     * @return stdClass[]
     */
    public function getSelect(string $whereClause = '', ...$params)
    {
        $throwIfNotFound = $this->checkThrowable($params);
        $boolEnumTreatment = $this->checkBoolEnumTreatment($params);
        if (not(empty($whereClause)) and not(Util::startsWith_ci('where', $whereClause))) {
            $whereClause = 'WHERE ' .$whereClause;
        }
        return $this->throwableQuery("SELECT * FROM abc_gametypes $whereClause", QueryType::STDCLASS_OBJECTS, $throwIfNotFound, $boolEnumTreatment);

    }
}
