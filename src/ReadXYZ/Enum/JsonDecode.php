<?php


namespace App\ReadXYZ\Enum;

use App\ReadXYZ\Helpers\PhonicsException;
use MyCLabs\Enum\Enum;

class JsonDecode extends Enum
{
    const RETURN_STDCLASS = false;
    const RETURN_ASSOCIATIVE_ARRAY = true;

    /**
     * @param string $json
     * @param bool $returnType
     * @return mixed
     * @throws PhonicsException
     */
    public static function decode(string $json, bool $returnType=JsonDecode::RETURN_STDCLASS)
    {
        try {
            return json_decode($json, $returnType, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $ex) {
            throw new PhonicsException("JSON decode error. " . $ex->getMessage(), 0, $ex);
        }

    }

    /**
     * @param string $filename
     * @param bool $returnType
     * @return mixed
     * @throws PhonicsException
     */
    public static function decodeFile(string $filename, bool $returnType=JsonDecode::RETURN_STDCLASS)
    {
        $json = file_get_contents($filename, false, null);
        try {
            return self::decode($json, $returnType);
        } catch (PhonicsException $ex) {
            throw new PhonicsException("JSON decode error. " . $ex->getMessage(), 0, $ex);
        }
    }
}
