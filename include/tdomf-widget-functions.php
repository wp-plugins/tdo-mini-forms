<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

////////////////////
// Manage Widgets //
////////////////////

// Load widgets from widget directory
//
function tdomf_load_widgets() {
   if(file_exists(TDOMF_WIDGET_PATH)) {
       #tdomf_log_message("Looking in ".TDOMF_WIDGET_PATH." for widgets...");
	   if ($handle = opendir(TDOMF_WIDGET_PATH)) {
		  while (false !== ($file = readdir($handle))) {
		     if(preg_match('/.*\\.php$/',$file)) {
			 	#tdomf_log_message("Loading widget $file...");
			 	require_once(TDOMF_WIDGET_PATH.$file);
			 }
		  }
	   } else {
		  tdomf_log_message("Could not open directory ".TDOMF_WIDGET_PATH."!",TDOMF_LOG_ERROR);
	   }
   } else {
      tdomf_log_message("Could not find ".TDOMF_WIDGET_PATH."!",TDOMF_LOG_ERROR);
   }
}

// Return the widget order
//
function tdomf_get_widget_order() {
  $widget_order = get_option(TDOMF_OPTION_FORM_ORDER);
  if($widget_order == false) {
  	return tdomf_get_form_widget_default_order();
  }
  return $widget_order;
}

// Is a preview avaliable
// Currently selected widgets must provide a preview and preview must be enabled.
//
function tdomf_widget_is_preview_avaliable() {
   global $tdomf_form_widgets_preview;
   if(!get_option(TDOMF_OPTION_PREVIEW)) {
   	  return false;
   }
   $widget_order = tdomf_get_widget_order();
   foreach($widget_order as $id) {
      if(isset($tdomf_form_widgets_preview[$id])) {
         return true;
      }
   }
   return false;
}

// AJAX allowed (Not currently supported)
//
function tdomf_widget_is_ajax_avaliable() {
   global $tdomf_form_widgets_preview, $tdomf_form_widgets_validate, $tdomf_form_widgets_post;
   if(!get_option(TDOMF_OPTION_AJAX)) {
   	  return false;
   }
   $widget_order = get_option(TDOMF_OPTION_FORM_ORDER);
   foreach($widget_order as $id) {
      if(get_option(TDOMF_OPTION_PREVIEW)
      		&& isset($tdomf_form_widgets_preview[$id])
      		&& !$tdomf_form_widgets_preview[$id]["ajax"]) {
       	return false;
      }
	  if(isset($tdomf_form_widgets_validate[$id]) && !$tdomf_form_widgets_validate[$id]["ajax"]) {
       	return false;
      }
	  if(isset($tdomf_form_widgets_post[$id]) && !$tdomf_form_widgets_post[$id]["ajax"]) {
       	return false;
      }
   }
   return true;
}

// All Widgets are in this array
//
$tdomf_form_widgets = array();
//
// Configuration panels for Widgets
//
$tdomf_form_widgets_control = array();
//
// Preview post for Widget
//
$tdomf_form_widgets_preview = array();
//
// Form validation for Widgets
//
$tdomf_form_widgets_validate = array();
//
// Post actions for Widgets
//
$tdomf_form_widgets_post = array();

// All Widgets need to register with this function
//
function tdomf_register_form_widget($name, $callback) {
   global $tdomf_form_widgets,$tdomf_form_widgets;
   $id = sanitize_title($name);
   if(isset($tdomf_form_widgets[$id])) {
      tdomf_log_message("tdomf_register_form_widget: Widget $id already exists. Overwriting...");
   }
   #tdomf_log_message("Loading Widget $id...");
   $tdomf_form_widgets[$id]['name'] = $name;
   $tdomf_form_widgets[$id]['cb'] = $callback;
}

// Widgets that require configuration must register with this function
//
function tdomf_register_form_widget_control($name, $control_callback, $width = 360, $height = 130) {
   global $tdomf_form_widgets_control,$tdomf_form_widgets;
   $id = sanitize_title($name);
   if(!isset($tdomf_form_widgets[$id])) {
   		 tdomf_log_message("Control: Widget $id has not be registered!...",TDOMF_LOG_ERROR);
   		 return;
   }
   if(isset($tdomf_form_widgets_control[$id])) {
         tdomf_log_message("tdomf_register_form_widget_control: Widget $id already exists. Overwriting...");
   }
   #tdomf_log_message("Loading Widget Control $id...");
   $tdomf_form_widgets_control[$id]['name'] = $name;
   $tdomf_form_widgets_control[$id]['cb'] = $control_callback;
   $tdomf_form_widgets_control[$id]['width'] = $width;
   $tdomf_form_widgets_control[$id]['height'] = $height;
}

