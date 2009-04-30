<?php
/*
Name: "Who Am I"
URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: One of the default widgets, allowing for more detailed user information
Version: 1
Author: Mark Cunningham
Author URI: http://thedeadone.net
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

///////////////////////
// "Who Am I" Widget //
///////////////////////

// Store user submitted defaults as cookies
//
function tdomf_widget_whoami_store_cookies($name = "", $email = "", $web = "") {
  setcookie("tdomf_whoami_widget_name",$name, time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
  setcookie("tdomf_whoami_widget_email",$email, time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
  setcookie("tdomf_whoami_widget_web",$web, time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
}

// Grab the options for the whoami widget. If none avaliable, generate default
//
function tdomf_widget_whoami_get_options($form_id) {
  $options = tdomf_get_option_widget('tdomf_whoami_widget',$form_id);
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
  $options = tdomf_widget_whoami_get_options($tdomf_form_id);

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
     $output .= ">".__("Name:","tdomf").' <br/><input type="text" value="'.htmlentities($whoami_name,ENT_QUOTES,get_bloginfo('charset')).'" name="whoami_name" id="whoami_name" />';
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
     $output .= ">".__("Email:","tdomf").'<br/><input type="text" value="'.htmlentities($whoami_email,ENT_QUOTES,get_bloginfo('charset')).'" name="whoami_email" id="whoami_email" />';
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
     $output .= ">".__("Webpage:","tdomf").'<br/><input type="text" value="'.htmlentities($whoami_webpage,ENT_QUOTES,get_bloginfo('charset')).'" name="whoami_webpage" id="whoami_webpage" />';
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
tdomf_register_form_widget('who-am-i',__('Who Am I','tdomf'), 'tdomf_widget_whoami');

//////////////////////////////////////////
// Display and handle widget control panel 
//
function tdomf_widget_whoami_control($form_id) {
  $options = tdomf_widget_whoami_get_options($form_id);
  
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
        tdomf_set_option_widget('tdomf_whoami_widget', $options,$form_id);
     }
  }
  
  // Display control panel for this widget
  
  extract($options);

        ?>
<div>
<label for="who_am_i-title" style="line-height:35px;display:block;"><?php _e("Title: ","tdomf"); ?><input type="text" id="who_am_i-title" name="who_am_i-title" value="<?php echo htmlentities($options['title'],ENT_QUOTES,get_bloginfo('charset')); ?>" /></label>

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
tdomf_register_form_widget_control('who-am-i',__('Who Am I','tdomf'), 'tdomf_widget_whoami_control', 200, 380);

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
          $link .= tdomf_protect_input($args['whoami_name']);
    } else {
          $link .= __("unknown","tdomf");
    }
    if(isset($args['whoami_webpage'])){
          $link .= "</a>";
    }
    return $before_widget.sprintf(__("Submitted by %s.","tdomf"),$link).$after_widget;
  }
}
tdomf_register_form_widget_preview('who-am-i',__('Who Am I','tdomf'), 'tdomf_widget_whoami_preview');

///////////////////////////////////////
// Generate a simple hacked preview for widget
//
function tdomf_widget_whoami_preview_hack($args) {
  extract($args);

    $output  = $before_widget;
    $output .= "\t<?php if(is_user_logged_in()) { ?>\n";
    $output .= "\t\t".sprintf(__("Submitted by %s.","tdomf"),TDOMF_MACRO_USERNAME)."\n";  
    $output .= "\t<?php } else { ?>\n";
    
    $nonreg_user  = "<?php if(isset(\$post_args['whoami_webpage'])){ ?>";
    $nonreg_user .= "<a href=\"<?php echo \$whoami_webpage; ?>\">";
    $nonreg_user .= "<?php } ?>";
    
    $nonreg_user .= "<?php if(isset(\$post_args['whoami_name'])){ ";
    $nonreg_user .= "echo tdomf_protect_input(\$whoami_name); ";
    $nonreg_user .= "} else { ?>";
    $nonreg_user .= __("unknown","tdomf");
    $nonreg_user .= "<?php } ?>";
    
    $nonreg_user .= "<?php if(isset(\$post_args['whoami_webpage'])){ ?>";
    $nonreg_user .= "</a>";
    $nonreg_user .= "<?php } ?>";
    
    $output .= "\t\t".sprintf(__("Submitted by %s.","tdomf"),$nonreg_user)."\n";
    
    $output .= "\t<?php } ?>\n";
    $output .= $after_widget;

    return $output;
}
tdomf_register_form_widget_preview_hack('who-am-i',__('Who Am I','tdomf'), 'tdomf_widget_whoami_preview_hack');

//////////////////////////////////
// Validate input for this widget
//
function tdomf_widget_whoami_validate($args,$preview) {
  // only preview - no validation required
  if($preview) {
    return NULL;
  }
  // if user logged in, no validation required
  if(is_user_logged_in()){
    return NULL;
  }
  // do validation
  extract($args);
  $output = "";
  $options = tdomf_widget_whoami_get_options($tdomf_form_id);
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
tdomf_register_form_widget_validate('who-am-i',__('Who Am I','tdomf'), 'tdomf_widget_whoami_validate');

///////////////////////////////////
// Update post after form submitted 
//
function tdomf_widget_whoami_post($args) {
  global $current_user;
  get_currentuserinfo();
  extract($args);
  if(isset($whoami_name)) {
    add_post_meta($post_ID, TDOMF_KEY_NAME, tdomf_protect_input($whoami_name), true);
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
  tdomf_widget_whoami_store_cookies(tdomf_protect_input($whoami_name),$whoami_email,$whoami_webpage);
  return NULL;
}
tdomf_register_form_widget_post('who-am-i',__('Who Am I','tdomf'), 'tdomf_widget_whoami_post');

/////////
// version of whoami widget for hacker
//
function tdomf_widget_whoami_hack($args) {
  global $current_user;
  
  get_currentuserinfo();
  extract($args);
  $options = tdomf_widget_whoami_get_options($tdomf_form_id);
  
  $output = $before_widget;  
  
  // logged in version
  
  $output .= "\t\t<?php if(is_user_logged_in()) { ?>\n";
  $tdomfurl = get_bloginfo('wpurl')."/wp-admin/admin.php?page=".TDOMF_FOLDER;
  $output .= <<<EOT
			<p>You are currently logged in as %%USERNAME%%.
			<?php if(current_user_can('manage_options')) { ?>
				<a href='$tdomfurl'>You can configure this form &raquo;</a>
			<?php } ?></p>
EOT;
  
  // logged out version
  
  $output .= "\n\t\t<?php } else { ?>\n";
  
  $login_uri = get_bloginfo('wpurl').'/wp-login.php?redirect_to='.TDOMF_MACRO_FORMURL;
  $reg_uri = get_bloginfo('wpurl').'/wp-register.php?redirect_to='.TDOMF_MACRO_FORMURL;
  
  $output .= "\t\t\t<p>".sprintf(__("We do not know who you are. Please supply your name and email address. Alternatively you can <a href=\"%s\">log in</a> if you have a user account or <a href=\"%s\">register</a> for a user account if you do not have one.","tdomf"),$login_uri,$reg_uri)."</p>\n";
  
   if($options['name-enable']) {
     $output .= <<<EOT
			<?php if(!isset(\$whoami_name) && isset(\$_COOKIE['tdomf_whoami_widget_name'])) {
				\$whoami_name = \$_COOKIE['tdomf_whoami_widget_name'];
			} ?>
EOT;
     $output .=  "\n\t\t\t<label for='whoami_name'";
     if($options['name-required']) {
         $output .= ' class="required" ';
     }
     $output .= ">".__("Name:","tdomf")."\n\t\t\t\t<br/>\n\t\t\t\t<input type=\"text\" value=\"";
     $output .= '<?php echo htmlentities($whoami_name,ENT_QUOTES,get_bloginfo(\'charset\')); ?>';
     $output .= '" name="whoami_name" id="whoami_name" />';
     if($options['name-required']) {
         $output .= __(" (Required)","tdomf");
     }
     $output .= "\n\t\t\t</label>";
     $output .= "\n\t\t\t<br/>\n\t\t\t<br/>\n";
  }
  
   if($options['email-enable']) {
       $output .= <<<EOT
			<?php if(!isset(\$whoami_email) && isset(\$_COOKIE['tdomf_whoami_widget_name'])) {
				\$whoami_email = \$_COOKIE['tdomf_whoami_widget_email'];
			} ?>
EOT;
     $output .=    "\n\t\t\t<label for='whoami_email'";
     if($options['email-required']) {
         $output .= ' class="required" ';
     }
     $output .= ">".__("Email:","tdomf")."\n\t\t\t\t<br/>\n\t\t\t\t<input type=\"text\" value=\"";
     $output .= '<?php echo htmlentities($whoami_email,ENT_QUOTES,get_bloginfo(\'charset\')); ?>';
     $output .= '" name="whoami_email" id="whoami_email" />';
     if($options['email-required']) {
         $output .= __(" (Required)","tdomf");
     }
     $output .= "\n\t\t\t</label>";
     $output .= "\n\t\t\t<br/>\n\t\t\t<br/>\n";
  }
  
   if($options['webpage-enable']) {
		$output .= <<<EOT
			<?php if(!isset(\$whoami_webpage) && isset(\$_COOKIE['tdomf_whoami_widget_name'])) {
				\$whoami_webpage = \$_COOKIE['tdomf_whoami_widget_webpage'];
			}
			if(!isset(\$whoami_webpage) || empty(\$whoami_webpage)){ \$whoami_webpage = "http://"; } ?>
EOT;
     $output .=    "\n\t\t\t<label for='whoami_webpage'";
     if($options['webpage-required']) {
        $output .= ' class="required" ';
     }
     $output .= ">".__("Webpage:","tdomf")."\n\t\t\t\t<br/>\n\t\t\t\t<input type=\"text\" value=\"";
     $output .= '<?php echo htmlentities($whoami_webpage,ENT_QUOTES,get_bloginfo(\'charset\')); ?>';
     $output .= '" name="whoami_webpage" id="whoami_webpage" />';
     if($options['webpage-required']) {
            $output .= __(" (Required)","tdomf");
     }
     $output .= "\n\t\t\t</label>";
     $output .= "\n\t\t\t<br/>\n\t\t\t<br/>\n";
  }
  
  $output .= "\t\t<?php } ?>";
  $output .= $after_widget;

  return $output;
}
tdomf_register_form_widget_hack('who-am-i',__('Who Am I','tdomf'), 'tdomf_widget_whoami_hack');
?>
