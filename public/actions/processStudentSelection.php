<?php

// HTTP GET target: P1 studentId

use App\ReadXYZ\Helpers\Util;
use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Twig\LessonListTemplate;

require 'autoload.php';

if (Util::isLocal()) {
    error_reporting(E_ALL | E_STRICT);
}

$studentId = $_REQUEST['studentId'] ?? $_REQUEST['P1'];
if (empty($studentId)) {
    exit('You should not arrive here without student id set.');
}
if (Session::hasNoSession()) {
    throw new RuntimeException("Cannot select student prior to selecting user.");
}
$session = new Session();
$session->updateStudent($studentId);

$lessonList = new LessonListTemplate();
$lessonList->display();
