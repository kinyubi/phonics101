<?php

use App\ReadXYZ\Enum\JsonDecode;
use App\ReadXYZ\JSON\LessonsJson;


require '../autoload.php';
$lessonsJson = LessonsJson::getInstance();
$json = file_get_contents('unifiedLessons.json');
$data = $lessonsJson->decode($this->json, JsonDecode::RETURN_STDCLASS);
$version = $lessonsJson->getImplicitVersion();
$lessons = $data->lessons->blending;

$lessonsJson->convert($lessons, 'abc_lessons_', 'abc_lessons_' . $version . '.json');
