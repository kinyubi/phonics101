
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
     //mover.removeEventListener('touchend', touchSquare, {passive: true});
 }
}
