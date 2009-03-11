<?php
/*
Name: "reCaptcha"
URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: Use recaptcha to verify user input
Version: 2
Author: Mark Cunningham
Author URI: http://thedeadone.net
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

// Path to recaptchalib.php
define("TDOMF_RECAPTCHALIB_PATH",TDOMF_WIDGET_PATH.'recaptcha/recaptchalib.php');

/* 1. report error */

  function tdomf_widget_recaptcha($args) {
    $options = tdomf_widget_recaptcha_get_options($args['tdomf_form_id']);
    
    extract($args);
    
    $output = $before_widget;

    if(!empty($options['title'])) {
        $output .= $before_title;
        $output .= $options['title'];
        $output .= $after_title;
    }
    
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
        $use_ssl = true;
    } else {
        $use_ssl = false;
    }

    if(!function_exists('recaptcha_get_html')) {
        @require_once(TDOMF_RECAPTCHALIB_PATH);
    }
    
    $output .= <<<END
		<script type='text/javascript'>
		var RecaptchaOptions = { theme : '{$options['theme']}', lang : '{$options['language']}' , tabindex : 30 };
		</script>
END;

    $form_data = tdomf_get_form_data($args['tdomf_form_id']);
    $error = null;
    if(isset($form_data['recaptcha_error'])) {
        $error = $form_data['recaptcha_error'];
    }

    $output .= recaptcha_get_html ($options['publickey'], $error, $use_ssl, $options['xhtml']);
    
    $output .= $after_widget;
    
    return $output;
  }
  tdomf_register_form_widget('recaptcha',__('reCaptcha',"tdomf"), 'tdomf_widget_recaptcha');
 
 
