<?php
require dirname(__DIR__) . '/autoload.php';
use App\ReadXYZ\Helpers\Location;

$cssJsVer = '?ver=1.0101.0';
$bootstrapVer = '?ver=0.1016.0';

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
    'horse', 'wolf', 'salmon', 'jellyfish', 'penguin', 'cow', 'whale', 'giraffe', 'raccoon', 'goat',
    'rhino', 'otter', 'pig', 'hamster', 'hedgehog', 'pigeon', 'sheep', 'koala', 'fox', 'platypus',
    'hippo', 'gorilla', 'owl', 'chimpanzee', 'rat', 'lemur', 'toucan', 'beaver', 'frog', 'butterfly',
    'parrot', 'redpanda', 'squirrel', 'zebra', 'rabbit', 'camel', 'flamingo', 'polarbear', 'seahorse', 'sloth',
    'skunk', 'starfish', 'swan', 'sugarglider', 'snail', 'duck', 'pufferfish', 'shark', 'eagle', 'crab',
    'tortoise', 'ladybug', 'turkey', 'snake', 'cougar', 'chicken', 'crocodile', 'ostrich', 'peacock', 'panther',
    'seal', 'porcupine', 'anteater', 'bee', 'hummingbird', 'mouse', 'octopus', 'kangaroo', 'bison', 'kiwi',
    'guineapig', 'llama', 'cheetah', 'turtle', 'walrus', 'yak', 'arcticfox', 'orca', 'deer', 'shrimp',
    'jaguar', 'emu', 'toad', 'stingray', 'beetle', 'lobster', 'scorpion', 'reindeer', 'spider', 'mantis'
];

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

$rat = Location::getTicTacToeAnimal($player1);
$cat = location::getTicTacToeAnimal($player2);
?>

