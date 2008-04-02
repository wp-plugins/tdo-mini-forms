<?php

//////////////////////////
// Process Form Request //
//////////////////////////

if (!isset($_SESSION)) session_start();

// Load up Wordpress
//
$wp_config = realpath("../../../wp-config.php");
if (!file_exists($wp_config)) {
   exit("Can't find wp-config.php");
}
require_once($wp_config);

global $wpdb, $tdomf_form_widgets_validate, $tdomf_form_widgets_preview;

// loading text domain for language translation
//
load_plugin_textdomain('tdomf',PLUGINDIR.DIRECTORY_SEPARATOR.TDOMF_FOLDER);

// Form id
//
if(!isset($_POST['tdomf_form_id'])) {
  tdomf_log_message("tdomf-form-post: No Form ID set!",TDOMF_LOG_BAD);
  exit(__("TDOMF: No Form id!","tdomf"));
}
$form_id = intval($_POST['tdomf_form_id']);
if(!tdomf_form_exists($form_id)){
  tdomf_log_message("tdomf-form-post: Bad form id %d!",TDOMF_LOG_BAD);
  exit(__("TDOMF: Bad Form Id","tdomf"));
}

// Security Check
//
if(!isset($_SESSION['tdomf_key_'.$form_id]) || $_SESSION['tdomf_key_'.$form_id] != $_POST['tdomf_key_'.$form_id]) {
   if(ini_get('register_globals') && !TDOMF_HIDE_REGISTER_GLOBAL_ERROR){
     tdomf_log_message('register_globals is enabled. This will prevent TDOMF from operating.',TDOMF_LOG_ERROR);
     exit(__("TDOMF: Bad data submitted. <i>register_globals</i> is enabled. This must be set to disabled.","tdomf"));
   } else  if(!isset($_SESSION) || !isset($_SESSION['tdomf_key_'.$form_id]) || trim($_SESSION['tdomf_key_'.$form_id]) == "") {
     tdomf_log_message('Key is missing from $_SESSION: contents of $_SESSION:<pre>'.var_export($_SESSION,true)."</pre>",TDOMF_LOG_BAD);
   }
   $session_key = $_SESSION['tdomf_key_'.$form_id];
   $post_key = $_POST['tdomf_key_'.$form_id];
   $ip = $_SERVER['REMOTE_ADDR'];
   tdomf_log_message("Form ($form_id) submitted with bad key (session = $session_key, post = $post_key) from $ip !",TDOMF_LOG_BAD);
   unset($_SESSION['tdomf_key_'.$form_id]);
   exit(__("TDOMF: Bad data submitted. Please return to the previous page and reload it. Then try submitting your post again.","tdomf"));
}
unset($_SESSION['tdomf_key_'.$form_id]);

function tdomf_fixslashesargs() {
    if (get_magic_quotes_gpc()) {
      tdomf_log_message_extra("Magic quotes is enabled. Stripping slashes!");
      if(!function_exists('stripslashes_array')) {
        function stripslashes_array($array) {
            return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
        }
      }
      $_COOKIE = stripslashes_array($_COOKIE);
      #$_FILES = stripslashes_array($_FILES);
      #$_GET = stripslashes_array($_GET);
      $_POST = stripslashes_array($_POST);
      $_REQUEST = stripslashes_array($_REQUEST);
    }
}

// Double check user permissions
//
$message = tdomf_check_permissions_form($form_id);

// Now either generate a preview or create a post
//
$save_post_info = FALSE;
if($message == NULL) {
  if(isset($_POST['tdomf_form'.$form_id.'_send'])) {

    tdomf_log_message("Someone is attempting to submit something");

    $message = tdomf_validate_form($_POST,false);
    if($message == NULL) {
      $args = $_POST;
      $args['ip'] = $_SERVER['REMOTE_ADDR'];
      $retVal = tdomf_create_post($args);
      // If retVal is an int it's a post id
      if(is_int($retVal)) {
        $post_id = $retVal;
        if(get_post_status($post_id) == 'publish') {
          $message = sprintf(__("Your submission has been automatically published. You can see it <a href='%s'>here</a>. Thank you for using this service.","tdomf"),get_permalink($post_id));
        } else {
          $message = sprintf(__("Your post submission has been added to the moderation queue. It should appear in the next few days. If it doesn't please contact the <a href='mailto:%s'>admins</a>. Thank you for using this service.","tdomf"),get_bloginfo('admin_email'));
        }
      // If retVal is a string, something went wrong!
      } else {
        $message = sprintf(__("Your submission contained errors:<br/><br/>%s<br/><br/>Please correct and resubmit.","tdomf"),$retVal);
        $save_post_info = TRUE;
        tdomf_fixslashesargs();
      }
    } else {
      $save_post_info = TRUE;
      tdomf_fixslashesargs();
    }
  } else if(isset($_POST['tdomf_form'.$form_id.'_preview'])) {

    // For preview, remove magic quote slashes!
    tdomf_fixslashesargs();

       $save_post_info = TRUE;
	   $message = tdomf_validate_form($_POST,true);
	   if($message == NULL) {
		  $message = tdomf_preview_form($_POST);
	   }
	}
}

if(!isset($post_id) || get_post_status($post_id) != 'publish') {
  // Go back to form with args
  //
  $redirect_url = $_POST['redirect'];
  if($save_post_info) {
    $args = $_POST;
  } else {
    $args = array();
    $args['tdomf_no_form_'.$form_id] = true;
  }
  $args['tdomf_post_message_'.$form_id] = $message;
  $_SESSION['tdomf_form_post_'.$form_id] = $args;
} else {
  unset($_SESSION['tdomf_form_post_'.$form_id]);
  $redirect_url = get_permalink($post_id);
}
//
header("Location: $redirect_url");
exit;
?>