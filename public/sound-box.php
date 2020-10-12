<?php

use ReadXYZ\Models\Cookie;
use ReadXYZ\Twig\TwigFactory;

require 'autoload.php';

Cookie::getInstance()->tryContinueSession();

$cookie = $_COOKIE['readxyz_sound_box'] ?? '3blue';
$cookieCount = substr($cookie,0, 1);
$cookieColor = substr($cookie, 1);

$parts = parse_url($_SERVER['REQUEST_URI']);
parse_str($parts['query'] ?? '', $query);

$objectCount = $_REQUEST['count'] ?? intval($query['count'] ?? $cookieCount);
$color = $_REQUEST['color'] ?? $query['color'] ?? $cookieColor;


require __DIR__ . '/autoload.php';

$args = ['count' => $objectCount, 'color' => $color, 'lessonName' => Cookie::getInstance()->getCurrentLesson() ];

echo TwigFactory::getInstance()->renderBlock('sound_box', 'soundBox', $args);
