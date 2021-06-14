let rats = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
let cats = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
const winners = [[1, 2, 3], [4, 5, 6], [7, 8, 9], [1, 4, 7], [2, 5, 8], [3, 6, 9], [1, 5, 9], [3, 5, 7]];
let moves = 0;
let status = "UNFINISHED";
let wordList = [];
let previousPositionData = [
    {
        moverId: '',
        receiverId: ''
    },
    {
        moverId: '',
        receiverId: ''
    },
    {
        moverId: '',
        receiverId: ''
    },
    {
        moverId: '',
        receiverId: ''
    },
    {
        moverId: '',
        receiverId: ''
    },
    {
        moverId: '',
        receiverId: ''
    },
    {
        moverId: '',
        receiverId: ''
    },
    {
        moverId: '',
        receiverId: ''
    },
    {
        moverId: '',
        receiverId: ''
    },
    {
        moverId: '',
        receiverId: ''
    }
]

$.ajax({url: "tictac-wordlist.php", success: function(result){
    wordList = JSON.parse(result);
    populateTicTacToeWithWords(wordList);
}});

function populateTicTacToeWithWords(wordList) {
    wordList.forEach((word, index) => {
        let square = document.getElementById(`s-${index+1}`);
        square.innerHTML = word;
    });
}

function disableGame() {
    const movers = document.getElementsByClassName('mover');
    for (let mover of movers) {
        mover.setAttribute('draggable', false);
        mover.classList.add('mover-disabled');
        mover.removeEventListener('touchstart', handleTouchStart, {passive: true});
        mover.removeEventListener('touchmove', handleTouchMove, {passive: true});
        mover.removeEventListener('touchend', handleTouchEnd, {passive: true});
    }
}

function winner() {
    disableGame();
    $.colorbox({
        closeButton: true, close: "X", opacity: 1, maxWidth: "200px", left: "18%", top: "25%",
        href: "https://movellas-users.s3.amazonaws.com/comment/201210171910330233/201307010339396490.gif"
    });
}

function noWinner() {
    disableGame();
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

    let ratsOnBoard = rats.filter((rat) => rat == 1);
    let catsOnBoard = cats.filter((cat) => cat == 1);

    if(ratsOnBoard.length + catsOnBoard.length >= 9){
        status = "DRAW";
        return;
    }

    status = "UNFINISHED";
}


function checkStatus(moverId, receiverId) {
  let startPos = receiverId.indexOf('-') + 1;
  if (startPos === 0) return;

  let idx = parseInt(receiverId.substring(startPos));

  let previousPositionDatum = previousPositionData.find((previousPositionItem) => {
      return previousPositionItem.moverId == moverId;
  });

  if(previousPositionDatum != null){
      if(previousPositionDatum.receiverId != receiverId){
          previousReceiverIdIndex = previousPositionDatum.receiverId.split('-')[1];
          if(previousPositionDatum.moverId.charAt(0) === "r"){
              rats[previousReceiverIdIndex] = 0;
          }
          else {
              cats[previousReceiverIdIndex] = 0;
          }
      }
  }

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

function handleDropOnSquare(moverId, receiverId){
    receiverIdIndex = receiverId.split('-')[1];

    willHandleDrop = false;
    isWithinBoard = false;

    if(rats[receiverIdIndex] != 1 && cats[receiverIdIndex] != 1){
        willHandleDrop = true;

        let previousPositionDatum = previousPositionData.find((previousPositionItem) => {
            return previousPositionItem.moverId == moverId;
        });

        if(previousPositionDatum != null){
            if(previousPositionDatum.receiverId != receiverId){
                isWithinBoard = true;
                let squareIndex = previousPositionDatum.receiverId.split('-')[1];
                receptacleToFill = document.getElementById(previousPositionDatum.receiverId);
                fillWord = wordList[squareIndex-1];
            }
        }

        checkStatus(moverId, receiverId);

        previousPositionData[receiverIdIndex].moverId = moverId;
        previousPositionData[receiverIdIndex].receiverId = receiverId;
        if(isWithinBoard){
            previousPositionDatum.moverId = '';
            previousPositionDatum.receiverId = '';
        }
    }

}

function dropOnSquare(event) {
    event.preventDefault();
    let moverId = event.dataTransfer.getData("text");
    let receiverId = event.currentTarget.id;
    handleDropOnSquare(moverId, receiverId);
}

function dropOnSquareMobile(moverId, receiverId){
    handleDropOnSquare(moverId, receiverId);
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
    }
}

document.addEventListener("DOMContentLoaded", function (event) {
  const receptacles = document.getElementsByClassName('receptacle');
  for (let receiver of receptacles) {
      receiver.addEventListener('drop', dropOnSquare, false);
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
