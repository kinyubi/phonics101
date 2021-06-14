<?php
require dirname(__DIR__) . '/autoload.php';

use App\ReadXYZ\Models\Session;
use App\ReadXYZ\JSON\ZooAnimalsAlt;
Session::sessionContinue();

$cssJsVer = '?ver=1.0407.0';
$bootstrapVer = '?ver=1.01.17.0';

$animals = ZooAnimalsAlt::getInstance()->get2AnimalObjects();

$player1 = $animals[0];
$player2 = $animals[1];

$rat = $player1->fileName;
$cat = $player2->fileName;
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
  <script src="/js/tictactoe-revised.js"></script>
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
          width: 60px;
          height: 60px;
      }

      h1 {
          font-family: "Rock Salt", Times, serif;
          font-size: 18px;
          color: var(--readxyz);
          padding: 4px;
          margin-bottom: 0;
      }

      h1, td {
          text-align: center;
          position: relative;
      }

      button.ticTacToe {
          background-color: var(--readxyz);
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
          width: 60px;
          height: 60px;
          margin: 0;
          z-index: 100;
      }
      .gamePiece__wrapper {
          width: 65px;
          height: 65px;
          margin-top: 5px;
      }

      .gamePiece__wrapper_right {
          text-align: right;
      }

      .row__ticTacToe:after {
          content: "";
          display: table;
          clear: both;
      }

      .square {
          width: 85px;
          height: 85px;
          padding: 3px;
          border-collapse: collapse;
          font-family: serif;
          font-size: 24px;
          color: black;
          -webkit-touch-callout: none; /* iOS Safari */
            -webkit-user-select: none; /* Safari */
             -khtml-user-select: none; /* Konqueror HTML */
               -moz-user-select: none; /* Old versions of Firefox */
                -ms-user-select: none; /* Internet Explorer/Edge */
                    user-select: none; /* Non-prefixed version, currently
                                          supported by Chrome, Edge, Opera and Firefox */
      }

      .border__right {
          border-right: 5px solid var(--readxyz);
      }

      .border__bottom {
          border-bottom: 5px solid var(--readxyz);
      }

      #new-game {
          text-align: center;
          padding-top: 15px;
      }

      .mover-disabled {
        -khtml-user-select: none;
        -o-user-select: none;
        -moz-user-select: none;
        -webkit-user-select: none;
        user-select: none;
      }

      .table-style {
          table-layout: fixed;
          width: 100%;
      }

      .table-td {
          width: 33%;
      }
  </style>
</head>

<body style="background-color: white;">
<div class="p-0 m-0">
  <div class="row row__ticTacToe row__header m-3 p-0 justify-content-center">
    <h1>Tic-Tac-Toe Fun</h1>
  </div>

  <div class="row__ticTacToe d-flex flex-nowrap justify-content-center" style="background-color:white; ">
    <div id="rats" class="d-flex flex-column">
      <div class="gamePiece__wrapper"><img id="rat1" class="gamePiece mover" src="<?php echo $rat; ?>" alt="P1"></div>
      <div class="gamePiece__wrapper"><img id="rat2" class="gamePiece mover" src="<?php echo $rat; ?>" alt="P1"></div>
      <div class="gamePiece__wrapper"><img id="rat3" class="gamePiece mover" src="<?php echo $rat; ?>" alt="P1"></div>
      <div class="gamePiece__wrapper"><img id="rat4" class="gamePiece mover" src="<?php echo $rat; ?>" alt="P1"></div>
      <div class="gamePiece__wrapper"><img id="rat5" class="gamePiece mover" src="<?php echo $rat; ?>" alt="P1"></div>
    </div>
    <div id="ticTacToe">
      <div id="container">

        <div id="theboard">
          <table class="table-style">
            <tr>
              <td id="s-1" class="square receptacle border__right border__bottom table-td">-</td>
              <td id="s-2" class=" square receptacle border__right border__bottom table-td">-</td>
              <td id="s-3" class=" square receptacle border__bottom table-td">-</td>
            </tr>
            <tr>
              <td id="s-4" class=" square receptacle border__right border__bottom table-td">-</td>
              <td id="s-5" class=" square receptacle border__right border__bottom table-td">-</td>
              <td id="s-6" class=" square receptacle border__bottom table-td">-</td>
            </tr>
            <tr>
              <td id="s-7" class=" square receptacle border__right table-td">-</td>
              <td id="s-8" class=" square receptacle border__right table-td">-</td>
              <td id="s-9" class=" square receptacle table-td">-</td>
            </tr>
          </table>
        </div>

        <div id="buttons" class="d-flex justify-content-center w-100 h-100 mt-5">
            <button class="ticTacToe mx-auto" onClick="window.location.reload();">New Game</button>
        </div>
      </div>
    </div>
    <div id="cats" class="d-flex flex-column">
      <div class="gamePiece__wrapper gamePiece__wrapper_right"><img id="cat1" class="gamePiece mover" src="<?php echo $cat; ?>" alt="P2"></div>
      <div class="gamePiece__wrapper gamePiece__wrapper_right"><img id="cat2" class="gamePiece mover" src="<?php echo $cat; ?>" alt="P2"></div>
      <div class="gamePiece__wrapper gamePiece__wrapper_right"><img id="cat3" class="gamePiece mover" src="<?php echo $cat; ?>" alt="P2"></div>
      <div class="gamePiece__wrapper gamePiece__wrapper_right"><img id="cat4" class="gamePiece mover" src="<?php echo $cat; ?>" alt="P2"></div>
      <div class="gamePiece__wrapper gamePiece__wrapper_right"><img id="cat5" class="gamePiece mover" src="<?php echo $cat; ?>" alt="P2"></div>
    </div>
  </div>
</div>
</body>

</html>
