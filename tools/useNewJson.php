<?php

use ReadXYZ\Helpers\Util;
use ReadXYZ\Lessons\Lessons;

require dirname(__DIR__) . '/src/ReadXYZ/autoload.php';

Util::fakeLogin();

$lessons = Lessons::getInstance();
$inputFile = Util::getReadXyzSourcePath('resources/unifiedLessons.json');
$json = file_get_contents($inputFile);
$blending = json_decode($json);
$blendingArray = json_decode($json, true);
$lessons->writeAllLessons('c:/users/carlb/desktop/full_lessons.json');
