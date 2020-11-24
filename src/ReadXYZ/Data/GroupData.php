<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Enum\Sql;
use stdClass;

class GroupData extends AbstractData
{
    public function __construct()
    {
        parent::__construct('abc_groups', 'groupCode');
        $this->booleanFields = ['active'];
    }

// ======================== PUBLIC METHODS =====================
    public function _create(): void
    {
        $query = <<<EOT
CREATE TABLE `abc_groups` (
	`groupCode` VARCHAR(32) NOT NULL,
	`groupName` VARCHAR(128) NOT NULL,
	`groupDisplayAs` VARCHAR(128) NOT NULL,
	`active` ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'use ActiveType class',
	`ordinal` TINYINT(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (`groupCode`)
) COLLATE='utf8_general_ci' ENGINE=InnoDB ;
EOT;
        $this->throwableQuery($query, QueryType::STATEMENT);
    }

    public function getGroupCode(string $groupName): string
    {
        $name = $this->smartQuotes($groupName);
        $query = "SELECT groupCode FROM abc_groups WHERE groupName = $name OR  groupDisplayAs = $name";
        return $this->throwableQuery($query, QueryType::SCALAR);
    }

    /**
     * @return string[] an associative array of groupName => displayAs
     */
    public function getGroupExtendedAssocArray()
    {
        $groups = $this->getGroupObjects("WHERE active='Y'");
        $array = [];
        foreach ($groups as $group) {
            $array[$group->groupName] = $group;
        }
        return $array;
    }

    public function getGroupName(string $groupKey): string
    {
        $name = $this->smartQuotes($groupKey);
        $query = "SELECT groupName FROM abc_groups WHERE groupName = $name OR  groupDisplayAs = $name OR groupCode = $name";
        return $this->throwableQuery($query, QueryType::SCALAR);
    }

    /**
     * @param string $whereClause
     * @return stdClass[]
     */
    public function getGroupObjects(string $whereClause = '')
    {
        $query = "SELECT * FROM vw_group_with_keychain $whereClause ORDER BY ordinal";
        return $this->throwableQuery($query, QueryType::STDCLASS_OBJECTS);
    }

    /**
     * @param stdClass $group
     * @param int $ordinal
     * @return DbResult
     */
    public function insertOrUpdate(stdClass $group, int $ordinal): DbResult
    {
        $active = isset($group->active) ? $this->boolToEnum($group->active) :  Sql::ACTIVE;
        $query = <<<EOT
    INSERT INTO abc_groups(groupCode, groupName, groupDisplayAs, ordinal, active)
        VALUES('{$group->groupId}', '{$group->groupName}', '{$group->displayAs}', $ordinal, '$active')
        ON DUPLICATE KEY UPDATE 
        groupName = '{$group->groupName}',
        groupDisplayAs = '{$group->displayAs}',
        ordinal = $ordinal,
        active = '$active'
EOT;
        return $this->query($query, QueryType::AFFECTED_COUNT);
    }

}
