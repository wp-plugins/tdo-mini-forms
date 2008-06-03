<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

//////////////////////////////////
// Code for Email Notifications //
/////////////////////////////////

// This includes the core "Notify Me" widget

// Validate email address
// Taken from http://www.ilovejackdaniels.com/php/email-address-validation/
//
function tdomf_check_email_address($email) {
  // First, we check that there's one @ symbol, and that the lengths are right
  if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
    // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
    return false;
  }
  // Split it into sections to make life easier
  $email_array = explode("@", $email);
  $local_array = explode(".", $email_array[0]);
  for ($i = 0; $i < sizeof($local_array); $i++) {
     if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
      return false;
    }
  }  
  if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
    $domain_array = explode(".", $email_array[1]);
    if (sizeof($domain_array) < 2) {
        return false; // Not enough parts to domain
    }
    for ($i = 0; $i < sizeof($domain_array); $i++) {
      if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
        return false;
      }
    }
  }
  return true;
}

// Grab email address of moderators
//
function tdomf_get_admin_emails($form_id) {
  global $wpdb;

  // grab email addresses
  $email_list = "";
  $notify_roles = tdomf_get_option_form(TDOMF_NOTIFY_ROLES,$form_id);
  if($notify_roles != false) {
     if($notify_roles != false) {
        $users = tdomf_get_all_users();
        $notify_roles = explode(';',$notify_roles);
        foreach($users as $user) {
           $user = get_userdata($user->ID);
           if(!empty($user->user_email)) {
              foreach($notify_roles as $role) {
                 if(!empty($role) && isset($user->{$wpdb->prefix.'capabilities'}[$role])){
                    $email_list .= $user->user_email.", ";
                    break;
                 }
              }
           }
        }
     }
  }
  return $email_list;
}

