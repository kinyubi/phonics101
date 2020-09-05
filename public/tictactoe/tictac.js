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

    $.colorbox({ closeButton: true, close: "X", opacity: 1, maxWidth: "200px", left: "18%", top: "25%", href: "https://movellas-users.s3.amazonaws.com/comment/201210171910330233/201307010339396490.gif" });
}

function noWinner() {
    $.colorbox({ html: "The game was a tie. Try again!" });
}

function setGameStatus() {
    let combo;
    for (combo of winners) {
        if (cats[combo[0]] && cats[combo[1]] && cats[combo[2]]) { status = "WINCAT"; return; }
    }
    for (combo of winners) {
        if (rats[combo[0]] && rats[combo[1]] && rats[combo[2]]) { status = "WINRAT"; return; }
    }
    if (moves === 9) { status = "DRAW"; return; }
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