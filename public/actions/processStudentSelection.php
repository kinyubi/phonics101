<?php

// HTTP GET target: P1 studentId

use ReadXYZ\Helpers\Util;
use ReadXYZ\Models\Cookie;
use ReadXYZ\Models\Identity;
use ReadXYZ\Twig\LessonListTemplate;
use ReadXYZ\Twig\Twigs;

require 'autoload.php';

if (Util::isLocal()) {
    error_reporting(E_ALL | E_STRICT);
}

$studentId = $_REQUEST['studentId'] ?? $_REQUEST['P1'];
if (empty($studentId)) {
    exit('You should not arrive here without student id set.');
}
// be sure to $identity->setStudent($studentId)
$identity = Identity::getInstance();
$identity->setStudent($studentId);
$identity->savePersistentState();

$lessonList = new LessonListTemplate();
$lessonList->display();
