<?php

// parameters:
// P1: lessonName
// P2: initialTabName

use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Lessons\Lessons;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Twig\LessonTemplate;

require 'autoload.php';

if (Util::isLocal()) {
    error_reporting(E_ALL | E_STRICT);
}

$session = new Session();

$lessonName = $_REQUEST['P1'] ?? $_REQUEST['lessonName'] ?? '';
$initialTabName = $_REQUEST['P2'] ?? $_REQUEST['initialTabName'] ?? '';

if (empty($lessonName)) {
    throw new InvalidArgumentException('No parameter for lesson name found.');
}

$lessons = Lessons::getInstance();
if (!($lessons->lessonExists($lessonName))) {
    throw new InvalidArgumentException("$lessonName is not a valid lesson name.");
}

$session->updateLesson($lessonName);
$lessonTemplate = new LessonTemplate($lessonName, $initialTabName);
$lessonTemplate->display();
