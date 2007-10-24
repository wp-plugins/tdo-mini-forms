<?php

//////////////////////////
// Inline upload!       //
//////////////////////////

// 1. User uploads files to a temporary area. Files will be deleted within an
//    hour if not "claimed"
// 2. User submits post.
// 3. Widget copies the files from a temporary area to their proper location and
//    updates post with info about claimed files.
//
// * If post is deleted, files are automatically deleted
// * No direct links to files are exposed (as long as the admins specify a
//   location not directly exposed to the web)

// Session start
//
if (!isset($_SESSION)) session_start();

// Load up Wordpress
//
$wp_config = realpath("../../../wp-config.php");
if (!file_exists($wp_config)) {
   exit("Can't find wp-config.php");
}
require_once($wp_config);

// loading text domain for language translation
//
load_plugin_textdomain('tdomf','wp-content/plugins/tdomf');

// URL for this form
$tdomf_upload_inline_url = TDOMF_URLPATH . 'tdomf-upload-inline.php';

// First pass security check
//
if(isset($_SESSION['tdomf_upload_key']) && $_SESSION['tdomf_upload_key'] != $_POST['tdomf_upload_key']){
   #tdomf_log_message("Upload form submitted with bad key from ".$_SERVER['REMOTE_ADDR']." !",TDOMF_LOG_BAD);
   unset($_SESSION['tdomf_upload_key']); // prevents any "operations" on uploads
   #exit("TDOMF: Bad data submitted");
}

// Permissions check
//
if(!tdomf_can_current_user_see_form()) {
  tdomf_log_message("Someone with no permissions tried to access the inline-uplaod form!",TDOMF_LOG_BAD);
  unset($_SESSION['tdomf_upload_key']);
  exit("TDOMF: Bad permissions");
}

// Widget in use check
//
if(!in_array("upload-files",tdomf_get_widget_order())) {
  exit("TDOMF: Upload feature not yet enabled");
}

// Grab options for uploads
//
$options = tdomf_widget_upload_get_options();

// Placeholder for error messages
//
$errors = "";

// Files recorded in session
//
$sessioncount = 0;
$mysessionfiles = array();

// Files uploaded now
//
$myfiles = array();
$count = 0;

// Double check files in $_SESSION!
//
if(isset($_SESSION['uploadfiles'])) {
  $sessioncount = 0;
  $mysessionfiles = $_SESSION['uploadfiles'];
  for($i =  0; $i < $options['max']; $i++) {
    if(!file_exists($mysessionfiles[$i]['path'])) {
      unset($mysessionfiles[$i]);
    } else {
      $sessioncount++;
    }
  }
}

// Allowed file extensions (used when file is uploaded and in javascript)
//
$allowed_exts = split(" ",strtolower($options['types']));

// Only do actions if key is good!
//
if(isset($_SESSION['tdomf_upload_key'])) {

  // Delete files at user request
  //
  if(isset($_POST['tdomf_upload_inline_delete_all'])) {
    for($i =  0; $i < $options['max']; $i++) {
      tdomf_delete_tmp_file($mysessionfiles[$i]['path']);
    }
    $mysessionfiles = array();
    $sessioncount = 0;
    unset($_SESSION['uploadfiles']);
  }

  // Only worry about uploaded files if the upload secruity key is good
  //
  else if(isset($_POST['tdomf_upload_inline_submit'])) {

    // Move the uploaded file to the temp storage path
    //
    for($i =  0; $i < $options['max']; $i++) {
      $upload_temp_file_name = $_FILES["uploadfile$i"]['tmp_name'];
      $upload_file_name = $_FILES["uploadfile$i"]['name'];
      $upload_error = $_FILES["uploadfile$i"]['error'];
      $upload_size = $_FILES["uploadfile$i"]['size'];
      $upload_type = $_FILES["uploadfile$i"]['type'];
      if(is_uploaded_file($upload_temp_file_name)) {
        // double check file extension
        $ext = strtolower(strrchr($upload_file_name,"."));
        if(in_array($ext,$allowed_exts)) {
          $storagepath = tdomf_create_tmp_storage_path();
          $uploaded_file = $storagepath.DIRECTORY_SEPARATOR.$upload_file_name;
          #tdomf_log_message("Saving uploaded file to $uploaded_file");
          // Save the file
          if(move_uploaded_file($upload_temp_file_name,$uploaded_file)) {
            // Remember the file
            $myfiles[$i] = array( "name" => $upload_file_name, "path" => $uploaded_file, "size" => $upload_size, "type" => $upload_type );
            $count++;
            tdomf_log_message("File $upload_file_name saved to tmp area as $uploaded_file. It has a size of $upload_size and type of $upload_type" );
            // within an hour, delete the file if not claimed!
            wp_schedule_single_event( time() + TDOMF_UPLOAD_TIMEOUT, 'tdomf_delete_tmp_file_hook', array($uploaded_file) );
          } else {
            tdomf_log_message("move_uploaded_file failed!");
            $errors .= sprintf(__("Could not move uploaded file %s to storage area!<br/>","tdomf"),$upload_file_name);
          }
        } else {
          tdomf_log_message("file $upload_file_name uploaded with bad extension: $ext");
          $errors .= sprintf(__("Files with %s extensions are forbidden.<br/>","tdomf"),$ext);
        }
      } else if($upload_error != 0 && !empty($upload_file_name)){
        tdomf_log_message("There was a reported error $upload_error with the uploaded file!");
        switch($upload_error) {
          case 1 :
            $errors .= sprintf(__("Sorry but %s was too big. It exceeded the server configuration.<br/>","tdomf"),$upload_file_name);
            break;
          case 2:
            $errors .= sprintf(__("Sorry but %s was too big. It was greater than %s. It exceeded the configured maximum.<br/>","tdomf"),$upload_file_name,tdomf_filesize_format($options['size']));
            break;
          case 3:
            $errors .= sprintf(__("Sorry but only part of %s was uploaded.<br/>","tdomf"),$upload_file_name);
            break;
          case 4:
            $errors .= __("Sorry file does not exist.<br/>","tdomf");
            break;
          default;
            $errors .= sprintf(__("Upload of %s failed for an unknown reason. (%s)<br/>","tdomf"),$upload_file_name,$upload_error);
            break;
        }
      } else {
        #tdomf_log_message("No file here",TDOMF_LOG_ERROR);
      }
    }
    // Store in session!
    $mysessionfiles = array_merge($myfiles, $mysessionfiles);
    $_SESSION['uploadfiles'] = $mysessionfiles;
    // Recount
    $sessioncount = 0;
    for($i =  0; $i < $options['max']; $i++) {
      if(file_exists($mysessionfiles[$i]['path'])) {
        $sessioncount++;
      }
    }
  }
}

