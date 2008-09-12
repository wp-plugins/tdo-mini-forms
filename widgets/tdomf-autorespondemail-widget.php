<?php

////////////////////////////////////////////////////////////////////////////////
//                                            "Auto Respond Email"            //
////////////////////////////////////////////////////////////////////////////////

// Get Options for this widget
//
function tdomf_widget_autorespondemail_get_options($form_id) {
  $options = tdomf_get_option_widget('tdomf_autorespondemail_widget',$form_id);
    if($options == false) {
       $options = array();
       $options['subject'] = sprintf(__("Your submission '%s' has been recieved!","tdomf"),TDOMF_MACRO_SUBMISSIONTITLE);
       $options['body'] = sprintf(__("Hi %s,\n\nYour submission %s has been recieved and will be online shortly\nThank you for using this service\nBest Regards\n%s","tdomf"),TDOMF_MACRO_USERNAME,TDOMF_MACRO_SUBMISSIONTITLE,"<?php echo get_bloginfo('title'); ?>");
    }
  return $options;
}

// Do we need to display a email input?
//
function tdomf_widget_autorespondemail_show_email_input($form_id){
  global $current_user;
  get_currentuserinfo();
  $show_email_input = true;
  if(is_user_logged_in() && tdomf_check_email_address($current_user->user_email)) {
    // user has already set a valid email address!
    $show_email_input = false;
  } else { 
    $widgets_in_use = tdomf_get_widget_order($form_id);
    if(in_array("who-am-i",$widgets_in_use)) {
      $whoami_options = tdomf_widget_whoami_get_options($form_id);
      if($whoami_options['email-enable'] && $whoami_options['email-required']) {
        // great, who-am-i widget will provide a valid email address!
        $show_email_input = false;
      }
    }
    if($show_email_input && in_array('notifyme',$widgets_in_use)) {
        // just as good! Notify me will supply an email address
        $show_email_input = false;
    }
  }
  return $show_email_input;
}

// Widget core
//
function tdomf_widget_autorespondemail($args) {
  global $current_user;
  get_currentuserinfo();

  extract($args);

  $output = $before_widget;
   
  // Check if values set in cookie
  if(!isset($autorespondemail_email) && isset($_COOKIE['tdomf_autorespond_widget_email'])) {
    $autorespondemail_email = $_COOKIE['tdomf_autorespond_widget_email'];
  }
  
  $show_email_input = tdomf_widget_autorespondemail_show_email_input($tdomf_form_id);

  if($show_email_input) {
    $output .=  "<br/><label for='autorespondemail_email'>".__("Email:","tdomf").' <input type="text" value="'.htmlentities($autorespondemail_email,ENT_QUOTES).'" name="autorespondemail_email" id="autorespondemail_email" size="40" /></label>';
  }
  
  $output .= $after_widget;
  return $output;
}
tdomf_register_form_widget('autorespondemail', __('Auto Respond Email','tdomf'), 'tdomf_widget_autorespondemail');

// Widget core
//
function tdomf_widget_autorespondemail_hack($args) {
  global $current_user;
  get_currentuserinfo();

  extract($args);
  
   $output .= $before_widget;

    $output .= "\t\t<?php if(tdomf_widget_autorespondemail_show_email_input(%%FORMID%%)) { ?>\n";
    $output .= "\t\t\t<?php if(isset(\$_COOKIE['tdomf_autorespond_widget_email'])) { \$autorespondemail_email = \$_COOKIE['tdomf_autorespond_widget_email']; } ?>\n";
    $output .= "\t\t\t\t<br/>\n\t\t\t\t<label for='autorespondemail_email'>".__("Email for notification:","tdomf").' <input type="text" value="';
    $output .= '<?php echo htmlentities($autorespondemail_email,ENT_QUOTES); ?>'.'" name="autorespondemail_email" id="autorespondemail_email" size="40" /></label>'."\n";
    $output .= "\t\t<?php } ?>";
    
   $output .= $after_widget;
 
  
  $output .= $after_widget;
  return $output;
}
tdomf_register_form_widget_hack('autorespondemail', __('Auto Respond Email','tdomf'), 'tdomf_widget_autorespondemail_hack');

// Widget validate input
//
function tdomf_widget_autorespondemail_validate($args,$preview) {
  extract($args);
  if(!$preview) {
    if(tdomf_widget_autorespondemail_show_email_input($tdomf_form_id)) {
      if(!tdomf_check_email_address($autorespondemail_email)) {
        return $before_widget.__("You must specify a valid email address!","tdomf").$after_widget;
      }
    }
  }
  return NULL;
}
tdomf_register_form_widget_validate('autorespondemail', __('Auto Respond Email','tdomf'), 'tdomf_widget_autorespondemail_validate');