// Widgets that provide a preview must register with this function
//
function tdomf_register_form_widget_preview($name, $preview_callback, $ajax = true) {
   global $tdomf_form_widgets_preview,$tdomf_form_widgets;
   $id = sanitize_title($name);
	if(!isset($tdomf_form_widgets[$id])) {
   		 tdomf_log_message("Preview: Widget $id has not be registered!...",TDOMF_LOG_ERROR);
   		 return;
   }
   if(isset($tdomf_form_widgets_preview[$id])) {
      tdomf_log_message("Preview widget $id already exists. Overwriting...");
   }
   #tdomf_log_message("Loading Widget Preview $id...");
   $tdomf_form_widgets_preview[$id]['name'] = $name;
   $tdomf_form_widgets_preview[$id]['cb'] = $preview_callback;
   $tdomf_form_widgets_preview[$id]['ajax'] = $ajax;
}

// Widgets that vaidate input *before* input
//
function tdomf_register_form_widget_validate($name, $validate_callback, $ajax = true) {
   global $tdomf_form_widgets_validate,$tdomf_form_widgets;
   $id = sanitize_title($name);
	if(!isset($tdomf_form_widgets[$id])) {
   		 tdomf_log_message("Validate: Widget $id has not be registered!...",TDOMF_LOG_ERROR);
   		 return;
   }
   if(isset($tdomf_form_widgets_validate[$id])) {
      tdomf_log_message("Widget $id already exists. Overwriting...");
   }
   #tdomf_log_message("Loading Widget Validate $id...");
   $tdomf_form_widgets_validate[$id]['name'] = $name;
   $tdomf_form_widgets_validate[$id]['cb'] = $validate_callback;
   $tdomf_form_widgets_validate[$id]['ajax'] = $ajax;
}

// Widgets that modify the post *after* submission 
//
function tdomf_register_form_widget_post($name, $post_callback, $ajax = true) {
   global $tdomf_form_widgets_post,$tdomf_form_widgets;
   $id = sanitize_title($name);
	if(!isset($tdomf_form_widgets[$id])) {
   		 tdomf_log_message("Post: Widget $id has not be registered!...",TDOMF_LOG_ERROR);
   		 return;
   }
   if(isset($tdomf_form_widgets_post[$id])) {
      tdomf_log_message("tdomf_register_form_widget_post: Widget $id already exists. Overwriting...");
   }
   #tdomf_log_message("Loading Widget Post $id...");
   $tdomf_form_widgets_post[$id]['name'] = $name;
   $tdomf_form_widgets_post[$id]['cb'] = $post_callback;
   $tdomf_form_widgets_post[$id]['ajax'] = $ajax;
}

// Return the default widget order!
//
function tdomf_get_form_widget_default_order() {
   return array("who-am-i","content","notify-me");
}

////////////////////////////////////////////////////////////////////////////////
//                                Default Widgets: "Content" and "Who Am I"   //
////////////////////////////////////////////////////////////////////////////////

////////////////////
// Content Widget //
////////////////////

#TODO: QuickTags and/or FckEditor

/////////////////////////////////////////
// Default options for the content widget 
//
function tdomf_widget_content_get_options() {
  $options = get_option('tdomf_content_widget');
    if($options == false) {
       $options = array();
       $options['title'] = "";
       $options['title-enable'] = true;
       $options['title-required'] = false;
       $options['title-size'] = 30;
       $options['text-enable'] = true;
       $options['text-required'] = true;
       $options['text-cols'] = 40;
       $options['text-rows'] = 10; 
       $options['restrict-tags'] = false;
       $options['allowable-tags'] = "<p><b><i><u><strong><a><img><table><tr><td><blockquote><ul><ol><li><br>";
    }
  return $options;
}

