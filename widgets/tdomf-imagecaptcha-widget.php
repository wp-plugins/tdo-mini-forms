<?php
/*
Name: "Image Captcha"
URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: The user must enter the text in the image otherwise the form will not be processed
Version: 0.1
Author: Mark Cunningham
Author URI: http://thedeadone.net
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

/////////////////////////////////
// Initilise multiple captchas!
//
function tdomf_widget_imagecaptcha_init(){
  if ( $_POST['tdomf-widget-imagecaptcha-number-submit'] ) {
    $count = $_POST['tdomf-widget-imagecaptcha-number'];
    if($count > 0){ update_option('tdomf_imagecaptcha_widget_count',$count); }
  }
  $count = get_option('tdomf_imagecaptcha_widget_count');

  
  for($i = 2; $i <= $count; $i++) {
    tdomf_register_form_widget("imagecaptcha-$i","Image Captcha $i", 'tdomf_widget_imagecaptcha',$i);
    tdomf_register_form_widget_validate("imagecaptcha-$i", "Image Captcha $i",'tdomf_widget_imagecaptcha_validate', true, $i);
  }
}
tdomf_widget_imagecaptcha_init();

//////////////////////////////
// Display the widget! 
//
function tdomf_widget_imagecaptcha($args,$params) {
  extract($args);

  if(!isset($args['imagecaptcha'])) {
    $_SESSION['freecap_attempts'] = 0;
    $_SESSION['freecap_word_hash'] = false;
  }

  $output  = $before_widget;

  $output .= <<< EOT
<script language="javascript">
<!--
function new_freecap()
{
	// loads new freeCap image
	if(document.getElementById)
	{
		// extract image name from image source (i.e. cut off ?randomness)
		thesrc = document.getElementById("freecap").src;
		thesrc = thesrc.substring(0,thesrc.lastIndexOf(".")+4);
		// add ?(random) to prevent browser/isp caching
		document.getElementById("freecap").src = thesrc+"?"+Math.round(Math.random()*100000);
	} else {
		alert("Sorry, cannot autoreload freeCap image\\nSubmit the form and a new freeCap will be loaded");
	}
}
//-->
</script>
EOT;
  
  $output .= "<img src='".TDOMF_WIDGET_URLPATH."freecap/freecap_tdomf.php' name='freecap' id='freecap' /><br/>";
  $output .= "<small>".sprintf(__("If you can't read the word in the image, <a href=\"%s\">click here</a>","tdomf"),'#" onClick="this.blur();new_freecap();return false;')."</small><br/>";
  $output .= '<label for="imagecaptcha" class="required" >';
  $output .= __('What is the word in the image? ','tdomf')."<br/>";
  $output .= '<input type="textfield" id="imagecaptcha" name="imagecaptcha" size="30" value="'.$args["imagecaptcha"].'" />';
  $output .= '</label>';
      
  $output .= $after_widget;
  return $output;
}

////////////////////////////////////////////////////////////////////////////
// Validate answer but using post so we don't have to validate it at preview!
//
function tdomf_widget_imagecaptcha_post($args,$params) {
  extract($args);
  
  // all freeCap words are lowercase.
	// font #4 looks uppercase, but trust me, it's not...
	if($_SESSION['hash_func'](strtolower($args["imagecaptcha"]))==$_SESSION['freecap_word_hash'])
	{
		// reset freeCap session vars
		// cannot stress enough how important it is to do this
		// defeats re-use of known image with spoofed session id
		$_SESSION['freecap_attempts'] = 0;
		$_SESSION['freecap_word_hash'] = false;
    
	} else {
		return $before_widget.__("You must enter the word in the image as you see it.","tdomf").$after_widget;
	}
  
  return NULL;
}

tdomf_register_form_widget("imagecaptcha","Image Captcha", 'tdomf_widget_imagecaptcha');
tdomf_register_form_widget_post("imagecaptcha", "Image Captcha",'tdomf_widget_imagecaptcha_post', true);

?>