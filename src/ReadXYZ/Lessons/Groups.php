<?php


namespace App\ReadXYZ\Lessons;


use App\ReadXYZ\Data\GroupData;
use LogicException;

class Groups
{

    private static Groups $instance;

    private array $groups;

    private function __construct()
    {
        $data = new GroupData();
        $whereClause = (defined('TESTING_IN_PROGRESS')) ? '' : "WHERE active = 'Y'";
        $this->groups = $data->getGroupObjects($whereClause);
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {self::$instance = new Groups();}

        return self::$instance;
    }

    public function getDisplayAs(string $groupId): string
    {
        foreach($this->groups as $group) {
            if (($group->groupCode == $groupId) || $group->groupName == $groupId) return $group->groupDisplayAs;
        }
        throw new LogicException("$groupId is not a valid groupCode or groupName.");
    }

    public function getGroupCode(string $groupId): string
    {
        foreach($this->groups as $group) {
            if (($group->groupDisplayAs == $groupId) || $group->groupName == $groupId) return $group->groupCode;
        }
        throw new LogicException("$groupId is not a valid groupCode or groupName.");
    }

    public function getGroupName(string $groupId): string
    {
        foreach($this->groups as $group) {
            if (($group->groupDisplayAs == $groupId) || $group->groupCode == $groupId) return $group->groupName;
        }
        throw new LogicException("$groupId is not a valid groupCode or groupName.");
    }
}
