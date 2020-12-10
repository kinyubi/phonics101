<?php


namespace App\ReadXYZ\Lessons;


use App\ReadXYZ\Data\GroupData;
use App\ReadXYZ\Helpers\PhonicsException;

class Groups
{

    private static Groups $instance;

    private array $groups;

    /**
     * Groups constructor.
     * @throws PhonicsException on ill-formed SQL
     */
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

    /**
     * @param string $groupCode
     * @return string
     * @throws PhonicsException on ill-formed SQL
     */
    public function getDisplayAs(string $groupCode): string
    {
        foreach($this->groups as $group) {
            if (($group->groupCode == $groupCode) || $group->groupName == $groupCode) return $group->groupDisplayAs;
        }
        throw new PhonicsException("$groupCode is not a valid groupCode or groupName.");
    }

    /**
     * @param string $groupCode
     * @return string
     * @throws PhonicsException on ill-formed SQL
     */
    public function getGroupCode(string $groupCode): string
    {
        foreach($this->groups as $group) {
            if (($group->groupDisplayAs == $groupCode) || $group->groupName == $groupCode) return $group->groupCode;
        }
        throw new PhonicsException("$groupCode is not a valid groupCode or groupName.");
    }

    /**
     * @param string $groupCode
     * @return string
     * @throws PhonicsException on ill-formed SQL
     */
    public function getGroupName(string $groupCode): string
    {
        foreach($this->groups as $group) {
            if (($group->groupDisplayAs == $groupCode) || $group->groupCode == $groupCode) return $group->groupName;
        }
        throw new PhonicsException("$groupCode is not a valid groupCode or groupName.");
    }
}
