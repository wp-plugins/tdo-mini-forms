<?php

// Disable revisions *before* everything else
//
define('WP_POST_REVISIONS', false);

//////////////////////////
// Process Form Request //
//////////////////////////

// Load up Wordpress
//
$wp_load = realpath("../../../wp-load.php");
if(!file_exists($wp_load)) {
  $wp_config = realpath("../../../wp-config.php");
  if (!file_exists($wp_config)) {
      exit("Can't find wp-config.php or wp-load.php");
  } else {
      require_once($wp_config);
  }
} else {
  require_once($wp_load);
}

global $wpdb, $tdomf_form_widgets_validate, $tdomf_form_widgets_preview;

// loading text domain for language translation
//
load_plugin_textdomain('tdomf',PLUGINDIR.DIRECTORY_SEPARATOR.TDOMF_FOLDER);

// Now using jquery to pre-seralise form output but must still support the old
// way of non-seralized (so people don't have to modify their hacked forms)
// - Note: "action" is still used in _POST
//
if(isset($_POST['tdomf_args'])) {
    parse_str($_POST['tdomf_args'],$tdomf_args);
} else {
    tdomf_log_message("AJAX: Using old argument method");
    $tdomf_args = $_POST;
}

// Form id
//
if(!isset($tdomf_args['tdomf_form_id'])) {
  tdomf_log_message("tdomf-form-ajax: No Form ID set!",TDOMF_LOG_BAD);
  die( "alert('".__("TDOMF: No Form id!","tdomf")."');" );
}
$form_id = intval($tdomf_args['tdomf_form_id']);
if(!tdomf_form_exists($form_id)){
  tdomf_log_message("tdomf-form-ajax: Bad form id %d!",TDOMF_LOG_BAD);
  die( "tdomfDisplayMessage$form_id('TDOMF: Bad Form Id','full');" );
}

function tdomf_ajax_exit($form_id, $message, $full = false, $preview = false) {
    global $form_id;
    #$message = str_replace("'","\\'",$message);
    #$message = str_replace("\n"," ",$message);
    $message = preg_replace('/\r\n|\n\r|\r/', '\n', str_replace('\'', '\\' . '\'', str_replace('\\', '\\\\', $message)));
    $message = str_replace("\n"," ",$message);
    #tdomf_log_message("sending '$message' via ajax...");
    #$message = htmlentities($message,ENT_COMPAT);
    if($full) {
        die( "tdomfDisplayMessage$form_id('$message','full');" );
    } else if ($preview) {
        die( "tdomfDisplayMessage$form_id('$message','preview');" );
    }  else {
        die( "tdomfDisplayMessage$form_id('$message','');" );
    }
}

// Get Form Data for verficiation check
//
$form_data = tdomf_get_form_data($form_id);

// Security Check
//
$tdomf_verify = get_option(TDOMF_OPTION_VERIFICATION_METHOD);
if($tdomf_verify == false || $tdomf_verify == 'default') {
  if(!isset($form_data['tdomf_key_'.$form_id]) || $form_data['tdomf_key_'.$form_id] != $tdomf_args['tdomf_key_'.$form_id]) {
     if(!isset($form_data) || !isset($form_data['tdomf_key_'.$form_id]) || trim($form_data['tdomf_key_'.$form_id]) == "") {
       tdomf_log_message('Key is missing from $form_data: contents of $form_data:<pre>'.var_export($form_data,true)."</pre>",TDOMF_LOG_BAD);
     }
     $session_key = $form_data['tdomf_key_'.$form_id];
     $post_key = $tdomf_args['tdomf_key_'.$form_id];
     $ip = $_SERVER['REMOTE_ADDR'];
     tdomf_log_message("Form ($form_id) submitted with bad key (session = $session_key, post = $post_key) from $ip !",TDOMF_LOG_BAD);
     unset($form_data['tdomf_key_'.$form_id]);
     tdomf_save_form_data($form_id,$form_data);
     tdomf_ajax_exit($form_id,__("<font color='red'>TDOMF: Bad data submitted. Please reload the page and try submitting your post again.</font>","tdomf"),true);
  }
  unset($form_data['tdomf_key_'.$form_id]);
} else if($tdomf_verify == 'wordpress_nonce') {
  if(!wp_verify_nonce($tdomf_args['tdomf_key_'.$form_id],'tdomf-form-'.$form_id)) {
    $post_key = $tdomf_args['tdomf_key_'.$form_id];
    $ip = $_SERVER['REMOTE_ADDR'];    
    tdomf_log_message("Form ($form_id) submitted with bad nonce key (post = $post_key) from $ip !",TDOMF_LOG_BAD);
    tdomf_ajax_exit($form_id,__("<font color='red'>TDOMF: Bad data submitted. Please reload the page and try submitting your post again.</font>","tdomf"));
  }
}

