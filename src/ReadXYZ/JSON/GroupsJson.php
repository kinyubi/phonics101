<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Helpers\PhonicsException;

/**
 * Class GroupsJson (singleton) associative array of group objects with groupCode as key
 * Fields are groupCode, groupName,  active, ordinal
 * @package App\ReadXYZ\JSON
 */
class GroupsJson extends AbstractJson
{
    protected array $nameMap = [];
    protected array $codeMap = [];
    protected array $aliasMap = [];
    protected array $codes = [];
    protected array $names = [];

    /**
     * TabTypeJson constructor.
     * @see https://goessner.net/articles/JsonPath/
     * @throws PhonicsException
     */
    protected function __construct()
    {
        parent::__construct('abc_groups.json', 'groupCode');
    }

    /**
     * overrides parent::makeMap. This creates a groupCode lookup table with groupCode and groupName keys
     * @param array $objects
     */
    protected function makeMap(array $objects): void
    {
        foreach ($objects as $object) {
            $this->map[$object->groupCode] = $object;
            $this->aliasMap[$object->groupName] = $object->groupCode;
            $this->aliasMap[$object->groupCode] = $object->groupCode;
            $this->codes[] = $object->groupCode;
            $this->names[] = $object->groupName;
        }
    }

    /**
     * Fields are groupCode, groupName, active, ordinal
     * @return GroupsJson
     */
    public static function getInstance()
    {
        return parent::getInstanceBase(__CLASS__);
    }

    /**
     * input can be a groupName or groupCode
     * @param string $groupTag
     * @return string the associated group code
     */
    public function getGroupCode(string $groupTag): string
    {
        return $this->map[$this->aliasMap[$groupTag]]->groupCode ?? '';
    }

    /**
     * input can be a groupName or groupCode
     * @param string $groupTag
     * @return string the associated group code
     */
    public function getGroupName(string $groupTag): string
    {
        return $this->map[$this->aliasMap[$groupTag]]->groupName ?? '';
    }

    /**
     * input can be a groupName or groupCode
     * @param string $groupTag
     * @return int
     */
    public function getGroupOrdinal(string $groupTag): int
    {
        return $this->map[$this->aliasMap[$groupTag]]->ordinal ?? '';
    }

    public function getGroupCodes(): array
    {
        return $this->codes;
    }

    public function getGroupNames(): array
    {
        return $this->names;
    }


    public function getNameMap(): array
    {
        return $this->nameMap;
    }
}
