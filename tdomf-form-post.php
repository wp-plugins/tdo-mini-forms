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
load_plugin_textdomain('tdomf','wp-content/plugins/tdomf');

// Debugging
//
tdomf_log_message('register_globals is '.ini_get('register_globals'));
if(ini_get('register_globals')){
   tdomf_log_message('register_globals equates to true => wp_unregister_GLOBALS will be called');
} else {
   tdomf_log_message('register_globals equates to false => wp_unregister_GLOBALS will not be called');
}

// Security Check
//
if(!isset($_SESSION['tdomf_key']) || $_SESSION['tdomf_key'] != $_POST['tdomf_key']) {
   if(!isset($_SESSION) || !isset($_SESSION['tdomf_key']) || trim($_SESSION['tdomf_key']) == "") {
     tdomf_log_message('Key is missing from $_SESSION: contents of $_SESSION:<pre>'.var_export($_SESSION,true)."</pre>",TDOMF_LOG_BAD);
   }
   $session_key = $_SESSION['tdomf_key'];
   $post_key = $_POST['tdomf_key'];
   $ip = $_SERVER['REMOTE_ADDR'];
   tdomf_log_message("Form submitted with bad key (session = $session_key, post = $post_key) from $ip !",TDOMF_LOG_BAD);
   unset($_SESSION['tdomf_key']);
   exit(__("TDOMF: Bad data submitted. Please return to the previous page and reload it. Then try submitting your post again.","tdomf"));
}
unset($_SESSION['tdomf_key']);

// Double check user permissions
//
$message = tdomf_check_permissions_form();

// Now either generate a preview or create a post
//
$save_post_info = FALSE;
if($message == NULL) {
  if(isset($_POST['tdomf_form1_send'])) {

    tdomf_log_message("Someone is attempting to submit something");

    $message = tdomf_validate_form($_POST);
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
      }
    } else {
      $save_post_info = TRUE;
    }
  } else if(isset($_POST['tdomf_form1_preview'])) {

    // For preview, remove magic quote slashes!
    if (get_magic_quotes_gpc()) {
      #tdomf_log_message("Magic quotes is enabled. Stripping slashes for preview...");
      function stripslashes_array($array) {
          return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
      }
      $_COOKIE = stripslashes_array($_COOKIE);
      #$_FILES = stripslashes_array($_FILES);
      #$_GET = stripslashes_array($_GET);
      $_POST = stripslashes_array($_POST);
      $_REQUEST = stripslashes_array($_REQUEST);
    }

       $save_post_info = TRUE;
	   $message = tdomf_validate_form($_POST);
	   if($message == NULL) {
		  $message = tdomf_preview_form($_POST);
	   }
	}
}

// Go back to form with args
//
$redirect_url = $_POST['redirect'];
if($save_post_info) {
	$args = $_POST;
} else {
	$args = array();
	$args['tdomf_no_form'] = true;
}
$args['tdomf_post_message'] = $message;
$_SESSION['tdomf_form_post'] = $args;
//
header("Location: $redirect_url");
exit;
?>