// Create new security key
//
unset($_SESSION['tdomf_upload_key']);
$random_string = tdomf_random_string(100);
$_SESSION["tdomf_upload_key"] = $random_string;

// Now the fun bit, the actually form!
//
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<!-- <link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" /> -->
<?php tdomf_stylesheet(); ?>
<script type="text/javascript">
// <![CDATA[
function endsWith(str,ends){
   var startPos = str.length - ends.length;
   if (startPos < 0) {
      return false;
   }
   return (str.lastIndexOf(ends, startPos) == startPos);
}
function validateFile(id,msg) {
  var e1 = document.getElementById(id);
  if(e1 != null) {
    var f = e1.value.toLowerCase();
    if(f.length > 0) {
      <?php foreach($allowed_exts as $e) {
        if(!empty($e)) { ?>
          if(endsWith(f,"<?php echo $e; ?>")) { return true; }
      <?php } } ?>
    } else {
      // Nothing to validate so okay
      return true;
    }
    if(msg) {
      alert("<?php printf(__("The file must be of type %s!","tdomf"),$options['types']); ?>");
    }
    return false;
  }
  // Nothing to validate so okay
  return true;
}
function validateForm() {
  <?php for($i =  0, $j = 0; $i < $options['max']; $i++) { ?>
  if(!validateFile('uploadfile<?php echo $i; ?>'),false) {
    var f = document.getElementById('uploadfile<?php echo $i; ?>').value;
    alert( "<?php printf(__('File %s has a bad extension and cannot be upload!','tdomf'),'" + f + "'); ?>" );
    return false;
  }
  <?php } ?>
  return true;
}
// ]]>
</script>
</head>
<body>

<?php if($errors != "") { ?>
  <div class="tdomf_upload_inline_errors">
  <?php echo $errors; ?>
  </div>
<?php } ?>

<form name="tdomf_upload_inline_form" id="tdomf_upload_inline_form" enctype="multipart/form-data" method="post" action="<?php echo $tdomf_upload_inline_url; ?>"  >
  <input type='hidden' id='tdomf_upload_key' name='tdomf_upload_key' value='<?php echo $random_string ?>' >
  <input type='hidden' name='MAX_FILE_SIZE' value='<?php echo $options['size']; ?>' />
  <?php if($sessioncount > 0) { ?>
  <p><?php _e("Your files will be kept on the server for 1 hour. You must submit your post before then.","tdomf"); ?></p>
  <?php } ?>
  <?php if($sessioncount < $options['max']) { ?>
  <p><small>
  <?php printf(__("Max File Size: %s","tdomf"),tdomf_filesize_format($options['size'])); ?><br/>
  <?php printf(__("Allowable File Types: %s","tdomf"),$options['types']); ?><br/>
  </small></p>
  <?php } ?>
  <?php for($i =  0, $j = 0; $i < $options['max']; $i++) {
      if(isset($mysessionfiles[$i])) { ?>
        <input type='hidden' name='deletefile[]' value="<?php echo $i; ?>" />
        <?php printf(__("<i>%s</i> (%s) Uploaded","tdomf"),$mysessionfiles[$i]['name'],tdomf_filesize_format($mysessionfiles[$i]['size'])); ?>
        <br/>
    <? } else {
      if(($sessioncount + $j) < $options['min']) { ?>
        <label for='uploadfile<?php echo $i; ?>' class='required'>
      <?php } else { ?>
        <label for='uploadfile<?php echo $i; ?>'>
      <?php } _e("Upload: ","tdomf"); $j++; ?>
      <input type='file' name='uploadfile<?php echo $i; ?>' id='uploadfile<?php echo $i; ?>' size='30' onChange="validateFile('uploadfile<?php echo $i; ?>',true);" /></label><br/>
  <?php } }?>
  <?php if($sessioncount < $options['max']) { ?>
  <input type="submit" id="tdomf_upload_inline_submit" name="tdomf_upload_inline_submit" value="<?php _e("Upload Now!","tdomf"); ?>" />
  <?php } ?>
  <?php if($sessioncount > 0) { ?>
  <input type="submit" id="tdomf_upload_inline_delete_all" name="tdomf_upload_inline_delete_all" value="<?php _e("Delete All!","tdomf"); ?>" />
  <?php } ?>
</form>

</body>