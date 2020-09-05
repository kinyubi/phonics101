<?php

$words = ['fat,cat,hat,sat,mat,pat,bat,rat,vat',
    'cap,gap,lap,map,rap,sap,tap,zap,nap',
    'bag,hag,jag,lag,nag,rag,sag,tag,wag',
    'ban,can,fan,lan,man,pan,ran,tan,van',
    'pap,dad,bab,dab,bad,pad,bad,pad,dad',
    'bit,fit,hit,kit,mitt,pit,sit,wit,zit',
    'big,dig,fig,jig,pig,rig,wig,zig,gig',
    'dip,hip,jip,lip,nip,pip,rip,sip,zip',
    'cot,dot,got,hot,jot,lot,not,pot,rot',
    'bog,cog,dog,fog,hog,jog,log,hop,top',
    'fob,fop,gob,God,hop,job,lob,mob,mod',
    'mop,nod,pod,pop,rob,rod,sob,sod,top',
    'but,cut,gut,hut,jut,mutt,nut,putt,rut',
    'bug,dug,hug,lug,jug,mug,pug,rug,tug',
    'bud,dub,dud,pub,pug,pup,dub,bud,dud',
    'bet,get,jet,let,met,net,pet,set,wet'];

$animals = [
    'elephant', 'monkey', 'tiger', 'panda', 'lion', 'bear', 'dog', 'cat', 'leopard', 'dolphin',
    'horse', 'wolf', 'salmon', 'jellyfish', 'penguin', 'cow', 'whate', 'giraffe', 'raccoon', 'goat',
    'rhino', 'otter', 'pig', 'hamster', 'hedgehog', 'pigeon', 'sheep', 'koala', 'fox', 'platypus',
    'hippo', 'gorilla', 'owl', 'chimpanzee', 'rat', 'lemur', 'toucan', 'beaver', 'frog', 'butterfly',
    'parrot', 'redpanda', 'squirrel', 'zebra', 'rabbit', 'camel', 'flamingo', 'polarbear', 'seahorse', 'sloth',
    'skunk', 'starfish', 'swan', 'sugarglider', 'snail', 'duck', 'pufferfish', 'shark', 'eagle', 'crab',
    'tortoise', 'ladybug', 'turkey', 'snake', 'cougar', 'chicken', 'crocodile', 'ostrich', 'peacock', 'panther',
    'seal', 'porcupine', 'anteater', 'bee', 'hummingbird', 'mouse', 'octopus', 'kangaroo', 'bison', 'kiwi',
    'guineapig', 'llama', 'cheetah', 'turtle', 'walrus', 'yak', 'arcticfox', 'orca', 'deer', 'shrimp',
    'jaguar', 'emu', 'toad', 'stingray', 'beetle', 'lobster', 'scorpion', 'reindeer', 'spider', 'mantis'
];

function isWordPress()
{
    return defined('ABSPATH');
}

function phonicsRoot()
{
    return $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'phonics' . DIRECTORY_SEPARATOR . '101' . DIRECTORY_SEPARATOR;
}

