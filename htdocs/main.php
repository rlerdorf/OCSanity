<!DOCTYPE html>
<html lang="en">
<head>
  <title>OpenCore config.plist Sanity Checker</title>
  <meta content="Sanity check your OpenCore config.plist" name="description">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css" type="text/css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/dropzone.css" type="text/css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" type="text/css"/>
  <link rel="stylesheet" href="main.css?version=1.1" type="text/css"/>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/dropzone.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-timeago/1.6.7/jquery.timeago.min.js"></script>
</head>
<body>

<br>
<br>

<div class="container">
  <div class="row">
  <?php if($show_upload):?>
    <div class="col-md-8">
      <form action="upload.php" enctype="multipart/form-data" class="dropzone" id="file-upload">
      <h2>Sanity Checker</h2>
        <div>
          <fieldset>
            <legend>Choose platform and OpenCore version: </legend>
            <?=$select_rules?>
          </fieldset>
        </div>
      </form>
    </div>
    <div class="col-sm-4 recent">
      <h3>Recent Checks</h3>
      <hr>
      <?=$links;?>
    </div>
    <?php else:?>
    <div class="col-md-12 col-centered results">
      <?= $results(); ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<script type="text/javascript">
  $( function() { $("#file-upload input[type='radio']").checkboxradio(); } );
  jQuery("time.timeago").timeago();
  Dropzone.options.fileUpload = {
  dictDefaultMessage: "<hr><div class=\"clickhere\">Then click here to choose<br>your config.plist or drag it here</div>",
    maxFilesize:1,
    acceptedFiles: ".plist",
    success: function(file, response) {
                 window.location.replace("/?file="+response.file+"&rs="+$("#file-upload input[type='radio']:checked").attr('value'));
             }
  };
</script>

</body>
</html>