//////////////////////////////
// Display the content widget! 
//
function tdomf_widget_content($args) {
  extract($args);
  $options = tdomf_widget_content_get_options();
  if(!$options['title-enable'] && !$options['text-enable']) { return ""; }
  $output = $before_widget;
  if($options['title'] != "") {
    $output .= $before_title.$options['title'].$after_title;
  }
  if($options['title-enable']) {
    if($options['title-required']) {
      $output .= '<label for="content_title" class="required">'.__("Post Title (Required): ","tdomf")."<br/>\n";
    } else {
      $output .= '<label for="content_title">'.__("Post Title: ","tdomf")."<br/>\n";
    }
    $output .= '<input type="text" name="content_title" id="content_title" size="'.$options['title-size'].'" value="'.$content_title.'" />';
    $output .= "</label>\n";
    if($options['text-enable']) {
      $output .= "<br/><br/>";
    }
  }
  if($options['text-enable']) {
    if($options['text-required']) {
      $output .= '<label for="content_content" class="required">'.__("Post Text (Required): ","tdomf")."<br/>\n";      
    } else {
      $output .= '<label for="content_content">'.__("Post Text: ","tdomf")."<br/>\n";
    }
    $output .= "</label>\n";    
    if($options['allowable-tags'] != "" && $options['restrict-tags']) {
      $output .= sprintf(__("<small>Allowable Tags: %s</small>","tdomf"),htmlentities($options['allowable-tags']))."<br/>";
    }
    $output .= '<textarea title="true" rows="'.$options['text-rows'].'" cols="'.$options['text-cols'].'" name="content_content" id="content_content" >'.$content_content.'</textarea>';
  }
  $output .= $after_widget;
  return $output;
}
tdomf_register_form_widget('Content', 'tdomf_widget_content');

///////////////////////////////////////
// Preview the post's content and title
//
function tdomf_widget_content_preview($args) {
  extract($args);
  $options = tdomf_widget_content_get_options();
  if(!$options['title-enable'] && !$options['text-enable']) { return ""; }
  $output = $before_widget;
  if($options['title'] != "") {
    $output .= $before_title.$options['title'].$after_title;
  }
  if($options['title-enable']) {
    $output .= "<b>".__("Title: ","tdomf")."</b>";
    $output .= $content_title;
    $output .= "<br/>";
  }
  if($options['text-enable']) {
    $content_content = preg_replace('|\[tdomf_form1\]|', '', $content_content);
    $output .= "<b>".__("Text: ","tdomf")."</b><br/>";
    if(!get_option(TDOMF_OPTION_MODERATION)){
     // if moderation is enabled, we don't do kses filtering, might as well
     // give full picture to user!
     $content_content = wp_filter_post_kses($content_content);
    }
    if($options['allowable-tags'] != "" && $options['restrict-tags']) {
      $output .= apply_filters('the_content', strip_tags($content_content,$options['allowable-tags']));
    } else {
      $output .= apply_filters('the_content', $content_content);
    }
  }
  $output .= $after_widget;
  return $output;
}
tdomf_register_form_widget_preview('Content', 'tdomf_widget_content_preview');

///////////////////////////////////////
// Add the title and content to the post 
//
function tdomf_widget_content_post($args) {
  extract($args);
  $options = tdomf_widget_content_get_options();
  if($options['allowable-tags'] != "" && $options['restrict-tags']) {
    tdomf_log_message("Content Widget: Stripping tags from post!");
    $post_content = strip_tags($content_content,$options['allowable-tags']);
  } else {
    $post_content = $content_content;
  }
  
  $post = array (
      "ID"                      => $post_ID,
      "post_content"            => $post_content,
      "post_title"              => $content_title,
  );
  $post_ID = wp_update_post($post);
}
tdomf_register_form_widget_post('Content', 'tdomf_widget_content_post');