function phonicsUrl($filename = '')
{
    $ssl = (isset($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS']) ? 'https://' : 'http://';
    $phonics = "$ssl{$_SERVER['SERVER_NAME']}:{$_SERVER['SERVER_PORT']}/phonics/101";
    if ('' == $filename) {
        return $phonics;
    } else {
        return "$phonics/$filename";
    }
}

function jsdir(string $filename = '')
{
    return phonicsUrl("js/$filename");
}

function cssdir(string $filename = '')
{
    return phonicsUrl("css/$filename");
}

function imagesdir(string $filename = '')
{
    return phonicsUrl("images/$filename");
}

function get_local_script($filename)
{
    $full_url = jsdir($filename);

    return "<script type=\"text/javascript\" src=\"$full_url\"></script>";
}

function get_local_css($filename)
{
    $full_url = cssdir($filename);

    return "<link rel=\"stylesheet\" href=\"$full_url\" media=\"all\" />";
}

if (isset($_GET['P1']) && in_array($_GET['P1'], $animals)) {
    $player1 = $_GET['P1'];
} else {
    $player1 = $animals[rand(0, 99)];
}
if (isset($_GET['P2']) && in_array($_GET['P2'], $animals)) {
    $player2 = $_GET['P2'];
} else {
    $player2 = $animals[rand(0, 99)];
}
while ($player2 == $player1) {
    $player2 = $animals[rand(0, 99)];
}

$list_count = count($words);
$idx = rand(0, $list_count - 1);
$word_list = explode(',', $words[$idx]);

if (isset($_GET['wordlist'])) {
    $raw_list = explode('_', $_GET['wordlist']);
    $count = count($raw_list);
    $word_list = $raw_list;
    for ($i = $count; $i < 9; ++$i) {
        $word_list[] = $raw_list[$i % $count];
    }
}

$rat = "images/$player1.jpg";
$cat = "images/$player2.jpg";
?>

<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tic Tac Toe</title>
    <!-- Adding rock salt font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Rock Salt">

    <style>
        table, td, tr {
            border-spacing: 0;
        }

        body {
            overflow-x: hidden;
            overflow-y: hidden;
            background-color: white;
        }

        table {
            margin: 0;
        }

        td {
            margin: 5px;
        }

        h1 {
            font-family: "Rock Salt", Times, serif;
            font-size: 26px;
            color: #00aabe;
            padding: 5px;
            margin-bottom: 0;
        }

        h1, td {
            text-align: center;
            position: relative;
        }

        button.tictac {
            background-color: #00aabe;
            border: none;
            color: white;
            padding: 5px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 10px;
            margin: 15px 15px 0 0;
            cursor: pointer;
            border-radius: 8px;
            width: 65px;
        }

        .board {
            position: absolute;
            background-color: white;
            margin: 15px;
        }

        .gamePiece {
            width: 50px;
            height: 50px;
            margin: 10px;
        }

        .row:after {
            content: "";
            display: table;
            clear: both;
        }

        .right-bdr {
            border-right: 5px solid #00aabe;
        }

        .bottom-bdr {
            border-bottom: 5px solid #00aabe;
        }

        .headRow {
            width: 500px;
            text-align: center;
        }

        #buttons {
            text-align: center;
            padding-top: 15px;
        }

        /* #cboxClose {width: 25px;height: 25px;color: black;border-radius: 50%;} */

        .square {
            width: 100px;
            height: 100px;
            padding: 3px;
            border-collapse: collapse;
            font-family: serif;
            font-size: 40px;
            color: black;
        }
    </style>
    <script>
        /**
         * javascript for tictac.php
         */
        let rats = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        let cats = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        const winners = [
            [1, 2, 3],
            [4, 5, 6],
            [7, 8, 9],
            [1, 4, 7],
            [2, 5, 8],
            [3, 6, 9],
            [1, 5, 9],
            [3, 5, 7]
        ];
        let moves = 0;
        let status = "UNFINISHED";


        /**
         *
         * @param {string} who
         */
        function winner(who) {

            $.colorbox({
                closeButton: true,
                close: "X",
                opacity: 1,
                maxWidth: "200px",
                left: "18%",
                top: "25%",
                href: "https://movellas-users.s3.amazonaws.com/comment/201210171910330233/201307010339396490.gif"
            });
        }

        function noWinner() {
            $.colorbox({html: "The game was a tie. Try again!"});
        }

        function setGameStatus() {
            let combo;
            for (combo of winners) {
                if (cats[combo[0]] && cats[combo[1]] && cats[combo[2]]) {
                    status = "WINCAT";
                    return;
                }
            }
            for (combo of winners) {
                if (rats[combo[0]] && rats[combo[1]] && rats[combo[2]]) {
                    status = "WINRAT";
                    return;
                }
            }
            if (moves === 9) {
                status = "DRAW";
                return;
            }
            status = "UNFINISHED";
        }


        /**
         * Used as target for an ondrop event
         * @param {Object} ev   The event that triggered this callback.
         */
        function drop(ev) {
            ev.preventDefault();
            const data = ev.dataTransfer.getData("text");
            document.getElementById(data).setAttribute("draggable", "false");
            ev.target.innerText = "";
            ev.target.appendChild(document.getElementById(data));
            ev.target.setAttribute("ondrop", "");
            ev.target.setAttribute("ondragover", "");
            moves++;
            if (data.charAt(0) === "r") {
                rats[parseInt(ev.target.id)] = 1;
            } else {
                cats[parseInt(ev.target.id)] = 1;
            }
            setGameStatus();
            if (status.startsWith("WIN")) {
                winner(status.substring(3, 3));
            } else if (status === "DRAW") {
                noWinner();
            }
        }

        /**
         * Target for an ondragstart event
         * called when target of ondragstart event even is this function
         * @param  ev
         */
        function drag(ev) {
            ev.dataTransfer.setData("text", ev.target.id);
            ev.dataTransfer.dropEffect = "move";
        }

        /**
         * Target for an ondragover event
         * called when target of ondrop even is this function
         * @param ev the event that occurred
         */
        function allowDrop(ev) {
            ev.preventDefault();
        }

        function resizeIframe(obj) {
            obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
        }
    </script>
</head>

<body>
<div class="row headRow">
    <h1>Tic-Tac-Toe Fun</h1>
</div>
<div style="display: flex; flex-wrap: nowrap; background-color:white; ">
    <div id="rats" style="display: flex; flex-direction: column; margin:10px; padding:10px">
        <img id="rat1" class="gamePiece" src="<?php echo $rat; ?>" draggable="true" ondragstart="drag(ev)" alt="P1">
        <img id="rat2" class="gamePiece" src="<?php echo $rat; ?>" draggable="true" ondragstart="drag(ev)" alt="P1">
        <img id="rat3" class="gamePiece" src="<?php echo $rat; ?>" draggable="true" ondragstart="drag(ev)" alt="P1">
        <img id="rat4" class="gamePiece" src="<?php echo $rat; ?>" draggable="true" ondragstart="drag(ev)" alt="P1">
        <img id="rat5" class="gamePiece" src="<?php echo $rat; ?>" draggable="true" ondragstart="drag(ev)" alt="P1">
    </div>
    <div id="tic-tac-toe" >
        <div id="container">

            <div id="theBoard">
                <table>
                    <tr>
                        <td id="1" class="square right-bdr bottom-bdr" ondrop="drop(ev)" ondragover="allowDrop(ev)">
                            <?php echo $word_list[0]; ?>
                        </td>
                        <td id="2" class="square right-bdr bottom-bdr" ondrop="drop(ev)" ondragover="allowDrop(ev)">
                            <?php echo $word_list[1]; ?>
                        </td>
                        <td id="3"
                            class="square bottom-bdr" ondrop="drop(ev)" ondragover="allowDrop(ev)">
                            <?php echo $word_list[2]; ?>
                        </td>
                    </tr>
                    <tr>
                        <td id="4" class="square right-bdr bottom-bdr" ondrop="drop(ev)" ondragover="allowDrop(ev)">
                            <?php echo $word_list[3]; ?>
                        </td>
                        <td id="5" class="square right-bdr bottom-bdr" ondrop="drop(ev)" ondragover="allowDrop(ev)">
                            <?php echo $word_list[4]; ?>
                        </td>
                        <td id="6" class="square bottom-bdr" ondrop="drop(ev)" ondragover="allowDrop(ev)">
                            <?php echo $word_list[5]; ?>
                        </td>
                    </tr>
                    <tr>
                        <td id="7" class="square right-bdr" ondrop="drop(ev)" ondragover="allowDrop(ev)">
                            <?php echo $word_list[6]; ?>
                        </td>
                        <td id="8" class="square right-bdr" ondrop="drop(ev)" ondragover="allowDrop(ev)">
                            <?php echo $word_list[7]; ?>
                        </td>
                        <td id="9" class="square" ondrop="drop(ev)" ondragover="allowDrop(ev)">
                            <?php echo $word_list[8]; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <div id="buttons">
                <button class="tictac" onClick="window.location.reload();">New Game</button>
                <button class="tictac" onClick="window.close()">Exit</button>
            </div>
        </div>
    </div>
    <div id="cats"  style="display: flex; flex-direction: column; margin:10px; padding:10px">
        <img id="cat1" class="gamePiece" src="<?php echo $cat; ?>" draggable="true" ondragstart="drag(ev)" alt="P2">
        <img id="cat2" class="gamePiece" src="<?php echo $cat; ?>" draggable="true" ondragstart="drag(ev)" alt="P2">
        <img id="cat3" class="gamePiece" src="<?php echo $cat; ?>" draggable="true" ondragstart="drag(ev)" alt="P2">
        <img id="cat4" class="gamePiece" src="<?php echo $cat; ?>" draggable="true" ondragstart="drag(ev)" alt="P2">
        <img id="cat5" class="gamePiece" src="<?php echo $cat; ?>" draggable="true" ondragstart="drag(ev)" alt="P2">
    </div>

</div>
</body>

</html>
