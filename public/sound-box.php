<?php

use ReadXYZ\Twig\TwigFactory;

$objectCount = $_REQUEST['count'] ?? 3;
$color = $_REQUEST['color'] ?? 'blue';


require __DIR__ . '/autoload.php';

echo TwigFactory::getInstance()->renderBlock('sound_box', 'soundBox');
