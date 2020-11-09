<?php


namespace App\ReadXYZ\JSON;


use App\ReadXYZ\Helpers\Util;
use stdClass;

class UnifiedLessons
{
    private static function getData(bool $asAssociativeArray = false)
    {
        $inputFile = Util::getReadXyzSourcePath('resources/unifiedLessons.json');
        $json = file_get_contents($inputFile);
        return json_decode($json, $asAssociativeArray, 512, JSON_THROW_ON_ERROR);
    }

    public static function getDataAsStdClass(): stdClass
    {
        return self::getData(false);
    }

    public static function getDataAsAssociativeArray(): array
    {
        return self::getData(true);
    }

}
