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
  <link rel="stylesheet" href="main.css" type="text/css"/>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/dropzone.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
</head>
<body>

<div class="jumbotron text-center">
  <h3>OpenCore config.plist Sanity Checker</h3>
</div>

<div class="container">
  <div class="row">
    <div class="col-md-12">
      <form action="upload.php" enctype="multipart/form-data" class="dropzone" id="file-upload">
        <div>
          <fieldset>
            <legend>Choose platform and OpenCore version: </legend>
            <?=$select_rules?>
          </fieldset>

        </div>
      </form>
    </div>
  </div>
  <?= $results(); ?>
</div>

<script type="text/javascript">
  $( function() { $("#file-upload input[type='radio']").checkboxradio(); } );
  Dropzone.options.fileUpload = {
    dictDefaultMessage: "Then drop your config.plist file or click to select",
    maxFilesize:1,
    acceptedFiles: ".plist",
    success: function(file, response) {
                 window.location.replace("/?file="+response.file+"&rs="+$("#file-upload input[type='radio']:checked").attr('value'));
             }
  };
</script>

</body>
</html>
