<!DOCTYPE html>
<html lang="en">
<head>
  <title>OpenCore config.plist Sanity Checker</title>
  <meta content="Sanity check your OpenCore config.plist" name="description">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css" type="text/css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css" type="text/css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/dropzone.css" type="text/css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" type="text/css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.18.1/styles/magula.min.css" />
  <link rel="stylesheet" href="main.css?version=1.7" type="text/css"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/dropzone.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-timeago/1.6.7/jquery.timeago.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.18.1/highlight.min.js" integrity="sha256-eOgo0OtLL4cdq7RdwRUiGKLX9XsIJ7nGhWEKbohmVAQ=" crossorigin="anonymous"></script>
</head>
<body>
<a href="/"><img src="oclogo.png" class="logo" alt="OC Sanity Check" width="128"></a>
<br>

<div class="container">
  <div class="row">
  <?php if($show_upload):?>
    <div class="col-md-7">
      <form action="upload.php" enctype="multipart/form-data" class="dropzone" id="file-upload">
        <h2>OpenCore Sanity Checker</h2>
        <h5>Choose CPU Architecture and OC Version</h3>
        <br>
        <div class="row">
          <div class="col-md-6">
            <div class="input-group mb-3">
              <div class="input-group-prepend"><label class="input-group-text" for="selectarch">CPU</label></div>
              <select class="form-control" name="selectarch" id="selectarch">
                <?=$archopts?>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="input-group mb-3">
              <div class="input-group-prepend"><label class="input-group-text" for="selectver">Version</label></div>
              <select class="form-control" name="selectver" id="selectver">
                <?=$veropts?>
              </select>
            </div>
          </div>
          <input type="hidden" name="ruleset" id="ruleset" value="">
        </div>
      </form>
    </div>
    <div class="col-sm-4 recent">
      <h3>Recent Checks</h3>
      <hr>
      <?=$links;?>
    </div>
    <?php else:?>
    <div class="col-md-12 col-centered results" id="resultview">
      <button type="button" class="btn btn-primary float-right" id="viewA">Show Raw XML</button>
      <?= $results(); ?>
    </div>
    <div id="xmlview" class="col-md-12 col-centered xml" style="display:none">
      <button type="button" class="btn btn-primary float-right" id="viewB">Sanity Check</button>
      <h1>config.plist</h1>
      <pre><code class="lang-xml"><?=$filtered_xml;?></code></pre>
    </div>
    <?php endif; ?>
  </div>
</div>

<script type="text/javascript">
  jQuery("time.timeago").timeago();
  var $select1 = $('#selectarch'), $select2 = $('#selectver'), $options = $select2.find('option');
  var $defaultval = '<?=$default_version?>';
  $select1.on('change', function() {
	 $select2.html($options.filter('[value="' + this.value + '"]'));
     $('#selectver option:contains(' + $defaultval + ')').prop({selected: true});
     $('#ruleset').val($('#selectver').val()+$('#selectver option:selected').text().replace(/\./g, ''));
  } ).trigger('change');
  $select2.on('change', function() {
     $('#ruleset').val($('#selectver').val()+$('#selectver option:selected').text().replace(/\./g, ''));
  } ).trigger('change');

  Dropzone.options.fileUpload = {
  dictDefaultMessage: "<hr><div class=\"clickhere\">Then click here to choose<br>your config.plist or drag it here</div>",
    maxFilesize:1,
    acceptedFiles: ".plist",
    success: function(file, response) {
                 var $short = $('#selectver').val()+$('#selectver option:selected').text().replace(/\./g, '');
                 window.location.replace("/?file="+response.file+"&rs="+$short);
             }
  };

  hljs.initHighlightingOnLoad();

  $('#viewA').on('click', function(event) {
      $('#resultview').toggle();
      $('#xmlview').toggle();
  });

  $('#viewB').on('click', function(event) {
      $('#resultview').toggle();
      $('#xmlview').toggle();
  });
</script>

</body>
</html>