// Notify Admins to tell them that a post is awaiting moderation
//
function tdomf_notify_admins($post_ID,$form_id){
  global $wpdb,$tdomf_form_widgets_adminemail,$post_meta_cache,$blog_id;

  // grab email addresses
  $email_list = tdomf_get_admin_emails($form_id);
  if($email_list == "") {
     tdomf_log_message("Could not get any email addresses to notify. No moderation notification email sent.",TDOMF_LOG_BAD);
     return false;
  }

  // For some reason, the post meta value cache does not include private 
  // keys (those starting with _) so unset it and update it properly!
  //
  unset($post_meta_cache[$blog_id][$post_ID]);
  update_postmeta_cache($post_ID);
  
  // Submitter Info
  //
  $can_ban_user = false;
  $submitter_string = "N/A";
  $user_ID = get_post_meta($post_ID,TDOMF_KEY_USER_ID,true);
  $submitter_name = get_post_meta($post_ID,TDOMF_KEY_NAME,true);
  if($user_ID) {
     $submitter_string = get_post_meta($post_ID,TDOMF_KEY_USER_NAME,true);
     $can_ban_user = true;
  } else if($submitter_name) {
     $submitter_email = get_post_meta($post_ID,TDOMF_KEY_EMAIL,true);
     $submitter_string = $submitter_name;
     if($submitter_email) {
        $submitter_string .= " (".$submitter_email.")";
     }
  }

  // IP info
  //
  $ip = get_post_meta($post_ID,TDOMF_KEY_IP,true);

  // Title and content of post
  //
  $post = get_post($post_ID);
  $content = $post->post_content;
  $title = $post->post_title;

  //Admin links
  //
  $moderate_all_link = get_bloginfo('wpurl').'/wp-admin/admin.php?page=tdomf_show_mod_posts_menu';
  
  //View link
  //
  $view_post = get_permalink($post_ID);

  //Spam links
  //
  $is_spam = get_post_meta($p->ID, TDOMF_KEY_SPAM); 
  $spam_link = get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_mod_posts_menu&action=spamit&post=$post_ID";
  $spam_link = wp_nonce_url($spam_link,'tdomf-spamit_'.$post_ID);
  $ham_link = get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_mod_posts_menu&action=hamit&post=$post_ID";
  $ham_link = wp_nonce_url($ham_link,'tdomf-hamit_'.$post_ID);
  
  // Subject line
  //
  if($is_spam && !get_option(TDOMF_OPTION_SPAM)) {
     $subject = sprintf(__("[SPAM] [%s] Please moderate this spam post","tdomf"),get_bloginfo('title'));
  } else {
     $subject = sprintf(__("[%s] Please moderate this new post request from %s","tdomf"),get_bloginfo('title'),$submitter_name);
  }
  
  // Email Body
  //
  $email_msg  = sprintf(__("A new post with title \"%s\" from %s is awaiting your approval.\r\n\r\n","tdomf"),$title,$submitter_string);
  if($is_spam && !get_option(TDOMF_OPTION_SPAM) ) {
      $email_msg = sprintf(__("This post is considered SPAM. You can mark it as not being spam from %s\r\n\r\n","tdomf"),$ham_link);
  }
  $email_msg .= sprintf(__("It was submitted using Form ID %d (\"%s\")\r\n\r\n","tdomf"),$form_id,tdomf_get_option_form(TDOMF_OPTION_NAME,$form_id));
  $email_msg .= sprintf(__("This was submitted from IP %s.\r\n\r\n","tdomf"),$ip);
  $email_msg .= sprintf(__("You can view this post from %s.\r\n\r\n","tdomf"),$view_post); 
  $email_msg .= sprintf(__("You can moderate this submission from %s.\r\n\r\n","tdomf"),$moderate_all_link);
  if(!$is_spam && !get_option(TDOMF_OPTION_SPAM)) {
      $email_msg .= sprintf(__("You can flag this post as spam from %s.\r\n\r\n","tdomf"),$spam_link);
  }
  $email_msg .= sprintf(__("Content of the post: \r\n\r\n %s \r\n\r\n","tdomf"),$content);
  
   // Widgets:adminemail
   //
   $widget_args = array( "post_ID"=>$post_ID,
                         "before_widget" => "",
                         "after_widget"  => "\r\n\r\n\n",
                         "before_title"  => "",
                         "after_title"   => "\r\n\r\n",
                         "tdomf_form_id" => $form_id);
   $widget_order = tdomf_get_widget_order();
   foreach($widget_order as $w) {
	  if(isset($tdomf_form_widgets_adminemail[$w])) {
      $temp_message = $tdomf_form_widgets_adminemail[$w]['cb']($widget_args,$tdomf_form_widgets_adminemail[$w]['params']);
      if($temp_message != NULL && trim($temp_message) != ""){
        $email_msg .= $temp_message;
      }
	  }
   }
   
  $email_msg .= sprintf(__("Best Regards\r\n\r\nTDOMF @ %s","tdomf"),get_bloginfo("title"));

  // Use custom from field
  //
  if(tdomf_get_option_form(TDOMF_OPTION_FROM_EMAIL,$form_id)) {

  	// We can modify the "from" field by using the "header" option at the end!
  	//
  	$headers = "MIME-Version: 1.0\n" .
  	           "From: ". tdomf_get_option_form(TDOMF_OPTION_FROM_EMAIL,$form_id) . "\n" .
  	           "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";

  	return @wp_mail($email_list, $subject, $email_msg, $headers);
  } else {
  	return @wp_mail($email_list, $subject, $email_msg);
  }
}

