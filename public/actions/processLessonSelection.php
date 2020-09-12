<?php

// target for Twigs::renderLessonList
// parameters:
// P1: lessonName
// P2: initialTabName

use ReadXYZ\Helpers\Util;
use ReadXYZ\Lessons\Lessons;
use ReadXYZ\Models\Cookie;
use ReadXYZ\Models\Identity;
use ReadXYZ\Twig\Twigs;

require 'autoload.php';

if (Util::isLocal()) {
    error_reporting(E_ALL | E_STRICT);
}
$twigs = Twigs::getInstance();

if (!Cookie::getInstance()->tryContinueSession()) {
    echo $twigs->login('Login has expired (1).');
    exit;
}
$lessonName = '';
$lessonName = $_REQUEST['P1'] ?? $_REQUEST['lessonName'] ?? '';
$initialTabName = $_REQUEST['P2'] ?? $_REQUEST['initialTabName'] ?? '';
$useNextLessonButton = ($_REQUEST['P3'] ?? '0') != '0';
if (empty($lessonName)) {
    throw new InvalidArgumentException('No parameter for lesson name found.');
}
$identity = Identity::getInstance();
$lessons = Lessons::getInstance();

if (!($lessons->lessonExists($lessonName))) {
    throw new InvalidArgumentException("$lessonName is not a valid lesson name.");
}

$html = $twigs->renderLesson($lessonName, $initialTabName, $useNextLessonButton);
echo $html;