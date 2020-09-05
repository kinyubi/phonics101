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
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=1">
    <title>Tic Tac Toe</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Rock Salt">
    <link rel="stylesheet" href="/css/bootstrap-4.4.1/bootstrap.min.css">
    <script src="/js/bootstrap-4.4.1/bootstrap.min.js"></script>
    <link rel="stylesheet" href="tictac.css">
    <script src="tictac.js"></script>
</head>

<body>
<div class="row headrow">
    <h1>Tic-Tac-Toe Fun</h1>
</div>
<div class="row d-flex flex-nowrap" style="background-color:white; ">
    <div id="rats" style="display: flex; flex-direction: column; margin:10px; padding:10px">
        <img id="rat1" class="gamepiece" src="<?php echo $rat; ?>" draggable="true" ondragstart="drag()" alt="P1">
        <img id="rat2" class="gamepiece" src="<?php echo $rat; ?>" draggable="true" ondragstart="drag()"  alt="P1">
        <img id="rat3" class="gamepiece" src="<?php echo $rat; ?>" draggable="true" ondragstart="drag()"  alt="P1">
        <img id="rat4" class="gamepiece" src="<?php echo $rat; ?>" draggable="true" ondragstart="drag()"  alt="P1">
        <img id="rat5" class="gamepiece" src="<?php echo $rat; ?>" draggable="true" ondragstart="drag()"  alt="P1">
    </div>
    <div id="tic-tac-toe">
        <div id="container"
        ">

        <div id="theboard">
            <table>
                <tr>
                    <td id="1" class="square rbdr bbdr" ondrop="drop(e)" ondragover="allowDrop()">
                        <?php echo $word_list[0]; ?>
                    </td>
                    <td id="2" class="square rbdr bbdr" ondrop="drop(e)" ondragover="allowDrop()">
                        <?php echo $word_list[1]; ?>
                    </td>
                    <td id="3"
                        class="square bbdr" ondrop="drop()" ondragover="allowDrop()">
                        <?php echo $word_list[2]; ?>
                    </td>
                </tr>
                <tr>
                    <td id="4" class="square rbdr bbdr" ondrop="drop(e)" ondragover="allowDrop()">
                        <?php echo $word_list[3]; ?>
                    </td>
                    <td id="5" class="square rbdr bbdr" ondrop="drop(e)" ondragover="allowDrop()">
                        <?php echo $word_list[4]; ?>
                    </td>
                    <td id="6" class="square bbdr" ondrop="drop(e)" ondragover="allowDrop()">
                        <?php echo $word_list[5]; ?>
                    </td>
                </tr>
                <tr>
                    <td id="7" class="square rbdr" ondrop="drop(e)" ondragover="allowDrop()">
                        <?php echo $word_list[6]; ?>
                    </td>
                    <td id="8" class="square rbdr" ondrop="drop(e)" ondragover="allowDrop()">
                        <?php echo $word_list[7]; ?>
                    </td>
                    <td id="9" class="square" ondrop="drop(e)" ondragover="allowDrop()">
                        <?php echo $word_list[8]; ?>
                    </td>
                </tr>
            </table>
        </div>

        <div id="buttons">
            <button class="tictac" onClick="window.location.reload();">New Game</button>
        </div>
    </div>
</div>
<div id="cats" style="display: flex; flex-direction: column; margin:10px; padding:10px">
    <img id="cat1" class="gamepiece" src="<?php echo $cat; ?>" draggable="true" ondragstart="drag()"  alt="P2">
    <img id="cat2" class="gamepiece" src="<?php echo $cat; ?>" draggable="true" ondragstart="drag()"  alt="P2">
    <img id="cat3" class="gamepiece" src="<?php echo $cat; ?>" draggable="true" ondragstart="drag()"  alt="P2">
    <img id="cat4" class="gamepiece" src="<?php echo $cat; ?>" draggable="true" ondragstart="drag()"  alt="P2">
    <img id="cat5" class="gamepiece" src="<?php echo $cat; ?>" draggable="true" ondragstart="drag()"  alt="P2">
</div>

</body>

</html>
