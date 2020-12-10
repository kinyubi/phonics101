<?php


namespace App\ReadXYZ\Enum;






use JsonException;

class JsonDecode extends \MyCLabs\Enum\Enum
{
    const RETURN_STDCLASS = false;
    const RETURN_ASSOCIATIVE_ARRAY = true;

    /**
     * @param string $json
     * @param bool $returnType
     * @return mixed
     * @throws JsonException
     */
    public static function decode(string $json, bool $returnType=JsonDecode::RETURN_STDCLASS)
    {
        return json_decode($json, $returnType, 512, JSON_THROW_ON_ERROR);
    }
}
