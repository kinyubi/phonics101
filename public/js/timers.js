
function pad(val) {
    let valString = val + "";
    if(valString.length < 2) {
        return "0" + valString;
    } else {
        return valString;
    }
}

let myPracticeInterval = 0;
let totalPracticeSeconds = 0;

let myFluencyInterval = 0;
let totalFluencySeconds = 0;

let myTestInterval = 0;
let totalTestSeconds = 0;


function displayPracticeTime() {
    totalPracticeSeconds++;
    let minutes = pad(Math.floor(totalPracticeSeconds / 60));
    let seconds = pad(totalPracticeSeconds % 60);
    document.getElementById("practiceTime").innerHTML = minutes + ":" + seconds;

}

function displayFluencyTime() {
    totalFluencySeconds++;
    let minutes = pad(Math.floor(totalFluencySeconds / 60));
    let seconds = pad(totalFluencySeconds % 60);
    document.getElementById("fluencyTime").innerHTML = minutes + ":" + seconds;
}

function displayTestTime() {
    totalTestSeconds++;
    let minutes = pad(Math.floor(totalTestSeconds / 60));
    let seconds = pad(totalTestSeconds % 60);
    document.getElementById("testTime").innerHTML = minutes + ":" + seconds;
}

$(function() {
    $("#practiceStartButton").on("click", function (e) {
        myPracticeInterval = setInterval(function() { displayPracticeTime()}, 1000);
    });
    $("#practiceStopButton").on("click", function(e) {
        clearInterval(myPracticeInterval);
    });
    $("#practiceResetButton").on("click", function (e) {
        totalPracticeSeconds = 0;
        document.getElementById("practiceTime").innerHTML = "00:00";
    })

    $("#fluencyStartButton").on("click", function (e) {
        myFluencyInterval = setInterval(function() { displayFluencyTime()}, 1000);
    });
    $("#fluencyStopButton").on("click", function(e) {
        clearInterval(myFluencyInterval);
    });
    $("#fluencyResetButton").on("click", function (e) {
        totalFluencySeconds = 0;
        document.getElementById("fluencyTime").innerHTML = "00:00";
    })

    $("#fluencySaveButton").on("click", function (e) {
        clearInterval(myPracticeInterval);
        let formObj = document.forms['fluencyTimerForm'];
        let seconds = formObj.elements['seconds'];
        let fluencyTimeStr = document.getElementById("fluencyTime").innerHTML;
        let timerMinutes = parseInt(fluencyTimeStr.substring(0,2));
        let timerSeconds = parseInt(fluencyTimeStr.substring(3));
        seconds.value = (timerMinutes * 60) + timerSeconds;
        if (seconds.value > 0) formObj.submit();

    });

    $("#testAdvancingButton, #testMasteredButton").on("click", function (e) {
        let formObj = document.forms['testMasteryForm'];
        /* read label on button pressed */
        let tempAction = e.target.firstChild.textContent;
        if (tempAction.startsWith('A')) {tempAction = "Advancing"} else {tempAction = "Mastered";}
        formObj.elements['masteryType'].value =  tempAction;
        let csvWords = document.getElementById("TM0").innerHTML;
        for (let i = 0; i < 9; i++) {
            csvWords = csvWords + ',' + document.getElementById('TM' + i.toString()).innerHTML;
        }
        formObj.elements['testWords'].value = csvWords;
        formObj.submit();
    });

    $("#testStartButton").on("click", function (e) {
        // document.getElementById("testStartButton").style.pointerEvents = 'none';
        myTestInterval = setInterval(function() { displayTestTime()}, 1000);
    });
    $("#testStopButton").on("click", function(e) {
        clearInterval(myTestInterval);
        // document.getElementById("testStartButton").style.pointerEvents = 'auto';
    });
    $("#testResetButton").on("click", function (e) {
        totalTestSeconds = 0;
        document.getElementById("testTime").innerHTML = "00:00";
    })

    $("#testSaveButton").on("click", function (e) {
        let formObj = document.forms['testTimerForm'];
        let seconds = formObj.elements['seconds'];
        let testTimeStr = document.getElementById("testTime").innerHTML;
        let timerMinutes = parseInt(testTimeStr.substring(0,2));
        let timerSeconds = parseInt(testTimeStr.substring(3));
        seconds.value = (timerMinutes * 60) + timerSeconds;
        if (seconds.value > 0) formObj.submit();
    });

});