function tdomf_fixslashesargs() {
    #if (get_magic_quotes_gpc()) {
      tdomf_log_message_extra("Magic quotes is enabled. Stripping slashes!");
      if(!function_exists('stripslashes_array')) {
        function stripslashes_array($array) {
            return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
        }
      }
      $_COOKIE = stripslashes_array($_COOKIE);
      #$_FILES = stripslashes_array($_FILES);
      #$_GET = stripslashes_array($_GET);
      $tdomf_args = stripslashes_array($tdomf_args);
      $_REQUEST = stripslashes_array($_REQUEST);
    #}
}

// Double check user permissions
//
$message = tdomf_check_permissions_form($form_id);
if($message != NULL) {
    tdomf_ajax_exit($form_id,$message,true);
}

if(!isset($_POST['tdomf_action'])) {
    tdomf_ajax_exit($form_id,__("TDOMF (AJAX) ERROR: no action set!","tdomf"),true);
}

// Now either generate a preview or create a post
//
if($_POST['tdomf_action'] == "post") {
    tdomf_log_message("Someone is attempting to submit something");
    $message = tdomf_validate_form($tdomf_args,false);
    if($message == NULL) {
      $args = $tdomf_args;
      $args['ip'] = $_SERVER['REMOTE_ADDR'];
      $retVal = tdomf_create_post($args);
      // If retVal is an int it's a post id
      if(is_int($retVal)) {
        $post_id = $retVal;
        if(get_post_status($post_id) == 'publish') {
            if(tdomf_get_option_form(TDOMF_OPTION_REDIRECT,$form_id)) {
                die( "tdomfRedirect$form_id('".get_permalink($post_id)."');" );
            } else {
                tdomf_ajax_exit($form_id,tdomf_get_message_instance(TDOMF_OPTION_MSG_SUB_PUBLISH,$form_id,false,$post_id),true);
            }
        } else if(get_post_status($post_id) == 'future') {
          tdomf_ajax_exit($form_id,tdomf_get_message_instance(TDOMF_OPTION_MSG_SUB_FUTURE,$form_id,false,$post_id),true);
        } else if(get_post_meta($post_id, TDOMF_KEY_SPAM)) { 
          tdomf_ajax_exit($form_id,tdomf_get_message_instance(TDOMF_OPTION_MSG_SUB_SPAM,$form_id),true);
        } else {
          tdomf_ajax_exit($form_id,tdomf_get_message_instance(TDOMF_OPTION_MSG_SUB_MOD,$form_id,false,$post_id),true);
        }
      // If retVal is a string, something went wrong!
      } else {
        tdomf_ajax_exit($form_id,tdomf_get_message_instance(TDOMF_OPTION_MSG_SUB_ERROR,$form_id,false,false,$retVal));
      }
    } else {
        tdomf_ajax_exit($form_id,tdomf_get_message_instance(TDOMF_OPTION_MSG_SUB_ERROR,$form_id,false,false,$message));    
    }
} else if($_POST['tdomf_action'] == "preview") {
   // For preview, remove magic quote slashes!
   tdomf_fixslashesargs();
   $message = tdomf_validate_form($tdomf_args,true);
   if($message == NULL) {
      $message = tdomf_preview_form($tdomf_args);
      tdomf_ajax_exit($form_id,$message,false,true);
   } else {
       tdomf_ajax_exit($form_id,sprintf(__("Your submission contained errors:<br/><br/>%s<br/><br/>Please correct and resubmit.","tdomf"),$message));
   }
} else {
    tdomf_ajax_exit($form_id,sprintf(__("TDOMF (AJAX) ERROR: unrecognised action %s!","tdomf"),$_POST['action']),true);
}

tdomf_ajax_exit($form_id,__("ERROR! Should never reach here.","tdomf"),true);
?>