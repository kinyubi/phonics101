<?php


namespace App\ReadXYZ\JSON;


/**
 * Class KeyChainJson fields are  keychainCode, fileName, friendlyName, groupCode
 * @package App\ReadXYZ\JSON
 */
class KeyChainJson extends AbstractJson
{
    protected function __construct()
    {
        parent::__construct('abc_keychain.json', 'groupCode');
    }

    public static function getInstance()
    {
        return parent::getInstanceBase(__CLASS__);
    }
}
