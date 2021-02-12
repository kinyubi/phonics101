// ---------------------------------------------------
// Sound Boxes
// ---------------------------------------------------
let soundBox = {
    color: "blue",
    count: 3,
    tileLetter: '',

    moveBall: function(num) {
        let ball = document.getElementById('ball-' + num.toString());
        let box = document.getElementById('ball-holder-' + num.toString());
        box.appendChild(ball);
    },

    tileClicked: function(letter) {
        soundBox.tileLetter = letter
        const instructions = document.getElementById("tile-instructions");
        instructions.style.visibility = "visible";
    },

    boxClicked: function(e) {
        let me = e.target;
        let id = me.id;
        let idNum = id.charAt(id.length-1);
        try {
            if(soundBox.tileLetter !== '') {
                let boxTile = document.getElementById(("box-tile-" + idNum));
                let letter = soundBox.tileLetter;
                soundBox.tileLetter = "";
                boxTile.style.visibility = "visible";

                if (id.startsWith("big-box")) {
                    boxTile.innerText = letter;
                    return false;
                } else if (id.startsWith("ball-row")) {
                    boxTile.innerText = letter;
                    return false;
                } else if (id.startsWith("ball-holder")) {
                    boxTile.innerText = letter;
                    return false;
                } else if (id.startsWith("tile-row")) {
                    boxTile.innerText = letter;
                    return false;
                } else if (id.startsWith("tile-cell")) {
                    boxTile.innerText = letter;
                    return false;
                } else if (id.startsWith("tile-holder")) {
                    boxTile.innerText = letter;
                    return false;
                } else if (id.startsWith("box-tile")) {
                    let tileText = boxTile.innerText;
                    if (tileText.length > 2) return false;
                    boxTile.innerText = tileText + letter;
                    return false;
                } else if (id.startsWith("letter")) {

                }
            }
        } catch (err) {
            console.error("rats", err.message);
        } finally {
            const instructions = document.getElementById("tile-instructions");
            instructions.style.visibility = "hidden";
        }
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
            soundBox.color = 'blue'
            soundBox.writeCookie();
        }
    },

    setColorDirect: function(newColor) {
        soundBox.color = newColor; soundBox.writeCookie()
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
    let boxes = document.getElementsByClassName("box");
    for (let el of boxes) {
        el.addEventListener('click', soundBox.boxClicked, false);
    }

});


