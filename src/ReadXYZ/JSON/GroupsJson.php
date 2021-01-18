<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Helpers\PhonicsException;

/**
 * Class GroupsJson (singleton) associative array of group objects with groupCode as key
 * Fields are groupCode, groupName,  active, ordinal
 * @package App\ReadXYZ\JSON
 */
class GroupsJson
{
    use JsonTrait;
    protected static GroupsJson   $instance;

    protected array $aliasMap = [];
    protected array $nameFromCodeMap = [];
    protected array $codeFromNameMap = [];

    /**
     * TabTypeJson constructor.
     * @see https://goessner.net/articles/JsonPath/
     * @throws PhonicsException
     */
    protected function __construct()
    {
        $this->baseConstruct('abc_groups.json', 'groupCode');
        $this->makeMap();
    }

    /**
     * overrides parent::makeMap. This creates a groupCode lookup table with groupCode and groupName keys
     */
    protected function makeMap(): void
    {
        foreach ($this->objects as $object) {
            $this->map[$object->groupCode] = $object;
            $this->aliasMap[$object->groupName] = $object->groupCode;
            $this->aliasMap[$object->groupCode] = $object->groupCode;
            $this->nameFromCodeMap[$object->groupCode] = $object->groupName;
            $this->codeFromNameMap[$object->groupName] = $object->groupCode;
        }
    }

    /**
     * @param string $groupTag
     * @return object|null
     */
    public function get(string $groupTag): ?object
    {
        $key = $this->aliasMap[$groupTag] ?? '';
        if (empty($key)) return null;
        return  $this->map[$key];
    }

    /**
     * input can be a groupName or groupCode
     * @param string $groupTag
     * @return string|false the associated group code
     */
    public function getGroupCode(string $groupTag)
    {
        if (empty($groupTag)) return false;
        $code = $this->aliasMap[$groupTag] ?? '';
        return empty($code) ? false : $code;
    }

    /**
     * input can be a groupName or groupCode
     * @param string $groupTag
     * @return string|false the associated group code
     */
    public function getGroupName(string $groupTag)
    {
        if (empty($groupTag)) return false;
        $code = $this->aliasMap[$groupTag] ?? '';
        if (empty($code)) return false;
        return $this->nameFromCodeMap[$code];
    }

    /**
     * input can be a groupName or groupCode
     * @param string $groupTag
     * @return int|false
     */
    public function getGroupOrdinal(string $groupTag)
    {
        $obj = $this->get($groupTag);
        if ($obj == null) return false;
        return $obj->ordinal;
    }

    public function getGroupCodeToNameMap(): array
    {
        return $this->nameFromCodeMap;
    }

}
