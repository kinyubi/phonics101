<?php
$videoNum = $_REQUEST['n'] ?? 0;
$videoUrl = "/images/mp4/$videoNum.mp4";
?>
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>readXYZ </title>

    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no"/>

    <!--[if IE]>
    <link rel="shortcut icon" href="/images/favicons/favicon.ico"><![endif]-->
    <link rel="apple-touch-icon" href="/images/favicons/favicon-57.png">
    <link rel="shortcut icon" href="/images/favicons/favicon.ico">
    <link rel="icon" sizes="16x16 32x32 64x64" href="/images/favicons/favicon.ico">

    <script src="https://kit.fontawesome.com/12c5ce46e9.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/css/bootstrap4-custom/bootstrap.min.css?ver=1.0407.0">

    <link rel="stylesheet" href="/css/colorbox/colorbox.css" type="text/css">

    <link href="https://fonts.googleapis.com/css?family=Muli" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="/css/phonics.css?ver=1.0407.0" media="all">


    <script src="/js/jquery-3.5.1.js"></script>
    <script src="/js/jquery.colorbox.js"></script>
    <script src="/js/popper.min.js"></script>
    <script src="/js/bootstrap4-custom/bootstrap.bundle.js?ver=1.0117.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js"></script>
    <script src="/js/phonics.js?ver=1.0407.0"></script>

    </head>

<body>


<div class="m-0 p-0 p-sm-1 p-md-3 p-lg-5 container-fluid" id="base-container" style="max-width: 950px;">




            <video id="award-video" controls type="video/mp4" preload="auto" >
        <source src="<?php echo $videoUrl ?>">
    </video>
    <script>
        function playVideo() {
            let video = document.getElementById("award-video");
            video.play();
        }

        $(document).ready(function () {
            $("#award-video").prop("volume", 0.1);
            window.addEventListener('unload', function() {$.post( "http://phonics101.test/handler/award");});
            // let video = document.getElementById("award-video");
            // video.addEventListener('loadeddata', playVideo);
        });
    </script>



</div>



<script>

    $(document).ready(function () {
                $('.modal-backdrop').removeClass("modal-backdrop");
        $('[data-toggle="tooltip"]').tooltip({'delay': {show: 500, hide: 100}});

        $(window).resize(function () {
            setScreenCookie();
        });

        $(".ajax").colorbox();
        $(".intro-image").colorbox({photo: true, innerWidth: 450, innerHeight: 500, top: 200});
        $(".iframe").colorbox({iframe: true, width: "80%", height: "80%"});
        $(".inline").colorbox({inline: true, width: "50%"});
        $(".info-box").colorbox({inline: true, width: 400, top: 50, left: 450});
        $(".zoo-animals").colorbox({
            iframe: true, innerWidth: "660px", maxWidth: "90%", innerHeight: "660px", maxHeight: "90%",
            top: 20, overlayClose: false, scrolling: true
        });

        $(".tic-tac-toe").colorbox({
            iframe: true, innerWidth: "660px", maxWidth: "95%", innerHeight: "660px", maxHeight: "90%",
            top: 20, overlayClose: false, scrolling: false
        });
                $(".award").colorbox({
            iframe: true, innerWidth: "700px", maxWidth: "98%", innerHeight: "500px", maxHeight: "98%",
            top: 20, overlayClose: false, scrolling: false, onClosed: function() {advanceAnimal();}
        });

        $(".games").colorbox({iframe: true, top: 20, className: "gameFrame", width: "750px", height: "540px"});
        $(".sound-box").colorbox({
            iframe: true, scrolling: false, overlayClose: false, width: "450px", height: "400px"
        });


        $.colorbox.settings.close = '<i class="fa fa-times-circle-o" aria-hidden="true" style="vertical-align: bottom"></i>';

        $(document).bind('cbox_open', function () {
            $('html').addClass("body__scroll_disable");
        }).bind('cbox_cleanup', function () {
            $('html').removeClass("body__scroll_disable");
        });

    });

</script>

</body>
</html>

