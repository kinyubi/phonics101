<?php

// HTTP GET target: P1 studentCode

use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Twig\LessonListTemplate;

require 'autoload.php';

if (Util::isLocal()) {
    error_reporting(E_ALL | E_STRICT);
}

$studentCode = $_REQUEST['studentCode'] ?? $_REQUEST['P1'];
if (empty($studentCode)) {
    exit('You should not arrive here without student id set.');
}
if (Session::hasNoSession()) {
    throw new RuntimeException("Cannot select student prior to selecting user.");
}
$session = new Session();
$session->updateStudent($studentCode);

$lessonList = new LessonListTemplate();
$lessonList->display();
