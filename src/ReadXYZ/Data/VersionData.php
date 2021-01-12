<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\DbVersion;
use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Helpers\PhonicsException;
use stdClass;

class VersionData extends AbstractData
{
    private static VersionData $instance;

const CREATE_QUERY = <<<EOT
CREATE TABLE `abc_versions` (
	`tableName` VARCHAR(50) NOT NULL,
	`version` VARCHAR(50) NOT NULL DEFAULT '',
	`lastUpdated` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`tableName`)
)
COLLATE='utf8_general_ci'
;
EOT;

    private function __construct(string $dbVersion=DbVersion::READXYZ0_PHONICS)
    {
        parent::__construct('abc_versions', 'tableName', $dbVersion);
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new VersionData();
        }

        return self::$instance;
    }

    /**
     * @param stdClass $version
     * @return DbResult
     * @throws PhonicsException
     */
    public function insertOrUpdateStd(stdClass $version): DbResult
    {
        $tableName = $this->smartQuotes($version->tableName);
        $versionCode = $this->smartQuotes($version->version);
        $lastUpdated = $this->smartQuotes($version->lastUpdated);

        $query = <<<EOT
        INSERT INTO abc_versions (tableName, version, lastUpdated) VALUES($tableName, $versionCode, $lastUpdated)
            ON DUPLICATE KEY UPDATE tableName = $tableName, version = $versionCode
EOT;
        return $this->query($query, QueryType::AFFECTED_COUNT);
    }

    /**
     * fetch version record for given table name
     * @param string $tableName
     * @return ?stdClass
     * @throws PhonicsException
     */
    public function get(string $tableName): ?stdClass
    {
        $query = "SELECT * FROM abc_versions WHERE tableName = '$tableName' ";
        return $this->throwableQuery($query, QueryType::SINGLE_OBJECT);
    }
}
