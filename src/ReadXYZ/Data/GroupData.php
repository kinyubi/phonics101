<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\QueryType;
use App\ReadXYZ\Enum\Sql;
use App\ReadXYZ\Enum\Throwable;
use App\ReadXYZ\Helpers\PhonicsException;
use stdClass;

class GroupData extends AbstractData
{
    public function __construct()
    {
        parent::__construct('abc_groups', 'groupCode');
        $this->booleanFields = ['active'];
    }

// ======================== PUBLIC METHODS =====================

    /**
     * @throws PhonicsException
     */
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

    /**
     * @param string $groupName
     * @param string $throwOnNotFound
     * @return string
     * @throws PhonicsException
     */
    public function getGroupCode(string $groupName, string $throwOnNotFound=Throwable::NOT_FOUND_IS_VALID): string
    {
        $name = $this->smartQuotes($groupName);
        $query = "SELECT groupCode FROM abc_groups WHERE groupName = $name OR  groupDisplayAs = $name";
        return $this->throwableQuery($query, QueryType::SCALAR, $throwOnNotFound);
    }

    /**
     * @return string[] an associative array of groupName => displayAs
     * @throws PhonicsException
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

    /**
     * Given the group code (or key) returns the group name. Can be told to throw on not found or return empty
     * @param string $groupKey
     * @param string $throwOnNotFound a valid Throwable enum string
     * @return string the group name if found or empty
     * @throws PhonicsException
     */
    public function getGroupName(string $groupKey, string $throwOnNotFound=Throwable::NOT_FOUND_IS_VALID): string
    {
        $name = $this->smartQuotes($groupKey);
        $query = "SELECT groupName FROM abc_groups WHERE groupName = $name OR  groupDisplayAs = $name OR groupCode = $name";
        return $this->throwableQuery($query, QueryType::SCALAR, $throwOnNotFound) ?? '';
    }

    /**
     * @param string $whereClause
     * @param string $throwOnNotFound
     * @return stdClass[]
     * @throws PhonicsException
     */
    public function getGroupObjects(string $whereClause = '', string $throwOnNotFound=Throwable::NOT_FOUND_IS_VALID)
    {
        $query = "SELECT * FROM vw_group_with_keychain $whereClause ORDER BY ordinal";
        return $this->throwableQuery($query, QueryType::STDCLASS_OBJECTS, $throwOnNotFound);
    }

    /**
     * Inserts or updates a stdClass object into abc_groups
     * @param stdClass $group
     * @param int $ordinal
     * @return DbResult good result is the affected count.
     * @throws PhonicsException
     */
    public function insertOrUpdate(stdClass $group, int $ordinal): DbResult
    {
        $active = isset($group->active) ? $this->boolToEnum($group->active) :  Sql::ACTIVE;
        $query = <<<EOT
    INSERT INTO abc_groups(groupCode, groupName, groupDisplayAs, ordinal, active)
        VALUES('{$group->groupCode}', '{$group->groupName}', '{$group->displayAs}', $ordinal, '$active')
        ON DUPLICATE KEY UPDATE 
        groupName = '{$group->groupName}',
        groupDisplayAs = '{$group->displayAs}',
        ordinal = $ordinal,
        active = '$active'
EOT;
        return $this->query($query, QueryType::AFFECTED_COUNT);
    }

}
