<?php


namespace App\ReadXYZ\Data;


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
	`active` TINYINT(4) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'bool',
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

    public function getGroupCode(string $groupName): DbResult
    {
        $query = "SELECT groupCode FROM abc_groups WHERE groupName = {$this->smartQuotes($groupName)}";
        return $this->db->queryAndGetScalar($query);
    }

}
