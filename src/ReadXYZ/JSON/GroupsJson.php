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
        if ($this->cacheUsed) return;
        $timer = $this->startTimer('creating object' . __CLASS__ . '.');
        foreach ($this->persisted['objects'] as $object) {
            $this->persisted['map'][$object->groupCode] = $object;
            $this->persisted['aliasMap'][$object->groupName] = $object->groupCode;
            $this->persisted['aliasMap'][$object->groupCode] = $object->groupCode;
            $this->persisted['nameFromCodeMap'][$object->groupCode] = $object->groupName;
            $this->persisted['codeFromNameMap'][$object->groupName] = $object->groupCode;
        }
        $this->stopTimer($timer);
        $this->cacheData();
    }

    /**
     * @param string $groupTag
     * @return object|null
     */
    public function get(string $groupTag): ?object
    {
        $key = $this->persisted['aliasMap'][$groupTag] ?? '';
        if (empty($key)) return null;
        return  $this->persisted['map'][$key];
    }

    /**
     * input can be a groupName or groupCode
     * @param string $groupTag
     * @return string|false the associated group code
     */
    public function getGroupCode(string $groupTag)
    {
        if (empty($groupTag)) return false;
        $code = $this->persisted['aliasMap'][$groupTag] ?? '';
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
        $code = $this->persisted['aliasMap'][$groupTag] ?? '';
        if (empty($code)) return false;
        return $this->persisted['nameFromCodeMap'][$code];
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
        return $this->persisted['nameFromCodeMap'];
    }

}
