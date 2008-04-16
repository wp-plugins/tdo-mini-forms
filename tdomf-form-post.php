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

// Get Form Data for verficiation check
//
$form_data = tdomf_get_form_data($form_id);

// Security Check
//
$tdomf_verify = get_option(TDOMF_OPTION_VERIFICATION_METHOD);
if($tdomf_verify == false || $tdomf_verify == 'default') {
  if(!isset($form_data['tdomf_key_'.$form_id]) || $form_data['tdomf_key_'.$form_id] != $_POST['tdomf_key_'.$form_id]) {
     if(!isset($form_data) || !isset($form_data['tdomf_key_'.$form_id]) || trim($form_data['tdomf_key_'.$form_id]) == "") {
       tdomf_log_message('Key is missing from $form_data: contents of $form_data:<pre>'.var_export($form_data,true)."</pre>",TDOMF_LOG_BAD);
     }
     $session_key = $form_data['tdomf_key_'.$form_id];
     $post_key = $_POST['tdomf_key_'.$form_id];
     $ip = $_SERVER['REMOTE_ADDR'];
     tdomf_log_message("Form ($form_id) submitted with bad key (session = $session_key, post = $post_key) from $ip !",TDOMF_LOG_BAD);
     unset($form_data['tdomf_key_'.$form_id]);
     tdomf_save_form_data($form_id,$form_data);
     exit(__("TDOMF: Bad data submitted. Please return to the previous page and reload it. Then try submitting your post again.","tdomf"));
  }
  unset($form_data['tdomf_key_'.$form_id]);
} else if($tdomf_verify == 'wordpress_nonce') {
  if(!wp_verify_nonce($_POST['tdomf_key_'.$form_id],'tdomf-form-'.$form_id)) {
    $post_key = $_POST['tdomf_key_'.$form_id];
    $ip = $_SERVER['REMOTE_ADDR'];    
    tdomf_log_message("Form ($form_id) submitted with bad nonce key (post = $post_key) from $ip !",TDOMF_LOG_BAD);
    exit(__("TDOMF: Bad data submitted. Please return to the previous page and reload it. Then try submitting your post again.","tdomf"));
  }
}

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
$hide_form = true;
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
        $hide_form = FALSE;
        tdomf_fixslashesargs();
      }
    } else {
      $save_post_info = TRUE;
      $hide_form = false;
      tdomf_fixslashesargs();
    }
  } else if(isset($_POST['tdomf_form'.$form_id.'_preview'])) {

    // For preview, remove magic quote slashes!
    tdomf_fixslashesargs();

       $save_post_info = TRUE;
       $hide_form = false;
	   $message = tdomf_validate_form($_POST,true);
	   if($message == NULL) {
		  $message = tdomf_preview_form($_POST);
	   }
	} else if(isset($_POST['tdomf_form'.$form_id.'_clear'])) {
    $message = NULL;
    $save_post_info = false;
    $hide_form = false;
  }
}

// update form data *after* widgets have done their work!
//
$form_data = tdomf_get_form_data($form_id);

if(!isset($post_id) || get_post_status($post_id) != 'publish') {
  // Go back to form with args
  //
  $redirect_url = $_POST['redirect'];

  if($save_post_info) {
    $args = $_POST;
  } else {
    $args = array();
  }
  if($hide_form) {
     $args['tdomf_no_form_'.$form_id] = true;
  }
  $args['tdomf_post_message_'.$form_id] = $message;
  $form_data['tdomf_form_post_'.$form_id] = $args;
} else {
  unset($form_data['tdomf_form_post_'.$form_id]);
  $redirect_url = get_permalink($post_id);
}

// save it!
//
tdomf_save_form_data($form_id,$form_data);

header("Location: $redirect_url");
exit;
?>