
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



$(document).ready(function () {
    WSpin.init();
    setScreenCookie();
});
