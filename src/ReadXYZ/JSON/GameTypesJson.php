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

    private array $aliasMap = [];
    /**
     * @var stdClass[]
     */
    private array $universalGames = [];
    /**
     * TabTypeJson constructor.
     * @see https://goessner.net/articles/JsonPath/
     * @throws PhonicsException
     */
    private function __construct()
    {
        $this->baseConstruct('abc_gametypes.json', 'gameTypeId');
        $this->makeMap();
        foreach ($this->objects as $object) {
            if(not(empty($object->url))) $this->universalGames[] = $object;
        }
    }

    private function makeMap()
    {
        $key = $this->primaryKey;
        foreach ($this->objects as $object) {
            $this->map[$object->$key] = $object;
            $this->aliasMap[$object->gameTypeId] = $object->gameTypeId;
            $this->aliasMap[$object->gameTitle] = $object->gameTypeId;
        }
    }

    /**
     * @param string $tag
     * @return string|false
     */
    private function getId(string $tag)
    {
        if (empty($tag)) return false;
        $id = $this->aliasMap[$tag] ?? '';
        if (empty($id)) return false;
        return $id;
    }

    public function get(string $tag): ?object
    {
        $id = $this->getId($tag);
        if ($id === false) return null;
        return $this->map[$id];
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
        return $this->universalGames;
    }

}
