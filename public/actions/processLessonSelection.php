<?php

// target for Twigs::renderLessonList
// parameters:
// P1: lessonName
// P2: initialTabName

use ReadXYZ\Helpers\Util;
use ReadXYZ\Lessons\Lessons;
use ReadXYZ\Models\Cookie;
use ReadXYZ\Models\Identity;
use ReadXYZ\Models\Student;
use ReadXYZ\Twig\LessonTemplate;
use ReadXYZ\Twig\Twigs;

require 'autoload.php';

if (Util::isLocal()) {
    error_reporting(E_ALL | E_STRICT);
}

if (!Cookie::getInstance()->tryContinueSession()) {
    throw new RuntimeException('Unable to find session.');
}

$lessonName = $_REQUEST['P1'] ?? $_REQUEST['lessonName'] ?? '';
$initialTabName = $_REQUEST['P2'] ?? $_REQUEST['initialTabName'] ?? '';

if (empty($lessonName)) {
    throw new InvalidArgumentException('No parameter for lesson name found.');
}
$identity = Identity::getInstance();
$lessons = Lessons::getInstance();

if (!($lessons->lessonExists($lessonName))) {
    throw new InvalidArgumentException("$lessonName is not a valid lesson name.");
}

Student::getInstance()->saveLessonSelection($lessonName);
$lessonTemplate = new LessonTemplate($lessonName, $initialTabName);
$lessonTemplate->displayLesson();
