<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Lessons\TabType;
use App\ReadXYZ\Models\BoolWithMessage;
use RuntimeException;
use stdClass;

class TabTypesData extends AbstractData
{
    public function __construct()
    {
        parent::__construct('abc_students');
    }

    public function create()
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
        $result = $this->db->queryStatement($query);
        if ($result->failed()) {
            throw new RuntimeException($this->db->getErrorMessage());
        }
    }

    public function insertOrUpdateStd(stdClass $tabType): BoolWithMessage
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
        return $this->db->queryStatement($query);
    }

    /**
     * @return TabType[]
     */
    public function getAll(): array
    {
        $result = $this->db->queryRows("SELECT * FROM abc_tabtypes");
        if ($result->failed()) {
            throw new RuntimeException($this->db->getErrorMessage());
        }
        $records = $result->getResult();
        $tabTypes = [];
        foreach ($records as $record) {
            $id = $record['tabTypeId'];
            $alias = $record['alias'];
            $tabType = $this->sqlToTabType($record);
            $tabTypes[$id] = $tabType;
            if (! empty($alias)) {
                $tabTypes[$alias] = $tabType;
            }
        }
        return $tabTypes;
    }

    public function get(string $tabTypeId): TabType
    {
        $query = "SELECT * FROM abc_tabtypes WHERE tabTypeId = '$tabTypeId' OR alias = '$tabTypeId'";
        $result = $this->db->queryRecord($query);
        if ($result->failed()) {
            throw new RuntimeException($this->db->getErrorMessage());
        }
        return $this->sqlToTabType($result->getResult());
    }

    private function sqlToTabType(array $record): TabType
    {
        $object = (object)[
            'tabTypeId'    => strtolower($record['tabTypeId']),
            'tabDisplayAs' => $record['tabDisplayAs'],
            'alias'        => strtolower($record['alias']),
            'script'       => $record['script'],
            'imageFile'    => $record['iconUrl']
        ];
        return new TabType($object);
    }

}
