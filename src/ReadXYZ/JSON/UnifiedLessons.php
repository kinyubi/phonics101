<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Enum\JsonDecode;
use App\ReadXYZ\Helpers\Util;
use stdClass;

class UnifiedLessons
{
    /**
     * @param bool $asAssociativeArray
     * @return mixed
     * @throws \JsonException
     */
    private static function getData(bool $returnType = JsonDecode::RETURN_STDCLASS)
    {
        $inputFile = Util::getReadXyzSourcePath('resources/unifiedLessons.json');
        $json = file_get_contents($inputFile);
        return JsonDecode::decode($json, $returnType);
    }

    /**
     * @return stdClass
     * @throws \JsonException
     */
    public static function getDataAsStdClass(): stdClass
    {
        return self::getData(JsonDecode::RETURN_STDCLASS);
    }

    /**
     * @return array
     * @throws \JsonException
     */
    public static function getDataAsAssociativeArray(): array
    {
        return self::getData(JsonDecode::RETURN_ASSOCIATIVE_ARRAY);
    }

}
