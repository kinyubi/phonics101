// ---------------------------------------------------
// Sound Boxes
// ---------------------------------------------------
let soundBox = {
    color: "#00aabe",
    count: 3,
    tileLetter: '',
    currentBox: 0,

    moveLowerBall: function(num) {
        let lowerBall = document.getElementById('ball-' + num.toString());
        let upperBall = document.getElementById('uball-' + num.toString());
        lowerBall.style.visibility = "hidden";
        upperBall.style.visibility = "visible";
        soundBox.currentBox = num;
    },

    moveUpperBall: function(num) {
        let lowerBall = document.getElementById('ball-' + num.toString());
        let upperBall = document.getElementById('uball-' + num.toString());
        lowerBall.style.visibility = "visible";
        upperBall.style.visibility = "hidden";
        soundBox.erase(num);
        soundBox.currentBox = -1;
    },

    tileClicked: function(letter) {
        soundBox.tileLetter = letter
        const instructions = document.getElementById("tile-instructions");
        instructions.style.visibility = "visible";
        soundBox.appendLetter(soundBox.currentBox);
    },

    erase: function (idNum) {
        let letterTile = document.getElementById(("box-letters-" + idNum));
        letterTile.innerText = '';
    },

    exchange: function(idNum) {
        let num = parseInt(idNum);
        if ((num < 0) || (num >= soundBox.count)) return;

        let letterTile = document.getElementById(("box-letters-" + idNum));
        const instructions = document.getElementById("tile-instructions");
        letterTile.innerText = soundBox.tileLetter;
        instructions.style.visibility = "hidden";
        soundBox.tileLetter = "";
    },

    appendLetter: function(idNum) {
        let num = parseInt(idNum);
        if ((num < 0) || (num >= soundBox.count)) return;

        let letterTile = document.getElementById(("box-letters-" + idNum));
        let tileText = letterTile.innerText;
        let letter = soundBox.tileLetter;
        soundBox.tileLetter = "";
        const instructions = document.getElementById("tile-instructions");
        instructions.style.visibility = "hidden";
        if (tileText.length > 3) return ;
        letterTile.innerText = tileText + letter;
    },

    writeCookie: function() {
        let countStr = soundBox.count.toString();
        let cookieValue = countStr + soundBox.color;
        let d = new Date();
        d.setTime(d.getTime() + (30 * 24 * 60 * 60 * 1000));
        let expires = "expires=" + d.toUTCString();
        document.cookie = "readxyz_sound_box=" + cookieValue + ";" + expires + ";path=/";
    },

    updateSoundBoxCookie: function () {
        let name = "readxyz_sound_box=";
        let cookieValue = "";
        let decodedCookie = decodeURIComponent(document.cookie);
        let parts = decodedCookie.split(';');
        for (let i = 0; i < parts.length; i++) {
            let rawCookie = parts[i].trim();
            // if this isn't the name part, its the value part
            if (rawCookie.indexOf(name) === 0) {
                cookieValue = rawCookie.substring(name.length, rawCookie.length);
                break;
            }
        }
        if (cookieValue !== "") {
            soundBox.count = parseInt(cookieValue.substring(0, 1));
            soundBox.color = cookieValue.substring(1);
        } else {
            soundBox.count = 3;
            soundBox.color = '#00aabe'
        }
        soundBox.writeCookie();
    },

    setColorDirect: function(newColor) {
        soundBox.color =  (newColor[0] === '#') ? newColor : '#00aabe';
        soundBox.writeCookie()
        let elements = document.getElementsByClassName('ball');
        for (let i = 0; i < elements.length; i++) {
            elements[i].style.color = soundBox.color;
        }
    },

    setColor: function(e) {
        let me = e.target;
        let tag = me.tagName;
        if (tag === 'a') me = me.firstElementChild;
        soundBox.color = me.style.color;
        soundBox.writeCookie();
        let elements = document.getElementsByClassName('ball');
        for (let i = 0; i < elements.length; i++) {
            elements[i].style.color = soundBox.color;
        }
        return false; //don't bubble up
    },

    setCount: function(e) {
        let me = e.target;
        let tag = me.tagName;
        if (tag === 'a') me = me.firstElementChild;
        soundBox.count = parseInt(me.innerText);
        soundBox.writeCookie();
        soundBox.reload();
        return false; // don't bubble up if the child element triggered.
    },

    reload: function() {
        let lessonName = document.getElementById('sound-box-lesson-name').innerText;
            window.location.href = '/handler/lesson/' + lessonName + '/write';
    }

};



$(document).ready(function () {
    soundBox.updateSoundBoxCookie();
    soundBox.setColorDirect(soundBox.color);
    let colorButtons = document.getElementsByClassName("color-click");
    for (let el of colorButtons) {
        el.addEventListener('click', soundBox.setColor, false);
    }
    let countButtons = document.getElementsByClassName("count-click");
    for (let el of countButtons) {
        el.addEventListener('click', soundBox.setCount, false);
    }

});


