<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

////////////////////
// Manage Widgets //
////////////////////

// TODO: default widgets should be included directly
// TODO: notifyme can check if whoami widget is installed...

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

// Preview avaliable
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

// AJAX allowed
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

?>
