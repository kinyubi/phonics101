<?php

use ReadXYZ\Helpers\Util;
use ReadXYZ\Twig\LoginTemplate;
use ReadXYZ\Twig\Page;

require 'autoload.php';

if (Util::isLocal()) {
    error_reporting(E_ALL | E_STRICT);
}

$action = $_REQUEST['action'] ?? 'none';

$page = new Page('ReadXYZ Login');
$errorMessage = '';
if ('incomplete' == $action) {
    $errorMessage = 'A username and password must be specified.';
} elseif ('fail' == $action) {
    $errorMessage = 'The specified username or password was incorrect.';
}
(new LoginTemplate($errorMessage))->display();