<!DOCTYPE html>
<html lang="en-US">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=1">
  <title>Tic Tac Toe</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Rock Salt">
  <link rel="stylesheet" href="/css/bootstrap4-custom/bootstrap.min.css<?php echo $bootstrapVer; ?>">
  <link rel="stylesheet" href="/css/colorbox/colorbox.css">
  <link rel="stylesheet" href="/css/phonics.css<?php echo $cssJsVer; ?>">

  <!--  <script src="/js/jquery-3.5.1.js"></script>-->
  <script src="/js/unused/jquery-1.12.4.js"></script>

  <script src="/js/jquery.colorbox.js"></script>
  <script src="/js/bootstrap4-custom/bootstrap.min.js<?php echo $bootstrapVer; ?>"></script>
  <script src="/js/drag-and-touch.js"></script>

  <style>
      /*body {*/
      /*    width: 440px;*/
      /*    height: 400px;*/
      /*    overflow: hidden;*/
      /*}*/

      table, td, tr {
          border-spacing: 0;
      }

      table {
          margin: 0;
      }

      td {
          margin: 2px;
          width: 70px;
          height: 70px;
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

      button.ticTacToe {
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

      .gamePiece {
          width: 45px;
          height: 45px;
          margin: 0;
          z-index: 100;
      }
      .gamePiece__wrapper {
          width: 50px;
          height: 50px;
          margin: 2px;
      }

      .row__ticTacToe:after {
          content: "";
          display: table;
          clear: both;
      }

      .square {
          width: 70px;
          height: 70px;
          padding: 3px;
          border-collapse: collapse;
          font-family: serif;
          font-size: 40px;
          color: black;
      }

      .border__right {
          border-right: 5px solid #00aabe;
      }

      .border__bottom {
          border-bottom: 5px solid #00aabe;
      }

      #buttons {
          text-align: center;
          padding-top: 15px;
      }
  </style>

  <script>
      let rats = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
      let cats = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
      const winners = [[1, 2, 3], [4, 5, 6], [7, 8, 9], [1, 4, 7], [2, 5, 8], [3, 6, 9], [1, 5, 9], [3, 5, 7]];
      let moves = 0;
      let status = "UNFINISHED";

      function winner() {
          $.colorbox({
              closeButton: true, close: "X", opacity: 1, maxWidth: "200px", left: "18%", top: "25%",
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

      function checkStatus(moverId, receiverId) {
          let idx = parseInt(receiverId.substring(1));
          moves++;
          if (moverId.charAt(0) === "r") {
              rats[idx] = 1;
          } else {
              cats[idx] = 1;
          }
          setGameStatus();
          if (status.startsWith("WIN")) {
              winner();
          } else if (status === "DRAW") {
              noWinner();
          }
      }

      function dropOnSquare(event) {
          event.preventDefault();
          let moverId = event.dataTransfer.getData("text");
          let receiverId = event.target.id;
          checkStatus(moverId, receiverId);
      }

      function touchSquare(event) {
          const receptacles = document.getElementsByClassName('receptacle');
          let mover = event.target;
          let receiver = null;
          for (let r of receptacles) {
              if (detectContainment(mover, r)) {
                  receiver = r;
                  break;
              }
          }
          if (receiver) {
              checkStatus(mover.id, receiver.id);
              mover.removeEventListener('touchend', touchSquare, {passive: true});
          }
      }

      document.addEventListener("DOMContentLoaded", function (event) {
          const receptacles = document.getElementsByClassName('receptacle');
          for (let receiver of receptacles) {
              receiver.addEventListener('drop', dropOnSquare, false);
          }
          const movers = document.getElementsByClassName('mover');
          for (let mover of movers) {
              mover.addEventListener('touchend', touchSquare, {passive: true});
          }
      });

      // window.onload = function () {
      //     const receptacles = document.getElementsByClassName('receptacle');
      //     for (let receiver of receptacles) {
      //         receiver.addEventListener('drop', dropOnSquare, false);
      //     }
      //     const movers = document.getElementsByClassName('mover');
      //     for (let mover of movers) {
      //         mover.addEventListener('touchend', touchSquare, {passive: true});
      //     }
      // }

  </script>
</head>

<body style=" background-color: white;">
<div class="container p-0 m-0">
  <div class="row row__ticTacToe row__header m-3 p-0">
    <h1 style="text-align: center; width:100%">Tic-Tac-Toe Fun</h1>
  </div>

  <div class="row__ticTacToe d-flex flex-nowrap" style="background-color:white; ">
    <div id="rats" class="d-flex flex-column m-1 p-1">
      <div class="gamePiece__wrapper"><img id="rat1" class="gamePiece mover" src="<?php echo $rat; ?>" alt="P1"></div>
      <div class="gamePiece__wrapper"><img id="rat2" class="gamePiece mover" src="<?php echo $rat; ?>" alt="P1"></div>
      <div class="gamePiece__wrapper"><img id="rat3" class="gamePiece mover" src="<?php echo $rat; ?>" alt="P1"></div>
      <div class="gamePiece__wrapper"><img id="rat4" class="gamePiece mover" src="<?php echo $rat; ?>" alt="P1"></div>
      <div class="gamePiece__wrapper"><img id="rat5" class="gamePiece mover" src="<?php echo $rat; ?>" alt="P1"></div>
    </div>
    <div id="tic-tac-toe">
      <div id="container">

        <div id="theboard">
          <table>
            <tr>
              <td id="s1" class="square receptacle border__right border__bottom"><?php echo $word_list[0]; ?></td>
              <td id="s2" class=" square receptacle border__right border__bottom"><?php echo $word_list[1]; ?></td>
              <td id="s3" class=" square receptacle border__bottom"><?php echo $word_list[2]; ?></td>
            </tr>
            <tr>
              <td id="s4" class=" square receptacle border__right border__bottom"><?php echo $word_list[3]; ?></td>
              <td id="s5" class=" square receptacle border__right border__bottom"><?php echo $word_list[4]; ?></td>
              <td id="s6" class=" square receptacle border__bottom"><?php echo $word_list[5]; ?></td>
            </tr>
            <tr>
              <td id="s7" class=" square receptacle border__right"><?php echo $word_list[6]; ?></td>
              <td id="s8" class=" square receptacle border__right"><?php echo $word_list[7]; ?></td>
              <td id="s9" class=" square receptacle"><?php echo $word_list[8]; ?></td>
            </tr>
          </table>
        </div>

        <div id="buttons">
          <button class="ticTacToe" onClick="window.location.reload();">New Game</button>
        </div>
      </div>
    </div>
    <div id="cats" class="d-flex flex-column m-1 p-1">
      <div class="gamePiece__wrapper"><img id="cat1" class="gamePiece mover" src="<?php echo $cat; ?>" alt="P2"></div>
      <div class="gamePiece__wrapper"><img id="cat2" class="gamePiece mover" src="<?php echo $cat; ?>" alt="P2"></div>
      <div class="gamePiece__wrapper"><img id="cat3" class="gamePiece mover" src="<?php echo $cat; ?>" alt="P2"></div>
      <div class="gamePiece__wrapper"><img id="cat4" class="gamePiece mover" src="<?php echo $cat; ?>" alt="P2"></div>
      <div class="gamePiece__wrapper"><img id="cat5" class="gamePiece mover" src="<?php echo $cat; ?>" alt="P2"></div>
    </div>
  </div>
</div>
</body>

</html>