// Notify Poster of approved post
//
function tdomf_notify_poster_approved($post_id) {
   global $wpdb;
   
   $email = get_post_meta($post_id, TDOMF_KEY_NOTIFY_EMAIL, true);
   delete_post_meta($post_id, TDOMF_KEY_NOTIFY_EMAIL);

   if(get_post_meta($post_id,TDOMF_KEY_SPAM,true)) {
      tdomf_log_message_extra("tdomf_notify_poster_approved: post $post_id is spam -- do nothing.");
      return $post_id;
   }
      
   if($email != false) {
      tdomf_log_message_extra("tdomf_notify_poster_approved: $email");
   }
   
   if(tdomf_check_email_address($email)){

    tdomf_log_message("Attempting to send notification email to $email for approved post $post_id!");
     
    $postdata = get_postdata($post_id);
    $title = $postdata['Title'];
    $form_id = get_post_meta($post_id, TDOMF_KEY_FORM_ID, true);
    if($form_id == false || !tdomf_form_exists($form_id)){
      $form_id = tdomf_get_first_form_id();
    }
    
    $subject = sprintf(__("[%s] Your entry \"%s\" has been approved!","tdomf"),get_bloginfo('title'),$title);

    $notify_message = sprintf(__("This is just a quick email to notify you that your post has been approved and published online. You can see it at %s.\r\n\r\n","tdomf"),get_permalink($post_id));
    $notify_message .= __("Best Regards","tdomf")."\r\n";
    $notify_message .= get_bloginfo("title");

    // Use custom from field
    //
    if(tdomf_get_option_form(TDOMF_OPTION_FROM_EMAIL,$form_id)) {
  
      // We can modify the "from" field by using the "header" option at the end!
      //
      $headers = "MIME-Version: 1.0\n" .
                 "From: ". tdomf_get_option_form(TDOMF_OPTION_FROM_EMAIL,$form_id) . "\n" .
                 "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
  
      return @wp_mail($email, $subject, $notify_message, $headers);
    } else {
      return @wp_mail($email, $subject, $notify_message);
    }
   }
   return $post_id;
}
// Notify Poster of rejected post
//
function tdomf_notify_poster_rejected($post_id) {
   global $wpdb;
   
   tdomf_log_message_extra("tdomf_notify_poster_rejected: $email");

   $email = get_post_meta($post_id, TDOMF_KEY_NOTIFY_EMAIL, true); 
   delete_post_meta($post_id, TDOMF_KEY_NOTIFY_EMAIL);

   if(get_post_meta($post_id,TDOMF_KEY_SPAM,true)) {
      tdomf_log_message_extra("tdomf_notify_poster_rejected: post $post_id is spam -- do nothing.");
      return $post_id;
   }
   
   if(tdomf_check_email_address($email)){

    tdomf_log_message("Attempting to send notification email to $email for rejected post $post_id!");
     
    $postdata = get_postdata($post_id);
    $title = $postdata['Title'];
    $form_id = get_post_meta($post_id, TDOMF_KEY_FORM_ID, true);
    if($form_id == false || !tdomf_form_exists($form_id)){
      $form_id = tdomf_get_first_form_id();
    }

    $subject = sprintf(__("[%s] Your entry \"%s\" has been rejected! :(","tdomf"),get_bloginfo('title'),$title);

    $notify_message = sprintf(__("We are sorry to inform you that your post \"%s\" has been rejected.\r\n\r\n","tdomf"),$title);
    $notify_message .= __("Best Regards","tdomf")."\r\n";
    $notify_message .= get_bloginfo("title");

    // Use custom from field
    //
    if(tdomf_get_option_form(TDOMF_OPTION_FROM_EMAIL,$form_id)) {
  
      // We can modify the "from" field by using the "header" option at the end!
      //
      $headers = "MIME-Version: 1.0\n" .
                 "From: ". tdomf_get_option_form(TDOMF_OPTION_FROM_EMAIL,$form_id) . "\n" .
                 "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
  
      return @wp_mail($email, $subject, $notify_message, $headers);
    } else {
      return @wp_mail($email, $subject, $notify_message);
    }
   }
   return $post_id;
}
add_action('publish_post', 'tdomf_notify_poster_approved');
add_action('delete_post', 'tdomf_notify_poster_rejected');

////////////////////////////////////////////////////////////////////////////////
//                                             Default Widgets: "Notify Me"   //
////////////////////////////////////////////////////////////////////////////////

// Do we need to display a email input?
//
function tdomf_widget_notifyme_show_email_input($form_id){
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
  }
  return $show_email_input;
}

