<?php

require dirname(__DIR__) . '/autoload.php';
$i = 0;
$urls = [
    "https://wordwall.net/embed/a47cb1a0652c4d55be1bd75c08852406?themeId=49&templateId=45",
    "https://wordwall.net/embed/711c28da0b9a47adbd46d4a127a437b9?themeId=1&templateId=2",
    "https://wordwall.net/embed/83a3b3caa5284e21a7b239cff52c39e3?themeId=27&templateId=70",
    "https://wordwall.net/embed/66270446171e47208f96d6ff262af6b8?themeId=27&templateId=7"
]

?>



<!DOCTYPE html>
<html lang="en">
<head>
  <title>Bootstrap Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/css/bootstrap4-custom/bootstrap.css">
  <script src="/js/jquery-3.5.1.js"></script>
    <script src="/js/popper.min.js"></script>
  <script src="js/bootstrap4-custom/bootstrap.bundle.js"></script>
</head>
<body>
<form method='POST' id='data' style='display:none'>
  <label for='screenwidth'></label><input type='text' id='screenwidth' name='screenwidth'>
  <label for='screenheight'></label><input type='text' id='screenheight' name='screenheight'>
  <label for='windowinnerWidth'></label><input type='text' id='windowinnerWidth' name='windowinnerWidth'>
  <label for='windowinnerHeight'></label><input type='text' id='windowinnerHeight' name='windowinnerHeight'>

  <input type='submit'></form>
<script>document.getElementById('screenwidth').value = screen.width;
    document.getElementById('screenheight').value = screen.height;
    document.getElementById('windowinnerWidth').value = window.innerWidth;
    document.getElementById('windowinnerHeight').value = window.innerHeight;
    document.forms.namedItem('data').submit();




</script>



<div class="container">
  <h2>Responsive Embed</h2>
    <button class="btn btn-primary" data-toggle="modal" data-target="#myModal">Trigger Modal in iFrame</button>

    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
<!--            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>-->
            <h4 class="modal-title" id="myModalLabel">Modal title</h4>
          </div>
          <div class="modal-body embed-responsive embed-responsive-25by19">
              <iframe class="embed-responsive-item" src="<?php echo $urls[0]; ?>" allowtransparency="true"></iframe>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
<script>
    $('document').ready(function () {

    });
</script>

</body>
</html>