function tdomf_widget_recaptcha_validate($args,$preview) {
    
  // don't bother validating for preview
  if($preview) {
    return NULL;
  }
  
  $options = tdomf_widget_recaptcha_get_options($args['tdomf_form_id']);
  
  tdomf_log_message('dump<pre>' . var_export($args,true) . '</pre>');
  
  if (empty($args['recaptcha_response_field'])) {
      return __('Please complete the reCAPTCHA.','tdomf');
  }

  if(!function_exists('recaptcha_check_answer')) {
      @require_once(TDOMF_RECAPTCHALIB_PATH);
  }  
  
    $response = recaptcha_check_answer($options['privatekey'],
        $_SERVER['REMOTE_ADDR'],
        $args['recaptcha_challenge_field'],
        $args['recaptcha_response_field']);

    if (!$response->is_valid) {
        $form_data = tdomf_get_form_data($args['tdomf_form_id']);  
        $form_data['recaptcha_error'] = $response->error;
        tdomf_save_form_data($args['tdomf_form_id'],$form_data);
        if ($response->error == 'incorrect-captcha-sol') {
                return __('That reCAPTCHA was incorrect.','tdomf');
        } else {
                tdomf_log_message('reCAPTCHA error ' . $response->error . '. Please refer to <a href="http://recaptcha.net/apidocs/captcha/">reCaptcha docs</a> for more information', TDOMF_LOG_ERROR);
                return __('Invalid reCAPTCHA configuration.','tdomf');
        }
    }
  return NULL;
}
tdomf_register_form_widget_validate('recaptcha',__('reCaptcha',"tdomf"), 'tdomf_widget_recaptcha_validate', array());
  
  
  function tdomf_widget_recaptcha_get_options($form_id,$strict=false) {
        $options = tdomf_get_option_widget('tdomf_recaptcha_widget',$form_id);
        $recaptcha_options = get_option('recaptcha');
        if($options == false) {
           $options = array();
           $options['title'] = "";
           $options['publickey'] = "";
           $options['privatekey'] = "";
           $options['theme'] = 'red'; 
           $options['language'] = 'en';
           $options['xhtml'] = false;
           $options['plugin'] = false;
           if($recaptcha_options != false) {
                   $options['publickey'] = $recaptcha_options['pubkey'];
                   $options['privatekey'] = $recaptcha_options['privkey'];
                   $options['theme'] = $recaptcha_options['re_theme']; 
                   $options['language'] = $recaptcha_options['re_lang'];
                   $options['xhtml'] = $recaptcha_options['re_xhtml'];
           }
        } else if(!$strict && $options['plugin']) {
           if($recaptcha_options != false) {
                   $options['publickey'] = $recaptcha_options['pubkey'];
                   $options['privatekey'] = $recaptcha_options['privkey'];
                   $options['theme'] = $recaptcha_options['re_theme']; 
                   $options['language'] = $recaptcha_options['re_lang'];
                   $options['xhtml'] = $recaptcha_options['re_xhtml'];
           }
        }
      return $options;
    }
  
  function tdomf_widget_recaptcha_control($form_id) {
      $options = tdomf_widget_recaptcha_get_options($form_id,true);
  
  // Store settings for this widget
    if ( $_POST['excerpt-submit'] ) {
           $newoptions['title'] = $_POST['recaptcha-title'];
           $newoptions['publickey'] = $_POST['recaptcha-publickey'];
           $newoptions['privatekey'] = $_POST['recaptcha-privatekey'];
           $newoptions['theme'] = $_POST['recaptcha-themekey']; 
           $newoptions['language'] = $_POST['recaptcha-language'];
           $newoptions['xhtml'] = isset($_POST['recaptcha-xhtml']);
           $newoptions['plugin'] = isset($_POST['recaptcha-plugin']);
     if ( $options != $newoptions ) {
        $options = $newoptions;
        tdomf_set_option_widget('tdomf_recaptcha_widget', $options,$form_id);
     }
  }

    if(!function_exists('recaptcha_get_signup_url')) {
      @require_once(TDOMF_RECAPTCHALIB_PATH);
    }  
  
    // get blog domain
    $uri = parse_url(get_settings('siteurl'));
	$blogdomain = $uri['host'];
    
   // Display control panel for this widget
  
  extract($options);

        ?>
<div>
    <label for="recaptcha-title" style="line-height:35px;"><?php _e("Title: ","tdomf"); ?></label>
    <input type="textfield" id="recaptcha-title" name="recaptcha-title" value="<?php echo htmlentities($options['title'],ENT_QUOTES,get_bloginfo('charset')); ?>" />

    <br/><br/>

    <small><?php _e('If this option is enabled and the reCaptcha plugin is active, the settings for comments will overwrite any configuration below.','tdomf'); ?></small><br/>
    <input type="checkbox" name="recaptcha-plugin" id="recaptcha-plugin" <?php if($options['plugin']) echo "checked"; ?> >
    <label for="recaptcha-plugin" style="line-height:35px;"><?php _e('Use comment settings from <a href="http://wordpress.org/extend/plugins/wp-recaptcha/">reCaptcha plugin</a> if active',"tdomf"); ?></label>
    
    <br/><br/>
    
    <small><?php printf(__('reCAPTCHA requires an API key, consisting of a "public" and a "private" key. You can sign up for a <a href="%s" target="0">free reCAPTCHA key</a>.','tdomf'),recaptcha_get_signup_url ($blogdomain, 'wordpress')); ?></small>
    <br/><br/>

    <label class="which-key" for="recaptcha-publickey" style="line-height:35px;">Public Key:</label>
    <input name="recaptcha-publickey" id="recaptcha-publickey" size="40" value="<?php  echo $options['publickey']; ?>" />
    
    <br />
    <label class="which-key" for="recaptcha-privatekey" style="line-height:35px;">Private Key:</label>
    <input name="recaptcha-privatekey" id="recaptcha-privatekey" size="40" value="<?php  echo $options['privatekey']; ?>" />

    <br/>

    <div class="theme-select">
    <label for="recaptcha-themekey" style="line-height:35px;"><?php _e('Theme:','tdomf'); ?></label>
    <select name="recaptcha-themekey" id="recaptcha-themekey">
    <option value="red" <?php if($options['theme'] == 'red'){echo 'selected="selected"';} ?>><?php _e('Red','tdomf'); ?></option>
    <option value="white" <?php if($options['theme'] == 'white'){echo 'selected="selected"';} ?>><?php _e('White','tdomf'); ?></option>
    <option value="blackglass" <?php if($options['theme'] == 'blackglass'){echo 'selected="selected"';} ?>><?php _e('Black Glass','tdomf'); ?></option>
    <option value="clean" <?php if($options['theme'] == 'clean'){echo 'selected="selected"';} ?>><?php _e('Clean','tdomf'); ?></option>
    </select>
    </div>

    <br/>
    
    <div class="lang-select">
    <label for="recaptcha-language" style="line-height:35px;"><?php _e('Language:','tdomf'); ?></label>
    <select name="recaptcha-language" id="recaptcha-languageg">
    <option value="en" <?php if($options['language'] == 'en'){echo 'selected="selected"';} ?>><?php _e('English','tdomf'); ?></option>
    <option value="nl" <?php if($options['language'] == 'nl'){echo 'selected="selected"';} ?>><?php _e('Dutch','tdomf'); ?></option>
    <option value="fr" <?php if($options['language'] == 'fr'){echo 'selected="selected"';} ?>><?php _e('French','tdomf'); ?></option>
    <option value="de" <?php if($options['language'] == 'de'){echo 'selected="selected"';} ?>><?php _e('German','tdomf'); ?></option>
    <option value="pt" <?php if($options['language'] == 'pt'){echo 'selected="selected"';} ?>><?php _e('Portuguese','tdomf'); ?></option>
    <option value="ru" <?php if($options['language'] == 'ru'){echo 'selected="selected"';} ?>><?php _e('Russian','tdomf'); ?></option>
    <option value="es" <?php if($options['language'] == 'es'){echo 'selected="selected"';} ?>><?php _e('Spanish','tdomf'); ?></option>
    <option value="tr" <?php if($options['language'] == 'tr'){echo 'selected="selected"';} ?>><?php _e('Turkish','tdomf'); ?></option>
    </select>
    </label>
    </div>

    <br/>
    
    <input type="checkbox" name="recaptcha-xhtml" id="recaptcha-xhtml" <?php if($options['xhtml']) echo "checked"; ?> >
    <label for="recaptcha-xhtml" style="line-height:35px;"><?php _e("XHTML 1.0 Strict compliant.","tdomf"); ?></label><br/>
    <small><?php _e('Bad for users who don\'t have Javascript enabled in their browser (Majority do).','tdomf'); ?></small>
    
    </div>
    
<?php
}
tdomf_register_form_widget_control('recaptcha',__('reCaptcha',"tdomf"), 'tdomf_widget_recaptcha_control', 700, 500);

function tdomf_widget_recaptcha_admin_error($form_id) {
    
  $options = tdomf_widget_recaptcha_get_options($form_id,true);

  $output = "";  
  if(empty($options['publickey']) || empty($options['privatekey'])) {
      $output .= __('<b>Error</b>: Widget "reCaptcha" is missing the private and/or public keys and cannot function if theses are not set.','tdomf');
  }
  
  return $output;
}
tdomf_register_form_widget_admin_error('recaptcha',__('reCaptcha','tdomf'), 'tdomf_widget_recaptcha_admin_error');

?>