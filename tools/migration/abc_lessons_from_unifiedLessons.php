<?php

use App\ReadXYZ\Enum\JsonDecode;
use App\ReadXYZ\JSON\AbstractJson;
use App\ReadXYZ\JSON\UnifiedLessons;

require '../autoload.php';

$json = file_get_contents('unifiedLessons.json');
$data = JsonDecode::decode($this->json, JsonDecode::RETURN_STDCLASS);
$version = AbstractJson::getImplicitVersion();
$lessons = $data->lessons->blending;

AbstractJson::convert($lessons, 'abc_lessons_' . $version . '.json');
