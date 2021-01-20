<?php

use App\ReadXYZ\Models\Session;
use App\ReadXYZ\Twig\TwigFactory;

require 'autoload.php';

Session::sessionContinue();

$cookie = $_COOKIE['readxyz_sound_box'] ?? '3blue';
$cookieCount = substr($cookie,0, 1);
$cookieColor = substr($cookie, 1);

$parts = parse_url($_SERVER['REQUEST_URI']);
parse_str($parts['query'] ?? '', $query);

$objectCount = $_REQUEST['count'] ?? intval($query['count'] ?? $cookieCount);
$color = $_REQUEST['color'] ?? $query['color'] ?? $cookieColor;


require __DIR__ . '/autoload.php';

$args = ['count' => $objectCount, 'color' => $color, 'lessonName' => Session::currentLesson() ];

echo TwigFactory::getInstance()->renderBlock('sound_box', 'soundBox', $args);