// Widget core
//
function tdomf_widget_notifyme($args) {
  global $current_user;
  get_currentuserinfo();

  extract($args);
  
  // Dont' do anything if the user can already publish or is trusted!
  //
  if(!tdomf_get_option_form(TDOMF_OPTION_MODERATION,$tdomf_form_id) || current_user_can('publish_posts')){
    return "";
   } else if(is_user_logged_in() && $current_user->ID != get_option(TDOMF_DEFAULT_AUTHOR)) {
     $user_status = get_usermeta($current_user->ID,TDOMF_KEY_STATUS);
     if($user_status == TDOMF_USER_STATUS_TRUSTED) {
       return "";
     }
   }

   $output = $before_widget;
   
  // Check if values set in cookie
  if(!isset($notifyme_email) && isset($_COOKIE['tdomf_notify_widget_email'])) {
    $notifyme_email = $_COOKIE['tdomf_notify_widget_email'];
  }
  
  $show_email_input = tdomf_widget_notifyme_show_email_input($tdomf_form_id);

  $output .= "<label for='notifyme'><input type='checkbox' name='notifyme' id='notifyme'";
  if(isset($notifyme)) $output .= " checked "; 
  $output .= " /> ".__("Do you wish to be notified when your post is approved (or rejected)?","tdomf")."</label>";
  
  if($show_email_input) {
    $output .=  "<br/><label for='notifyme_email'>".__("Email for notification:","tdomf").' <input type="text" value="'.htmlentities($notifyme_email,ENT_QUOTES).'" name="notifyme_email" id="notifyme_email" size="40" /></label>';
  }
  
  $output .= $after_widget;
  return $output;
}
tdomf_register_form_widget('notifyme', 'Notify Me', 'tdomf_widget_notifyme');

// Widget core
//
function tdomf_widget_notifyme_hack($args) {
  global $current_user;
  get_currentuserinfo();

  extract($args);
  
   $output  = "\t<?php if(tdomf_get_option_form(TDOMF_OPTION_MODERATION,\$tdomf_form_id) && !current_user_can('publish_posts') && !tdomf_current_user_default_author() && !tdomf_current_user_trusted()) { ?>\n\t";
   $output .= $before_widget;
   
    $output .= "\t\t\t<label for='notifyme'><input type='checkbox' name='notifyme' id='notifyme'";
    $output .= "<?php if(isset(\$notifyme)) { ?> checked <?php } ?>"; 
    $output .= " /> ".__("Do you wish to be notified when your post is approved (or rejected)?","tdomf")."</label>\n";

    $output .= "\t\t<?php if(tdomf_widget_notifyme_show_email_input(%%FORMID%%)) { ?>\n";
    $output .= "\t\t\t<?php if(isset(\$_COOKIE['tdomf_notify_widget_email'])) { \$notifyme_email = \$_COOKIE['tdomf_notify_widget_email']; } ?>\n";
    $output .= "\t\t\t\t<br/>\n\t\t\t\t<label for='notifyme_email'>".__("Email for notification:","tdomf").' <input type="text" value="';
    $output .= '<?php echo htmlentities($notifyme_email,ENT_QUOTES); ?>'.'" name="notifyme_email" id="notifyme_email" size="40" /></label>'."\n";
    $output .= "\t\t<?php } ?>";
    
   $output .= $after_widget;
   $output .= "\t<?php } ?>";
 
  
  $output .= $after_widget;
  return $output;
}
tdomf_register_form_widget_hack('notifyme', 'Notify Me', 'tdomf_widget_notifyme_hack');

// Widget validate input
//
function tdomf_widget_notifyme_validate($args,$preview) {
  extract($args);
  if(!$preview) {
    if(tdomf_widget_notifyme_show_email_input($tdomf_form_id)) {
      if(isset($notifyme) && !tdomf_check_email_address($notifyme_email)) {
        return $before_widget.__("You must specify a valid email address to send the notification to.","tdomf").$after_widget;
      }
    }
  }
  return NULL;
}
tdomf_register_form_widget_validate('notifyme', 'Notify Me', 'tdomf_widget_notifyme_validate');

// Widget post submitted post-op
//
function tdomf_widget_notifyme_post($args) {
  global $current_user;
  get_currentuserinfo();
  extract($args);
  if(isset($notifyme)) {
    if(!isset($notifyme_email)) {
      if(is_user_logged_in() && tdomf_check_email_address($current_user->user_email)) {
        $notifyme_email = $current_user->user_email;
      } else if(isset($whoami_email)) {
        $notifyme_email = $whoami_email;
      } else {
        tdomf_log_message("Could not find a email address to store for notification!",TDOMF_LOG_ERROR);
      }
    }
    setcookie("tdomf_notify_widget_email",$notifyme_email, time() + 30000000, COOKIEPATH, COOKIE_DOMAIN);
    add_post_meta($post_ID, TDOMF_KEY_NOTIFY_EMAIL, $notifyme_email, true);    
  }
  return NULL;
}
tdomf_register_form_widget_post('notifyme', 'Notify Me', 'tdomf_widget_notifyme_post');

?>
