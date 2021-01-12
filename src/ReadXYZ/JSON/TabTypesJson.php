<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Helpers\PhonicsException;
use stdClass;

/**
 * Class TabTypesJson. [ [$tabTypeId => TabType object ] ]
 * @package App\ReadXYZ\JSON
 */
class TabTypesJson extends AbstractJson
{
    protected array $aliasMap = [];

    /**
     * TabTypeJson constructor.
     * @see https://goessner.net/articles/JsonPath/
     * @throws PhonicsException
     */
    protected function __construct()
    {
        parent::__construct('abc_tabtypes.json', 'tabTypeId');
        $this->makeAliasMap();
    }

    public static function getInstance()
    {
        return parent::getInstanceBase(__CLASS__);
    }

    /**
     * creates a map of aliases we'll use for our overridden get method
     * @return void
     */
    protected function makeAliasMap(): void
    {
        foreach ($this->map as $key => $object) {
            $this->aliasMap[$object->tabTypeId] = $object->tabTypeId;
            $this->aliasMap[$object->tabDisplayAs] = $object->tabTypeId;
            foreach ($object->aliases as $alias) {
                $this->aliasMap[$alias] = $object->tabTypeId;
            }
        }
    }

    public function getTabId(string $tabId): string
    {
        return $this->aliasMap[$tabId] ?? '';
    }

    public function get(string $tabId): ?stdClass
    {
        $key = $this->aliasMap[$tabId] ?? '';
        if (empty($key)) {return null;}
        return $this->map[$key] ?? null;
    }



}
