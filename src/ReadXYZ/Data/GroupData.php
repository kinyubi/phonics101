<?php


namespace App\ReadXYZ\Data;


use App\ReadXYZ\Enum\RecordType;
use App\ReadXYZ\Models\BoolWithMessage;
use RuntimeException;
use stdClass;

class GroupData extends AbstractData
{
    public function __construct()
    {
        parent::__construct('abc_groups');
    }

    public function create(): void
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
        $result = $this->db->queryStatement($query);
        if ($result->failed()) {
            throw new RuntimeException($this->db->getErrorMessage());
        }
    }

    public function insertMany(array $groups): int
    {
        $query = 'INSERT INTO abc_groups(groupCode, groupName, groupDisplayAs) VALUES(?, ?, ?)';
        $statement = $this->db->getPreparedStatement($query);
        $statement->bind_param('sss', $groupCode, $groupName, $groupDisplayAs);
        $group = 4;
        foreach ($groups as $groupName => $groupDisplayAs)
        {
            $groupCode = 'G' . str_pad(strval($group), 2, '0', STR_PAD_LEFT);
            $statement->execute();
            $group+=4;
        }

        $statement->close();

        return $this->getCount();

    }

    public function insertOrUpdate(stdClass $group, int $ordinal): BoolWithMessage
    {
        $query = <<<EOT
    INSERT INTO abc_groups(groupCode, groupName, groupDisplayAs, ordinal)
        VALUES('{$group->groupId}', '{$group->groupName}', '{$group->displayAs}', $ordinal)
        ON DUPLICATE KEY UPDATE 
        groupName = '{$group->groupName}',
        groupDisplayAs = '{$group->displayAs}',
        ordinal = $ordinal
EOT;
        return $this->db->queryStatement($query);
    }

    public function getGroupCode(string $groupName): string
    {
        $name = $this->smartQuotes($groupName);
        $query = "SELECT groupCode FROM abc_groups WHERE groupName = $name OR  groupDisplayAs = $name";
        return $this->throwableQuery($query, RecordType::get(RecordType::SCALAR));
    }

    public function getActiveGroupRecords()
    {
        $query = "SELECT groupCode, groupName, groupDisplayAs FROM abc_groups WHERE active='Y' ORDER BY ordinal";
        $result = $this->db->queryRows($query);
        if ($result->failed()) {
            throw new RuntimeException($result->getErrorMessage());
        }
        return $result->getResult();
    }

    /**
     * @return string[] an associative array of groupName => displayAs
     */
    public function getAllActiveGroups()
    {
        $groups = $this->getActiveGroupRecords();
        $array = [];
        foreach ($groups as $group) {
            $array[$group['groupName']] = $group['groupDisplayAs'];
        }
        return $array;
    }

    public function getGroupName(string $groupKey): DbResult
    {
        $name = $this->smartQuotes($groupKey);
        $query = "SELECT groupName FROM abc_groups WHERE groupName = $name OR  groupDisplayAs = $name OR groupCode = $name";
        return $this->db->queryAndGetScalar($query);
    }

}