// Widget post submitted post-op
//
function tdomf_widget_autorespondemail_post($args) {
  global $current_user;
  get_currentuserinfo();
  extract($args);
  if(!isset($autorespondemail_email)) {
      if(is_user_logged_in() && tdomf_check_email_address($current_user->user_email)) {
        $autorespondemail_email = $current_user->user_email;
      } else if(isset($whoami_email)) {
        $autorespondemail_email = $whoami_email;
      } else if(isset($notifyme_email)) {
        $autorespondemail_email = $notifyme_email;
      } else {
        tdomf_log_message("Could not find a email address to store for notification!",TDOMF_LOG_ERROR);
        return __("Could not find email address!","tdomf");
      }
  }
  setcookie("tdomf_autorespond_widget_email",$autorespondemail_email, time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
  add_post_meta($post_ID, "_tdomf_autorespond_widget_email", $autorespondemail_email, true);    

  // mail will be sent after post is created and post is not flagged as spam
  
  return NULL;
}
tdomf_register_form_widget_post('autorespondemail', __('Auto Respond Email','tdomf'), 'tdomf_widget_autorespondemail_post');

function tdomf_widget_autorespondemail_send_mail($post_id,$form_id) {
 
   // do nothing if no email set
   //   
   $autorespondemail_email = get_post_meta($post_id, '_tdomf_autorespond_widget_email', true);
   if($autorespondemail_email == false) {
       return false;
   }
   delete_post_meta($post_id, '_tdomf_autorespond_widget_email');
      
   // if spam, do nothing
   //
   if(get_post_meta($post_id,TDOMF_KEY_SPAM,true)) {
      return false;
   }
   
  $options = tdomf_widget_autorespondemail_get_options($form_id);
   
  $subject = tdomf_prepare_string($options['subject'], $form_id, "", $post_id);
  $body = tdomf_prepare_string($options['body'], $form_id, "", $post_id);
  $body = str_replace("\n","\r\n",$body);
      
  // Use custom from field
  //
  if(tdomf_get_option_form(TDOMF_OPTION_FROM_EMAIL,$form_id)) {
  
      // We can modify the "from" field by using the "header" option at the end!
      //
      $headers = "MIME-Version: 1.0\n" .
                 "From: ". tdomf_get_option_form(TDOMF_OPTION_FROM_EMAIL,$form_id) . "\n" .
                 "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
  
      $status = @wp_mail($autorespondemail_email, $subject, $body, $headers);
  } else {
      $status = @wp_mail($autorespondemail_email, $subject, $body);
  }
  
  // should we do some sort of error handling here?
  //
  tdomf_log_message("wp_mail returned $status for auto responde email on post $post_id");
  
  return true;
}
add_action('tdomf_create_post_end','tdomf_widget_autorespondemail_send_mail',10,2);

///////////////////////////////////////////////////
// Display and handle widget control panel 
//
function tdomf_widget_autorespondemail_control($form_id) {
  $options = tdomf_widget_autorespondemail_get_options($form_id);
  // Store settings for this widget
    if ( $_POST['autorespondemail-submit'] ) {
     $newoptions['subject'] = $_POST['autorespondemail-subject'];
     $newoptions['body'] = $_POST['autorespondemail-body'];
     if ( $options != $newoptions ) {
        $options = $newoptions;
        tdomf_set_option_widget('tdomf_autorespondemail_widget', $options, $form_id);
     }
  }

   // Display control panel for this widget
  
  extract($options);

        ?>
<div>

<p><?php _e("This widget sends a plain ascii email to the submitter once the form is submitted. Form Hacker macros and PHP code are fine here. Also, if Who Am I widget or Notify Me widget is used, the email address will be taken from there. If not avaliable (or not mandatory) a new field will be added to the form. Please make sure this is one of the bottom widgets on your form","tdomf"); ?></p>

<br/><br/>
<input type="text" name="autorespondemail-subject" id="autorespondemail-subject" size="70" value="<?php echo htmlentities($subject,ENT_QUOTES,get_bloginfo('charset')); ?>" />
<textarea title="true" rows="5" cols="70" name="autorespondemail-body" id="autorespondemail-body" ><?php echo htmlentities($body,ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
</div>

<?php 
}
tdomf_register_form_widget_control('autorespondemail', __('Auto Respond Email','tdomf'), 'tdomf_widget_autorespondemail_control', 700, 400);


?>
