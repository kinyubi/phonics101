<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Helpers\PhonicsException;
use stdClass;

/**
 * Class GameTypesJson stdClass(gameTypeId, gameTitle, thumbNailUrl, cssClass, belongsOnTab, url, active)
 * parent methods: get($key), getAll(), getCount()
 * makeMap is not overridden.
 * @package App\ReadXYZ\JSON
 */
class GameTypesJson
{
    use JsonTrait;

    private static GameTypesJson   $instance;
/*
     gameTypeId: string,
     gameTitle: string,
     thumbNailUrl: string,      (example: /images/sidebar/alien.jpg)
     cssClass: string,          (games,tic-tac-toe,sound-box. default is games)
     belongsOnTab: string,      (must be a tabTypeId in abc_tabtypes.json
     url: string,               ( if this has a value, the record will be added to universalGames
     active: bool
 */


    /**
     * @var stdClass[]
     */

    /**
     * TabTypeJson constructor.
     * @see https://goessner.net/articles/JsonPath/
     * @throws PhonicsException
     */
    private function __construct()
    {
        $this->cachingEnabled = false;
        $this->baseConstruct('abc_gametypes.json', 'gameTypeId');
        $this->makeMap();
    }

    private function makeMap()
    {
        if ($this->cacheUsed) return;
        $timer = $this->startTimer('creating object' . __CLASS__ . '.');
        $this->persisted['aliasMap'] = [];
        $key = $this->persisted['primaryKey'];
        foreach ($this->persisted['objects'] as $object) {
            $this->persisted['map'][$object->$key] = $object;
            $this->persisted['aliasMap'] [$object->gameTypeId] = $object->gameTypeId;
            $this->persisted['aliasMap'] [$object->gameTitle] = $object->gameTypeId;
        }
        $this->persisted['universalGames'] = [];
        foreach ($this->persisted['objects'] as $object) {
            if(not(empty($object->url))) $this->persisted['universalGames'][] = $object;
        }
        $this->stopTimer($timer);
        $this->cacheData();
    }

    /**
     * @param string $tag
     * @return string|false
     */
    private function getId(string $tag)
    {
        if (empty($tag)) return false;
        $id = $this->persisted['aliasMap'][$tag] ?? '';
        if (empty($id)) return false;
        return $id;
    }

    public function get(string $tag): ?object
    {
        $id = $this->getId($tag);
        if ($id === false) return null;
        $game = $this->persisted['map'][$id];
        return clone $game;
    }

    public function exists(string $tag): bool
    {
        return $this->get($tag) != null;
    }

    /**
     * returns an array of stdClass game objects
     * @return stdClass[]
     */
    public function getUniversal(): array
    {
        return $this->persisted['universalGames'];
    }

}
