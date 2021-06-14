<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Helpers\PhonicsException;
use stdClass;

/**
 * Class TabTypesJson. [ [$tabTypeId => TabType object ] ]
 * @package App\ReadXYZ\JSON
 */
class TabTypesJson
{
    use JsonTrait;
    protected static TabTypesJson   $instance;

    /**
     * TabTypeJson constructor.
     * @see https://goessner.net/articles/JsonPath/
     * @throws PhonicsException
     */

    public function __construct()
    {
        if (isset($_SESSION['isLetters'])) {
            $isLetters = $_SESSION['isLetters'];
            if($isLetters){
                $this->baseConstruct('abc_letters_tabtypes.json', 'tabTypeId');
            }
            else{
                $this->baseConstruct('abc_tabtypes.json', 'tabTypeId');
            }
        }
        else{
            $this->baseConstruct('abc_tabtypes.json', 'tabTypeId');
        }
        $this->makeMap();
    }

    /**
     * creates a map of aliases we'll use for our overridden get method
     * @return void
     */
    protected function makeMap(): void
    {
        if ($this->cacheUsed) return;
        $timer = $this->startTimer('creating object' . __CLASS__ . '.');
        $this->persisted['aliasMap'] = [];
        foreach ($this->persisted['objects'] as $object) {
            $this->persisted['map'][$object->tabTypeId] = $object;
            $this->persisted['aliasMap'][$object->tabTypeId] = $object->tabTypeId;
            $this->persisted['aliasMap'][$object->tabDisplayAs] = $object->tabTypeId;
            foreach ($object->aliases as $alias) {
                $this->persisted['aliasMap'][$alias] = $object->tabTypeId;
            }
        }
        $this->stopTimer($timer);
        $this->cacheData();
    }

    /**
     * @param string $tabId
     * @return string|false
     */
    public function getTabId(string $tabId)
    {
        return $this->persisted['aliasMap'][$tabId] ?? false;
    }

    /**
     * @param string $tabId
     * @return stdClass|null
     */
    public function get(string $tabId): ?stdClass
    {
        $key = $this->persisted['aliasMap'][$tabId] ?? '';
        if (empty($key)) {return null;}
        return clone $this->persisted['map'][$key] ;
    }



}
