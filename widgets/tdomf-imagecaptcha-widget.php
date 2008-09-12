<?php
/*
Name: "Image Captcha"
URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: The user must enter the text in the image otherwise the form will not be processed
Version: 3
Author: Mark Cunningham
Author URI: http://thedeadone.net
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

function tdomf_widget_imagecaptcha_handle_number($form_id) {
  if(tdomf_form_exists($form_id)) {   
   if ( isset($_POST['tdomf-widget-imagecaptcha-number-submit']) ) {
      $count = $_POST['tdomf-widget-imagecaptcha-number'];
      if($count > 0){ tdomf_set_option_widget('tdomf_imagecaptcha_widget_count',$count,$form_id); }
   }
  }
}
#add_action('tdomf_widget_page_top','tdomf_widget_imagecaptcha_handle_number');
add_action('tdomf_control_form_start','tdomf_widget_imagecaptcha_handle_number');

/////////////////////////////////
// Initilise multiple captchas!
//
function tdomf_widget_imagecaptcha_init($form_id){
  if(tdomf_form_exists($form_id)) {   
     $count = tdomf_get_option_widget('tdomf_imagecaptcha_widget_count',$form_id);
     $max = tdomf_get_option_form(TDOMF_OPTION_WIDGET_INSTANCES,$form_id);
     if($max <= 1){ $count = 1; }
     else if($count > ($max+1)){ $count = $max + 1; }
     for($i = 2; $i <= $count; $i++) {
       tdomf_register_form_widget("imagecaptcha-$i","Image Captcha $i", 'tdomf_widget_imagecaptcha',array(), $i);
       tdomf_register_form_widget_hack("imagecaptcha-$i","Image Captcha $i", 'tdomf_widget_imagecaptcha_hack',array(), $i);
       tdomf_register_form_widget_validate("imagecaptcha-$i", "Image Captcha $i",'tdomf_widget_imagecaptcha_validate', array(), $i);
     }
  }
}
add_action('tdomf_create_post_start','tdomf_widget_imagecaptcha_init');
add_action('tdomf_generate_form_start','tdomf_widget_imagecaptcha_init');
add_action('tdomf_validate_form_start','tdomf_widget_imagecaptcha_init');
#add_action('tdomf_widget_page_top','tdomf_widget_imagecaptcha_init');
add_action('tdomf_control_form_start','tdomf_widget_imagecaptcha_init');

//////////////////////////////
// Display the widget! 
//
function tdomf_widget_imagecaptcha($args,$params) {
  extract($args);
  $form_data = tdomf_get_form_data($tdomf_form_id);
  if(!isset($args['imagecaptcha'])) {
    $form_data['freecap_attempts_'.$tdomf_form_id] = 0;
    $form_data['freecap_word_hash_'.$tdomf_form_id] = false;
    tdomf_save_form_data($tdomf_form_id,$form_data);
  }

  $output  = $before_widget;

  $output .= <<< EOT
		<script type="text/javascript">
		<!--
		function new_freecap_$tdomf_form_id()
		{
			// loads new freeCap image
			if(document.getElementById)
			{
				// extract image name from image source (i.e. cut off ?randomness)
				thesrc = document.getElementById("freecap_$tdomf_form_id").src;
				// add ?(random) to prevent browser/isp caching
				document.getElementById("freecap_$tdomf_form_id").src = thesrc+"?"+Math.round(Math.random()*100000);
			} else {
				alert("Sorry, cannot autoreload freeCap image\\nSubmit the form and a new freeCap will be loaded");
			}
		}
		//-->
		</script>
EOT;
  
  $output .= "\n\t\t<img src='".TDOMF_WIDGET_URLPATH."freecap/freecap_tdomf.php?tdomf_form_id=$tdomf_form_id'  id='freecap_$tdomf_form_id' alt='' />\n\t\t<br/>\n";
  $output .= "\t\t<small>".sprintf(__("If you can't read the word in the image, <a href=\"%s\">click here</a>","tdomf"),'#" onclick="this.blur();new_freecap_'.$tdomf_form_id.'();return false;')."</small>\n\t\t<br/>\n";
  $output .= "\t\t".'<label for="imagecaptcha_'.$tdomf_form_id.'" class="required" >'."\n";
  $output .= "\t\t".__('What is the word in the image? ','tdomf')."\n\t\t<br/>\n";
  $output .= "\t\t".'<input type="text" id="imagecaptcha_'.$tdomf_form_id.'" name="imagecaptcha_'.$tdomf_form_id.'" size="30" value="'.htmlentities($args["imagecaptcha"],ENT_QUOTES).'" />'."\n";
  $output .= "\t\t".'</label>';
      
  $output .= $after_widget;
  return $output;
}

////////////////////////////////////////////////////////////////////////////
// Validate answer but using post so we don't have to validate it at preview!
//
function tdomf_widget_imagecaptcha_validate($args,$preview,$params) {
  if($preview) { return NULL; }
  extract($args);
  $form_data = tdomf_get_form_data($tdomf_form_id);
  
  // all freeCap words are lowercase.
	// font #4 looks uppercase, but trust me, it's not...
	if($form_data['hash_func_'.$tdomf_form_id](strtolower($args["imagecaptcha_".$tdomf_form_id]))==$form_data['freecap_word_hash_'.$tdomf_form_id])
	{
		// reset freeCap session vars
		// cannot stress enough how important it is to do this
		// defeats re-use of known image with spoofed session id
		$form_data['freecap_attempts_'.$tdomf_form_id] = 0;
		$form_data['freecap_word_hash_'.$tdomf_form_id] = false;
    tdomf_save_form_data($tdomf_form_id,$form_data);
	} else {
		return $before_widget.__("You must enter the word in the image as you see it.","tdomf").$after_widget;
	}
  
  return NULL;
}

tdomf_register_form_widget("imagecaptcha","Image Captcha", 'tdomf_widget_imagecaptcha');
tdomf_register_form_widget_validate("imagecaptcha", "Image Captcha",'tdomf_widget_imagecaptcha_validate', true);
tdomf_register_form_widget_hack("imagecaptcha", "Image Captcha",'tdomf_widget_imagecaptcha', true);

?>