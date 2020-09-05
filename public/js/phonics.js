
$( document ).ready(function() {
    // use this to initialize all the functions that require an clear on each page
	WSpin.init();
});

// ====================================================
// Word Spinner
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

function scaleIframe(gameId)
{
    const wrapper = $("#" + gameId + "-iframe-wrapper");
    let _wrapWidth = wrapper.width();
    let _frameWidth = $(wrapper[0].contentDocument).width();

    if(!this.contentLoaded) this.initialWidth=_frameWidth;
    this.contentLoaded=true;
    let frame = wrapper[0];

    let percent = (_wrapWidth/this.initialWidth).toString(10);

    frame.style.width=100.0/percent+"%";
    frame.style.height=100.0/percent+"%";

    frame.style.zoom=percent;
    frame.style.MozTransform="scale(" + percent + ")";
    frame.style.MozTransformOrigin='top left';
    frame.style.oTransform='scale('+percent+')';
    frame.style.oTransformOrigin='top left';
  }