///////////////////////////////////////////////////
// Display and handle content widget control panel 
//
function tdomf_widget_content_control() {
  $options = tdomf_widget_content_get_options();
  // Store settings for this widget
    if ( $_POST['content-submit'] ) {
     $newoptions['title'] = strip_tags(stripslashes($_POST['content-title']));
     $newoptions['title-enable'] = isset($_POST['content-title-enable']);
     $newoptions['title-required'] = isset($_POST['content-title-required']);
     $newoptions['title-size'] = intval($_POST['content-title-size']); 
     $newoptions['text-enable'] = isset($_POST['content-text-enable']);
     $newoptions['text-required'] = isset($_POST['content-text-required']);
     $newoptions['text-cols'] = intval($_POST['content-text-cols']);
     $newoptions['text-rows'] = intval($_POST['content-text-rows']); 
     $newoptions['restrict-tags'] = isset($_POST['content-restrict-tags']);
     $newoptions['allowable-tags'] = $_POST['content-allowable-tags'];
     if ( $options != $newoptions ) {
        $options = $newoptions;
        update_option('tdomf_content_widget', $options);
        
     }
  }

   // Display control panel for this widget
  
  extract($options);

        ?>
<div>
<label for="content-title" style="line-height:35px;display:block;"><?php _e("Title: ","tdomf"); ?><input type="text" id="content-title" name="content-title" value="<?php echo $options['title']; ?>" /></label>

<h4><?php _e("Title of Post","tdomf"); ?></h4>
<label for="content-title-enable" style="line-height:35px;"><?php _e("Show","tdomf"); ?> <input type="checkbox" name="content-title-enable" id="content-title-enable" <?php if($options['title-enable']) echo "checked"; ?> ></label>
<label for="content-title-required" style="line-height:35px;"><?php _e("Required","tdomf"); ?> <input type="checkbox" name="content-title-required" id="content-title-required" <?php if($options['title-required']) echo "checked"; ?> ></label>
<label for="content-title-size" style="line-height:35px;"><?php _e("Size","tdomf"); ?> <input type="textfield" name="content-title-size" id="content-title-size" value="<?php echo $options['title-size']; ?>" size="3" /></label>

<h4><?php _e("Content of Post","tdomf"); ?></h4>
<label for="content-text-enable" style="line-height:35px;"><?php _e("Show","tdomf"); ?> <input type="checkbox" name="content-text-enable" id="content-text-enable" <?php if($options['text-enable']) echo "checked"; ?> ></label>
<label for="content-text-required" style="line-height:35px;"><?php _e("Required","tdomf"); ?> <input type="checkbox" name="content-text-required" id="content-text-required" <?php if($options['text-required']) echo "checked"; ?> ></label>
<br/>
<label for="content-text-cols" style="line-height:35px;"><?php _e("Cols","tdomf"); ?> <input type="textfield" name="content-text-cols" id="content-text-cols" value="<?php echo $options['text-cols']; ?>" size="3" /></label>
<label for="content-text-rows" style="line-height:35px;"><?php _e("Rows","tdomf"); ?> <input type="textfield" name="content-text-rows" id="content-text-rows" value="<?php echo $options['text-rows']; ?>" size="3" /></label>
<br/>
<label for="content-restrict-tags" style="line-height:35px;"><?php _e("Restrict Tags","tdomf"); ?> <input type="checkbox" name="content-restrict-tags" id="content-restrict-tags" <?php if($options['restrict-tags']) echo "checked"; ?> ></label>
<br/>
<label for="content-allowable-tags" style="line-height:35px;"><?php _e("Allowable Tags","tdomf"); ?> <textarea title="true" cols="30" name="content-allowable-tags" id="content-allowable-tags" ><?php echo $options['allowable-tags']; ?></textarea></label>
</div>
        <?php 
}
tdomf_register_form_widget_control('Content', 'tdomf_widget_content_control', 300, 430);

///////////////////////////////////////
// Validate title and content from form 
//
function tdomf_widget_content_validate($args) {
  $options = tdomf_widget_content_get_options();
  if(!$options['title-enable'] && !$options['text-enable']) { return ""; }  
  extract($args);
  $output = "";
  if($options['title-enable'] && $options['title-required']
       && (empty($content_title) || trim($content_title) == "")) {
      if($output != "") { $output .= "<br/>"; }
      $output .= __("You must specify a post title.","tdomf");
  }
  if($options['text-enable'] && $options['text-required']
       && (empty($content_content) || trim($content_content) == "")) {
      if($output != "") { $output .= "<br/>"; }
      $output .= __("You must specify some post text.","tdomf");
  }
  // return output if any
  if($output != "") {
    return $before_widget.$output.$after_widget;
  } else {
    return NULL;
  }
}
tdomf_register_form_widget_validate('Content', 'tdomf_widget_content_validate');

