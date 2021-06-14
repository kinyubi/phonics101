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
    <meta name="viewport"
          content="width=device-width,initial-scale=1,maximum-scale=1"/>
    <title>
        Dice</title>
    <style>
        body {
            overflow-x: hidden;
            overflow-y: hidden;
        }

        #dice {
            position: absolute;
            top: 0;
            left: 30px;
            width: 100px;
            height: 100px;
            margin-top: 15px;

            -webkit-transform-style: preserve-3d;
            -moz-transform-style: preserve-3d;
            -ms-transform-style: preserve-3d;
            -o-transform-style: preserve-3d;
            transform-style: preserve-3d;
        }

        #dice .face {
            background: url(<?php echo $die_file; ?>) no-repeat;
            background-size: 300px 200px;
            border-radius: 15px;
            border: 3px solid #00aabe;
            color: white;
            font-size: 20px;
            position: absolute;
            width: 100px;
            height: 100px;
            display: block;
        }

        #dice .front {
            -webkit-transform: perspective(000px) rotateX(0) translateZ(50px);
            -moz-transform: perspective(000px) rotateX(0) translateZ(50px);
            -ms-transform: perspective(000px) rotateX(0) translateZ(50px);
            -o-transform: perspective(000px) rotateX(0) translateZ(50px);
            transform: perspective(000px) rotateX(0) translateZ(50px);
        }

        #dice .back {
            background-position: -100px 0;
            -webkit-transform: perspective(000px) rotateY(180deg) translateZ(50px);
            -moz-transform: perspective(000px) rotateY(180deg) translateZ(50px);
            -ms-transform: perspective(000px) rotateY(180deg) translateZ(50px);
            -o-transform: perspective(000px) rotateY(180deg) translateZ(50px);
            transform: perspective(000px) rotateY(180deg) translateZ(50px);
        }

        #dice .left {
            background-position: -200px 0;
            -webkit-transform: perspective(000px) rotateY(90deg) translateZ(50px);
            -moz-transform: perspective(000px) rotateY(90deg) translateZ(50px);
            -ms-transform: perspective(000px) rotateY(90deg) translateZ(50px);
            -o-transform: perspective(000px) rotateY(90deg) translateZ(50px);
            transform: perspective(000px) rotateY(90deg) translateZ(50px);
        }

        #dice .bottom {
            background-position: 0 -100px;
            -webkit-transform: perspective(000px) rotateX(-90deg) translateZ(50px);
            -moz-transform: perspective(000px) rotateX(-90deg) translateZ(50px);
            -ms-transform: perspective(000px) rotateX(-90deg) translateZ(50px);
            -o-transform: perspective(000px) rotateX(-90deg) translateZ(50px);
            transform: perspective(000px) rotateX(-90deg) translateZ(50px);
        }

        #dice .top {
            background-position: -100px -100px;
            -webkit-transform: perspective(000px) rotateX(90deg) translateZ(50px);
            -moz-transform: perspective(000px) rotateX(90deg) translateZ(50px);
            -ms-transform: perspective(000px) rotateX(90deg) translateZ(50px);
            -o-transform: perspective(000px) rotateX(90deg) translateZ(50px);
            transform: perspective(000px) rotateX(90deg) translateZ(50px);
        }

        #dice .right {
            background-position: -200px -100px;
            -webkit-transform: perspective(000px) rotateY(-90deg) translateZ(50px);
            -moz-transform: perspective(000px) rotateY(-90deg) translateZ(50px);
            -ms-transform: perspective(000px) rotateY(-90deg) translateZ(50px);
            -o-transform: perspective(000px) rotateY(-90deg) translateZ(50px);
            transform: perspective(000px) rotateY(-90deg) translateZ(50px);
        }

        .rollBox {
            height: 150px;
            width: 150px;
        }

    </style>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.transit/0.9.12/jquery.transit.js"></script>    <script type="text/javascript">
        let debug = false;
        const element = document.getElementById("rollContainer");
        const dice = {
            position0: {
                y: 20,
                rotateX: 0,
                rotateY: 0
            },
            position1: {
                y: 20,
                rotateX: 1080,
                rotateY: -180
            },
            position2: {
                y: 20,
                rotateX: -1080,
                rotateY: -180
            },
            position3: {
                y: 20,
                rotateX: -1080,
                rotateY: -90
            },
            position4: {
                y: 20,
                rotateX: -990,
                rotateY: -360
            },
            position5: {
                y: 20,
                rotateX: -810,
                rotateY: -360
            },
            position6: {
                y: 20,
                rotateX: -1080,
                rotateY: -270
            },
        };

        function dieRoll() {
            if (typeof dieRoll.recentRolls === 'undefined') {dieRoll.recentRolls = [];}
            let number = Math.floor((Math.random() * 6) + 1);
            while (dieRoll.recentRolls.includes(number)) {number = Math.floor((Math.random() * 6) + 1);}
            dieRoll.recentRolls.push(number);
            if (dieRoll.recentRolls.length > 5) {dieRoll.recentRolls.shift();}
            document.getElementById("diceValue").innerHTML = number + "";
            let theDie = $("#dice");
            theDie.transition(dice.position0, 0);
            theDie.transition(dice['position' + number], 1300, 'linear');
        }
    </script>

</head>

<body>
<div id="rollContainer"
     class="rollBox"
     onclick="dieRoll()">
    <div id="dice" >
        <div class="face front"></div>
        <div class="face back"></div>
        <div class="face left"></div>
        <div class="face bottom"></div>
        <div class="face top"></div>
        <div class="face right"></div>
    </div>
    <p style="display:none" id="diceValue">0</p>
    <!-- <button id="play" class="button">Roll</button> -->
</div>



</body>
</html>
