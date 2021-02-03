<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Helpers\PhonicsException;

/**
 * Class KeyChainJson fields are  keychainCode, fileName, friendlyName, groupCode
 * @package App\ReadXYZ\JSON
 */
class KeyChainJson
{
    use JsonTrait;
    protected static KeyChainJson   $instance;

    /**
     * KeyChainJson constructor.
     * @throws PhonicsException
     */
    protected function __construct()
    {
        $this->baseConstruct('abc_keychain.json', 'groupCode');
        $this->baseMakeMap();
    }

    /**
     * @param string $key   a groupCode or a groupName
     * @return object|null
     */
    public function get(string $key): ?object
    {
        $code = GroupsJson::getInstance()->getGroupCode($key) ?? null;
        return $code ? $this->persisted['map'][$code] : null;
    }

    public function exists(string $key): bool
    {
        $code = GroupsJson::getInstance()->getGroupCode($key) ?? null;
        if ($code == null) return false;
        return $this->persisted['map'][$code] != null;
    }

}
