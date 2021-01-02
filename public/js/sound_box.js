// ---------------------------------------------------
// Sound Boxes
// ---------------------------------------------------
let soundBoxColor = 'blue';
let soundBoxCount = 3;

function moveBall(num) {
    let ball = document.getElementById('ball-' + num.toString());
    let box = document.getElementById('box-' + num.toString());
    box.appendChild(ball)
}

/**
 * Keeps track of sound-box settings cookie 'readxyz_sound_box'
 * @param cookieValue first char is # of balls, rest of string is html color of balls
 * @param daysToExpire
 */
function setSoundBoxCookie(cookieValue, daysToExpire) {
    let d = new Date();
    d.setTime(d.getTime() + (daysToExpire * 24 * 60 * 60 * 1000));
    let expires = "expires=" + d.toUTCString();
    document.cookie = "readxyz_sound_box=" + cookieValue + ";" + expires + ";path=/";
}

/**
 * find the cookie we're looking for and decode it
 * @returns {string}
 */
function getSoundBoxCookie() {
    let name = "readxyz_sound_box=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let parts = decodedCookie.split(';');
    for (let i = 0; i < parts.length; i++) {
        let cookieValue = parts[i].trim();

        if (cookieValue.indexOf(name) === 0) {
            return cookieValue.substring(name.length, cookieValue.length);
        }
    }
    return "";
}

/**
 * We try to get the cookie. If we can't find it we create one with default values.
 * We return the values into the global variables color and soundBoxCount
 */
function checkSoundBoxCookie() {
    let cookieValue = getSoundBoxCookie();
    if (cookieValue !== "") {
        soundBoxCount = parseInt(cookieValue.substring(0, 1));
        soundBoxColor = cookieValue.substring(1);
    } else {
        soundBoxCount = 3;
        soundBoxColor = 'blue'
        cookieValue = '3blue';
        setSoundBoxCookie(cookieValue, 30);
    }
}

function setColor(newColor) {
    soundBoxColor = newColor;
    reload();
}

function setCount(newCount) {
    soundBoxCount = newCount;
    reload();
}

function reload() {
    let countStr = soundBoxCount.toString();
    let cookieValue = countStr + soundBoxColor;
    setSoundBoxCookie(cookieValue, 30);
    let lessonName = document.getElementById('soundbox-lesson-name').innerText;
    window.location.href = '/handler/lesson/' + lessonName + '/write';
}

$(document).ready(function () {
    checkSoundBoxCookie();
});