///////////////////////
// "Who Am I" Widget //
///////////////////////

// Simple regex check to validate a URL
//
function tdomf_check_url($url) 
{ 
  return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url); 
} 

// Store user submitted defaults as cookies
//
function tdomf_widget_whoami_store_cookies($name = "", $email = "", $web = "") {
  setcookie("tdomf_whoami_widget_name",$name, time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
  setcookie("tdomf_whoami_widget_email",$email, time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
  setcookie("tdomf_whoami_widget_web",$web, time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
}

// Grab the options for the whoami widget. If none avaliable, generate default
//
function tdomf_widget_whoami_get_options() {
  $options = get_option('tdomf_whoami_widget');
    if($options == false) {
       $options = array();
       $options['title'] = "";
       $options['name-enable'] = true;
       $options['name-required'] = true;
       $options['email-enable'] = true;
       $options['email-required'] = true;
       $options['webpage-enable'] = true;
       $options['webpage-required'] = true;
    }
  return $options;
}

/////////////////////////////////
// Display whoami widget in form! 
//
function tdomf_widget_whoami($args) {
  global $current_user;
  
  get_currentuserinfo();
  
  extract($args);
  $options = tdomf_widget_whoami_get_options();

  $output = $before_widget;

  // Check if values set in cookie
  if(!isset($whoami_name) && isset($_COOKIE['tdomf_whoami_widget_name'])) {
    $whoami_name = $_COOKIE['tdomf_whoami_widget_name'];
  }
  
  if(!isset($whoami_email) && isset($_COOKIE['tdomf_whoami_widget_email'])) {
    $whoami_email = $_COOKIE['tdomf_whoami_widget_email'];
  }
  
  if(!isset($whoami_webpage) && isset($_COOKIE['tdomf_whoami_widget_web'])) {
    $whoami_webpage = $_COOKIE['tdomf_whoami_widget_web'];
  }
  
  if(isset($options['title']) && !empty($options['title'])) {
     $output .= $before_title . $options['title'] . $after_title;
  }

  // default webpage value
  if(!isset($whoami_webpage) || empty($whoami_webpage)){ $whoami_webpage = "http://"; }
  
  // If user is logged in, nothing much more to do here!
  if(is_user_logged_in()) {
     $output .= "<p>".sprintf(__("You are currently logged in as <a href=\"%s\">%s</a>.","tdomf"),get_bloginfo('wpurl').'/wp-admin',$current_user->display_name);
     if(current_user_can('manage_options')) {
        $output .= " <a href='".get_option('siteurl')."/wp-admin/admin.php?page=".TDOMF_FOLDER."'>".__("You can configure this form &raquo;","tdomf")."</a>";
     }
     $output .= "</p>";
  } else {
    $our_uri = $_SERVER['REQUEST_URI'];
    $login_uri = get_bloginfo('wpurl').'/wp-login.php?redirect_to='.$our_uri;
    $reg_uri = get_bloginfo('wpurl').'/wp-register.php?redirect_to='.$our_uri;

    $output .= "<p>".sprintf(__("We do not know who you are. Please supply your name and email address. Alternatively you can <a href=\"%s\">log in</a> if you have a user account or <a href=\"%s\">register</a> for a user account if you do not have one.","tdomf"),$login_uri,$reg_uri)."</p>";

    if($options['name-enable']) {
     $output .=  "<label for='whoami_name'";
     if($options['name-required']) {
        $output .= ' class="required" ';
     }
     $output .= ">".__("Name:","tdomf").' <br/><input type="text" value="'.$whoami_name.'" name="whoami_name" id="whoami_name" />';
     if($options['name-required']) {
        $output .= __(" (Required)","tdomf");
     }
     $output .= "</label>";
     $output .= "<br/><br/>\n";
    }

    if($options['email-enable']) {
         $output .=    "<label for='whoami_email'";
     if($options['email-required']) {
        $output .= ' class="required" ';
     }
     $output .= ">".__("Email:","tdomf").'<br/><input type="text" value="'.$whoami_email.'" name="whoami_email" id="whoami_email" />';
         if($options['email-required']) {
            $output .= __(" (Required)","tdomf");
         }
         $output .= "</label>";
         $output .= "<br/><br/>\n";
      }

   if($options['webpage-enable']) {
         $output .=    "<label for='whoami_webpage'";
     if($options['webpage-required']) {
        $output .= ' class="required" ';
     }
     $output .= ">".__("Webpage:","tdomf").'<br/><input type="text" value="'.$whoami_webpage.'" name="whoami_webpage" id="whoami_webpage" />';
         if($options['webpage-required']) {
            $output .= __(" (Required)","tdomf");
         }
         $output .= "</label>";         
         $output .= "<br/><br/>\n";
      }
  }
  $output .= $after_widget;
  return $output;
}
tdomf_register_form_widget('Who Am I', 'tdomf_widget_whoami');

//////////////////////////////////////////
// Display and handle widget control panel 
//
function tdomf_widget_whoami_control() {
  $options = tdomf_widget_whoami_get_options();
  
  // Store settings for this widget
    if ( $_POST['who-am-i-submit'] ) {
     $newoptions['title'] = strip_tags(stripslashes($_POST['who_am_i-title']));
     $newoptions['name-enable'] = isset($_POST['who_am_i-name-enable']);;
     $newoptions['name-required'] = isset($_POST['who_am_i-name-required']);
     $newoptions['email-enable'] = isset($_POST['who_am_i-email-enable']);
     $newoptions['email-required'] = isset($_POST['who_am_i-email-required']);
     $newoptions['webpage-enable'] = isset($_POST['who_am_i-webpage-enable']);
     $newoptions['webpage-required'] = isset($_POST['who_am_i-webpage-required']);
     if ( $options != $newoptions ) {
        $options = $newoptions;
        update_option('tdomf_whoami_widget', $options);
     }
  }
  
  // Display control panel for this widget
  
  extract($options);

        ?>
<div>
<label for="who_am_i-title" style="line-height:35px;display:block;"><?php _e("Title: ","tdomf"); ?><input type="text" id="who_am_i-title" name="who_am_i-title" value="<?php echo $options['title']; ?>" /></label>

<h4><?php _e("Submitter Name","tdomf"); ?></h4>
<label for="who_am_i-name-enable" style="line-height:35px;"><?php _e("Show","tdomf"); ?> <input type="checkbox" name="who_am_i-name-enable" id="who_am_i-name-enable" <?php if($options['name-enable']) echo "checked"; ?> ></label>
<label for="who_am_i-name-required" style="line-height:35px;"><?php _e("Required","tdomf"); ?> <input type="checkbox" name="who_am_i-name-required" id="who_am_i-name-required" <?php if($options['name-required']) echo "checked"; ?> ></label>

<h4><?php _e("Submitter Webpage","tdomf"); ?></h4>
<label for="who_am_i-webpage-enable" style="line-height:35px;"><?php _e("Show","tdomf"); ?> <input type="checkbox" name="who_am_i-webpage-enable" id="who_am_i-webpage-enable" <?php if($options['webpage-enable']) echo "checked"; ?> ></label>
<label for="who_am_i-webpage-required" style="line-height:35px;"><?php _e("Required","tdomf"); ?> <input type="checkbox" name="who_am_i-webpage-required" id="who_am_i-webpage-required" <?php if($options['webpage-required']) echo "checked"; ?> ></label>

<h4><?php _e("Submitter Email","tdomf"); ?></h4>
<label for="who_am_i-email-enable" style="line-height:35px;"><?php _e("Show","tdomf"); ?> <input type="checkbox" name="who_am_i-email-enable" id="who_am_i-email-enable" <?php if($options['email-enable']) echo "checked"; ?> ></label>
<label for="who_am_i-email-required" style="line-height:35px;"><?php _e("Required","tdomf"); ?> <input type="checkbox" name="who_am_i-email-required" id="who_am_i-email-required" <?php if($options['email-required']) echo "checked"; ?> ></label>
</div>
        <?php
}
tdomf_register_form_widget_control('Who Am I', 'tdomf_widget_whoami_control', 200, 380);

///////////////////////////////////////
// Generate a simple preview for widget
//
function tdomf_widget_whoami_preview($args) {
extract($args);
  global $current_user;
  get_currentuserinfo();
  if(is_user_logged_in()) {
    return $before_widget.sprintf(__("Submitted by %s.","tdomf"),$current_user->display_name).$after_widget;
  } else {
    $link = "";
    if(isset($args['whoami_webpage'])){
          $link .= "<a href=\"".$args['whoami_webpage']."\">";
    }
    if(isset($args['whoami_name'])){
          $link .= $args['whoami_name'];
    } else {
          $link .= __("unknown","tdomf");
    }
    if(isset($args['whoami_webpage'])){
          $link .= "</a>";
    }
    return $before_widget.sprintf(__("Submitted by %s.","tdomf"),$link).$after_widget;
  }
}
tdomf_register_form_widget_preview('Who Am I', 'tdomf_widget_whoami_preview');

//////////////////////////////////
// Validate input for this widget
//
function tdomf_widget_whoami_validate($args) {
  // if user logged in, no validation required
  if(is_user_logged_in()){
    return NULL;
  }
  // do validation
  extract($args);
  $output = "";
  $options = tdomf_widget_whoami_get_options();
  if($options['name-enable'] && $options['name-required']
       && (empty($whoami_name) || trim($whoami_name) == "")) {
      $output .= __("You must specify a name.","tdomf");
  }
  if($options['email-enable'] && $options['email-required']
       && (empty($whoami_email) || trim($whoami_email) == "")) {
      if($output != "") { $output .= "<br/>"; }
      $output .= __("You must specify a email address.","tdomf");
  }
  // if something entered for email, check it!
  else if((($options['email-enable'] && $options['email-required']) 
        || ($options['email-enable'] && trim($whoami_email) != "")) 
       && !tdomf_check_email_address($whoami_email)) {
      if($output != "") { $output .= "<br/>"; }
      $output .= __("Your email address does not look correct.","tdomf");
  }
  if($options['webpage-enable'] && $options['webpage-required']
       && (empty($whoami_webpage) || trim($whoami_webpage) == "")) {
      if($output != "") { $output .= "<br/>"; }
      $output .= __("You must specify a valid webpage.","tdomf");
  }
  // if something entered for URL, check it!
  else if((($options['webpage-enable'] && $options['webpage-required'])
  || ($options['webpage-enable'] && trim($whoami_webpage) != "http://" && trim($whoami_webpage) != ""))
  && !tdomf_check_url($whoami_webpage)) {
    if($output != "") { $output .= "<br/>"; }
    $output .= __("Your webpage URL does not look correct.<br/>","tdomf");
  }
  // return output if any
  if($output != "") {
    return $before_widget.$output.$after_widget;
  } else {
    return NULL;
  }
}
tdomf_register_form_widget_validate('Who Am I', 'tdomf_widget_whoami_validate');

///////////////////////////////////
// Update post after form submitted 
//
function tdomf_widget_whoami_post($args) {
  global $current_user;
  get_currentuserinfo();
  extract($args);
  if(isset($whoami_name)) {
    add_post_meta($post_ID, TDOMF_KEY_NAME, $whoami_name, true);
  }
  if(isset($whoami_webpage)) {
    add_post_meta($post_ID, TDOMF_KEY_WEB, $whoami_webpage, true);
  }
  if(isset($whoami_email)) {
    add_post_meta($post_ID, TDOMF_KEY_EMAIL, $whoami_email, true);
  }
  if(is_user_logged_in()) {
    if($current_user->ID != get_option(TDOMF_DEFAULT_AUTHOR)){
       add_post_meta($post_ID, TDOMF_KEY_USER_ID, $current_user->ID, true);
       add_post_meta($post_ID, TDOMF_KEY_USER_NAME, $current_user->user_login, true);
       add_post_meta($post_ID, TDOMF_KEY_NAME, $current_user->display_name, true);
       add_post_meta($post_ID, TDOMF_KEY_EMAIL, $user->user_email, true);
       add_post_meta($post_ID, TDOMF_KEY_WEB, $user->user_url, true);
       update_usermeta($current_user->ID, TDOMF_KEY_FLAG, true);
    }
  }
  tdomf_widget_whoami_store_cookies($whoami_name,$whoami_email,$whoami_webpage);
}
tdomf_register_form_widget_post('Who Am I', 'tdomf_widget_whoami_post');

?>
