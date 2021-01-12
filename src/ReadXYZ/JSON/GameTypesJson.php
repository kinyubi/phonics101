<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Helpers\PhonicsException;

/**
 * Class GameTypesJson stdClass(gameTypeId, gameTitle, thumbNailUrl, cssClass, belongsOnTab, url, active)
 * parent methods: get($key), getAll(), getCount()
 * makeMap is not overridden.
 * @package App\ReadXYZ\JSON
 */
class GameTypesJson extends AbstractJson
{
/*
     gameTypeId: string,
     gameTitle: string,
     thumbNailUrl: string,      (example: /images/sidebar/alien.jpg)
     cssClass: string,          (games,tic-tac-toe,sound-box. default is games)
     belongsOnTab: string,      (must be a tabTypeId in abc_tabtypes.json
     url: string,               ( if this has a value, the record will be added to universalGames
     active: bool
 */

    protected array $universalGames = [];
    /**
     * TabTypeJson constructor.
     * @see https://goessner.net/articles/JsonPath/
     * @throws PhonicsException
     */
    protected function __construct()
    {
        parent::__construct('abc_gametypes.json', 'gameTypeId');
    }

    public static function getInstance()
    {
        return parent::getInstanceBase(__CLASS__);
    }

    /**
     * returns an array of stdClass game objects
     * @return array
     */
    public function getUniversal(): array
    {
        return $this->universalGames;
    }

}
