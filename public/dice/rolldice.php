<?php
$request_uri_exists = array_key_exists('REQUEST_URI', $_SERVER);
// valid face/pips combinations: white/red, white/black, white/gray, black/white, black/red, */bdpq
// if nothing is specified it will default to white/#00aabe
$face = 'white';
$pips = 'turquoise';

if ($request_uri_exists) {
    $uri = parse_url(rawurldecode($_SERVER['REQUEST_URI']));
    if (isset($uri['query'])) {
        $query = $uri['query'];
        foreach (explode('&', $query) as $chunk) {
            $param = explode('=', $chunk);
            if ($param) {
                switch ($param[0]) {
                    case 'face':
                        $face = $param[1];
                        break;
                    case 'pips':
                        $pips = $param[1];
                        break;
                }
            }
        }
    }
}

$die_file = 'images/' . $face . '_die_' . $pips . '_pips.jpg';
if ('bdpq' == $pips) {
    $die_file = 'images/bdpq_die.jpg';
} elseif ('turquoise' == $pips) {
    $die_file = 'images/turquoise_die.jpg';
}

if (!file_exists($die_file)) {
    $die_file = 'images/turquoise_die.jpg';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"content="width=device-width,initial-scale=1,maximum-scale=1"/>
    <title>Dice</title>

    <script src="/js/jquery-3.5.1.min.js"></script>
    <link rel="stylesheet" href="/css/dieRoll.css" media="all">
    <script src="/js/dieRoll.js"></script>
</head>
<body>
<!-- id for the die must be passed into dieRoll(dieId, valueId) function (i.e. dieRoll('dice', 'diceValue') -->
<div id="rollContainer" class="die-rollBox" onclick="dieRoll('dice', 'diceValue')">
    <div id="dice" class="die" style="transform: translate(0px, 0px) rotateX(1080deg) rotateY(-180deg);">
        <div class="die-face bdp-die-img die-front"></div>
        <div class="die-face bdp-die-img die-back"></div>
        <div class="die-face bdp-die-img die-left"></div>
        <div class="die-face bdp-die-img die-bottom"></div>
        <div class="die-face bdp-die-img die-top"></div>
        <div class="die-face bdp-die-img die-right"></div>
    </div>
    <p style="display:none" id="diceValue">0</p>
</div>
</body>
</html>


