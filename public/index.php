<?php

use ReadXYZ\Models\RouteMe;
use ReadXYZ\Helpers\Util;
use ReadXYZ\Models\Cookie;
use ReadXYZ\Twig\Twigs;

if (false !== strpos($_SERVER['HTTP_HOST'], '.test')) {
    error_reporting(E_ALL | E_STRICT);
}

require dirname(__DIR__) . '/src/ReadXYZ/autoload.php';

$cookie = Cookie::getInstance();
$mustLogin = empty($cookie->getUsername());
$query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
$action = strtolower($query['action'] ?? '');

$twigs = Twigs::getInstance();
if ($mustLogin or ('login' == $action)) {
    echo $twigs->login();
} else {
    RouteMe::autoLogin();
}
