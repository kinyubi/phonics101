
// ====================================================
// Word Spinner -- used by the Spell tab
// ====================================================
let WPrefix = "";
let WVowel = "";
let WSuffix = "";

WSpin = {		// Word Spinner

	init: function(){
		//alert("in WSpin.init");
		WSpin.prefix = "";
		WSpin.vowel  = "";
		WSpin.suffix = "";
	},
    spinIt: function(pvs, letters, finalVowel) {
        if(pvs==="p") {WPrefix = letters;}
        if(pvs==="v") {WVowel = letters;}
        if(pvs === "s") {WSuffix = letters;}
        document.getElementById("spinResult").innerHTML = WPrefix+WVowel+WSuffix+finalVowel;
    },

    wordSpinner: function (pvs,letters){this.spinIt(pvs, letters, "");},

    wordSpinnerPlusE: function (pvs,letters){this.spinIt(pvs, letters, "e");}
}

// ====================================================
// Turn scrolling off when colorbox modal is active
// see https://www.geeksforgeeks.org/how-to-disable-scrolling-temporarily-using-javascript/
// ====================================================

function disableScroll() {
    document.body.classList.add("body__scroll_disable");
}

function enableScroll() {
    document.body.classList.remove("body__scroll_disable");
}

function eraseSpellBox() {
    $("#spinResult").html("");
}

// ====================================================
// Sets a cookie to give PHP screen and window size
// ====================================================
function setScreenCookie() {
    let cookieName = 'readxyz_screen';
    let screenStr = screen.width.toString() + ',' + screen.height.toString();
    let windowStr = window.innerWidth.toString() + ',' + window.innerHeight.toString();
    let cookieValue = screenStr + ',' + windowStr;
    Cookies.set(cookieName, cookieValue, { expires: 1 });
}

function format(fmt, ...args){
    return fmt.split("%%").reduce((aggregate, chunk, i) => aggregate + chunk + (args[i] || ""), "");
}

function seeZoo() {
    let width = (screen.availWidth < 700) ? screen.availWidth - 20 : Math.min((window.innerWidth - 40), 900);
    let height = (screen.availWidth < 700) ? screen.availHeight -20 : window.innerHeight - 40;
    let staticConfig = 'toolbar=no,menubar=no,scrollbars=no,resizable=no,location=no,directories=no,status=no,left=10,top=10';
    let specs = format('height=%%,width=%%,%%', height, width, staticConfig);
    window.open('/seeZoo', 'newwindow', specs);
    // setTimeout( function(){document.location.href='/My_Folder/PHP/file_2.php'}, 1000 );
}

function doNothing(data) {}

function noAward(data) {
    if (data === '0') {
        alert("Sorry. You can't earn prizes that quickly.")
    } else {
        window.location.assign('/reloadLesson');
    }
}

$(document).ready(function () {
    WSpin.init();
    setScreenCookie();
});
