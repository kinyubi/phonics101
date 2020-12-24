<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\DbVersion;
use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\POPO\TabType;
use stdClass;

class TabTypesData extends AbstractData
{
    public function __construct(string $dbVersion=DbVersion::READXYZ0_PHONICS)
    {
        parent::__construct('abc_tabtypes', 'tabTypeId', $dbVersion);
    }

    public function _create()
    {
        $query = <<<EOT
CREATE TABLE `abc_tabtypes` (
	`tabTypeId` VARCHAR(32) NOT NULL,
	`tabDisplayAs` VARCHAR(32) NOT NULL,
	`alias` VARCHAR(32) NOT NULL DEFAULT '',
	`script` VARCHAR(1024) NOT NULL DEFAULT '',
	`iconUrl` VARCHAR(128) NOT NULL,
	PRIMARY KEY (`tabTypeId`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    public function insertOrUpdateStd(stdClass $tabType): DbResult
    {
        $id = $this->smartQuotes($tabType->tabTypeId);
        $display = $this->smartQuotes($tabType->tabDisplayAs);
        $script = $this->smartQuotes($tabType->script);
        $icon = $this->smartQuotes($tabType->imageFile);
        $query = <<<EOT
        INSERT INTO abc_tabtypes (tabTypeId, tabDisplayAs, script, iconUrl) 
            VALUES($id, $display, $script, $icon)
            ON DUPLICATE KEY UPDATE
            tabDisplayAs = $display,
            script = $script,
            iconUrl = $icon
EOT;
        return $this->query($query, QueryType::AFFECTED_COUNT);
    }

    /**
     * Creates an associative array of TabType objects. If an object has an alias then an index is
     * creates for the alias as well as the tabTypeId enabling lookup by either name.
     * @return TabType[]
     */
    public function getAll(): array
    {
        $objects = $this->throwableQuery("SELECT * FROM abc_tabtypes", QueryType::STDCLASS_OBJECTS);
        $tabTypes = [];
        foreach ($objects as $object) {
            $id = $object->tabTypeId;
            $alias = $object->alias;
            $tabType = new TabType($object);
            $tabTypes[$id] = $tabType;
            if (! empty($alias)) $tabTypes[$alias] = $tabType;
        }
        return $tabTypes;
    }

    /**
     * fetch a tab type using either its tabTypeId or alias
     * @param string $tabTypeId
     * @return TabType
     */
    public function get(string $tabTypeId): TabType
    {
        $query = "SELECT * FROM abc_tabtypes WHERE tabTypeId = '$tabTypeId' OR alias = '$tabTypeId'";
        $object = $this->throwableQuery($query, QueryType::SINGLE_OBJECT);
        return new TabType($object);
    }

}
