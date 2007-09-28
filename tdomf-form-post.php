<?php

//////////////////////////
// Process Form Request //
//////////////////////////

// Load up Wordpress
//
$wp_config = realpath("../../../wp-config.php");
if (!file_exists($wp_config)) {
   exit("Can't find wp-config.php");
}
require_once($wp_config);

global $wpdb, $tdomf_form_widgets_validate, $tdomf_form_widgets_preview;

if (!$_SESSION) session_start();

// Security Check
//
if(!isset($_SESSION['tdomf_key']) || $_SESSION['tdomf_key'] != $_POST['tdomf_key']) {
   tdomf_log_message("Form submitted with bad key from ".$_SERVER['REMOTE_ADDR']." !",TDOMF_LOG_BAD);
   unset($_SESSION['tdomf_key']);
   exit("TDOMF: Bad data submitted");
}
unset($_SESSION['tdomf_key']);

// loading text domain for language translation
//
load_plugin_textdomain('tdomf','wp-content/plugins/tdomf');

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
		$post_id = tdomf_create_post($args);
		if(get_post_status($post_id) == 'publish') {
		   $message = sprintf(__("Your submission has been automatically published. You can see it <a href='%s'>here</a>. Thank you for using this service.","tdomf"),get_permalink($post_id));
		} else {
		   $message = sprintf(__("Your post submission has been added to the moderation queue. It should appear in the next few days. If it doesn't please contact the <a href='mailto:%s'>admins</a>. Thank you for using this service.","tdomf"),get_bloginfo('admin_email'));
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