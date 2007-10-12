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
function tdomf_get_admin_emails() {
  global $wpdb;

  // grab email addresses
  $email_list = "";
  $notify_roles = get_option(TDOMF_NOTIFY_ROLES);
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
function tdomf_notify_admins($post_ID){
  global $wpdb,$tdomf_form_widgets_adminemail;

  // grab email addresses
  $email_list = tdomf_get_admin_emails();
  if($email_list == "") {
     tdomf_log_message("Could not get any email addresses to notify. No moderation notification email sent.",TDOMF_LOG_BAD);
     return false;
  }

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
  
  // Subject line
  //
  $subject = sprintf(__("[%s] Please moderate this new post request from %s","tdomf"),get_bloginfo('title'),$submitter_name);

  // Email Body
  //
  $email_msg  = sprintf(__("A new post with title \"%s\" from %s is awaiting your approval.\r\n\r\n","tdomf"),$title,$submitter_string);
  $email_msg .= sprintf(__("This was submitted from IP %s.\r\n\r\n","tdomf"),$ip);
  $email_msg .= sprintf(__("You can moderate this submission from %s.\r\n\r\n","tdomf"),$moderate_all_link);
  $email_msg .= sprintf(__("Content of the post: \r\n\r\n %s \r\n\r\n","tdomf"),$content);
  
   // Widgets:adminemail
   //
   $widget_args = array( "post_ID"=>$post_ID,
                         "before_widget" => "",
                         "after_widget"  => "\r\n\r\n\n",
                         "before_title"  => "",
                         "after_title"   => "\r\n\r\n");
   $widget_order = tdomf_get_widget_order();
   foreach($widget_order as $w) {
	  if(isset($tdomf_form_widgets_adminemail[$w])) {
      $temp_message = $tdomf_form_widgets_adminemail[$w]['cb']($widget_args);
      if($temp_message != NULL && trim($temp_message) != ""){
        $email_msg .= $temp_message;
      }
	  }
   }
   
  $email_msg .= sprintf(__("Best Regards\r\n\r\nTDOMF @ %s","tdomf"),get_bloginfo("title"));

  // Use custom from field
  //
  if(get_option(TDOMF_OPTION_FROM_EMAIL)) {

  	// We can modify the "from" field by using the "header" option at the end!
  	//
  	$headers = "MIME-Version: 1.0\n" .
  	           "From: ". get_option(TDOMF_OPTION_FROM_EMAIL) . "\n" .
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
   
   #tdomf_log_message("tdomf_notify_poster_approved: $email");
   
   if(tdomf_check_email_address($email)){

    tdomf_log_message("Attempting to send notification email to $email for approved post $post_id!");
     
    $postdata = get_postdata($post_id);
    $title = $postdata['Title'];

    $subject = sprintf(__("[%s] Your entry \"%s\" has been approved!","tdomf"),get_bloginfo('title'),$title);

    $notify_message = sprintf(__("This is just a quick email to notify you that your post has been approved and published online. You can see it at %s.\r\n\r\n","tdomf"),get_permalink($post_id));
    $notify_message .= __("Best Regards","tdomf")."\r\n";
    $notify_message .= get_bloginfo("title");

    // Use custom from field
    //
    if(get_option(TDOMF_OPTION_FROM_EMAIL)) {
  
      // We can modify the "from" field by using the "header" option at the end!
      //
      $headers = "MIME-Version: 1.0\n" .
                 "From: ". get_option(TDOMF_OPTION_FROM_EMAIL) . "\n" .
                 "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
  
      return @wp_mail($email, $subject, $notify_message, $headers);
    } else {
      return @wp_mail($email, $subject, $notify_message);
    }
   }
   delete_post_meta($post_id, TDOMF_KEY_NOTIFY_EMAIL);
   return $post_id;
}
// Notify Poster of rejected post
//
function tdomf_notify_poster_rejected($post_id) {
   global $wpdb;
   
   #tdomf_log_message("tdomf_notify_poster_rejected: $email");
   
   $email = get_post_meta($post_id, TDOMF_KEY_NOTIFY_EMAIL, true); 
   
   if(tdomf_check_email_address($email)){

    tdomf_log_message("Attempting to send notification email to $email for rejected post $post_id!");
     
    $postdata = get_postdata($post_id);
    $title = $postdata['Title'];

    $subject = sprintf(__("[%s] Your entry \"%s\" has been rejected! :(","tdomf"),get_bloginfo('title'),$title);

    $notify_message = sprintf(__("We are sorry to inform you that your post \"%s\" has been rejected.\r\n\r\n","tdomf"),$title);
    $notify_message .= __("Best Regards","tdomf")."\r\n";
    $notify_message .= get_bloginfo("title");

    // Use custom from field
    //
    if(get_option(TDOMF_OPTION_FROM_EMAIL)) {
  
      // We can modify the "from" field by using the "header" option at the end!
      //
      $headers = "MIME-Version: 1.0\n" .
                 "From: ". get_option(TDOMF_OPTION_FROM_EMAIL) . "\n" .
                 "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
  
      return @wp_mail($email, $subject, $notify_message, $headers);
    } else {
      return @wp_mail($email, $subject, $notify_message);
    }
   }
   delete_post_meta($post_id, TDOMF_KEY_NOTIFY_EMAIL);
   return $post_id;
}
add_action('publish_post', 'tdomf_notify_poster_approved');
add_action('delete_post', 'tdomf_notify_poster_rejected');

////////////////////////////////////////////////////////////////////////////////
//                                             Default Widgets: "Notify Me"   //
////////////////////////////////////////////////////////////////////////////////

// Do we need to display a email input?
//
function tdomf_widget_notifyme_show_email_input(){
  global $current_user;
  get_currentuserinfo();
  $show_email_input = true;
  if(is_user_logged_in() && tdomf_check_email_address($current_user->user_email)) {
    // user has already set a valid email address!
    $show_email_input = false;
  } else { 
    $widgets_in_use = tdomf_get_widget_order();
    if(in_array("who-am-i",$widgets_in_use)) {
      $whoami_options = tdomf_widget_whoami_get_options();
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
  
  // Dont' do anything if the user can already publish or is trusted!
  //
  if(!get_option(TDOMF_OPTION_MODERATION) || current_user_can('publish_posts')){
    return "";
   } else if(is_user_logged_in() && $current_user->ID != get_option(TDOMF_DEFAULT_AUTHOR)) {
     $user_status = get_usermeta($current_user->ID,TDOMF_KEY_STATUS);
     if($user_status == TDOMF_USER_STATUS_TRUSTED) {
       return "";
     }
   }

   extract($args);
   $output = $before_widget;
   
  // Check if values set in cookie
  if(!isset($notifyme_email) && isset($_COOKIE['tdomf_notify_widget_email'])) {
    $notifyme_email = $_COOKIE['tdomf_notify_widget_email'];
  }
  
  $show_email_input = tdomf_widget_notifyme_show_email_input();

  $output .= "<label for='notifyme'><input type='checkbox' name='notifyme' id='notifyme'";
  if(isset($notifyme)) $output .= " checked "; 
  $output .= " /> ".__("Do you wish to be notify when your post is approved (or rejected)?","tdomf")."</label>";
  
  if($show_email_input) {
    $output .=  "<br/><label for='notifyme_email'>".__("Email for notification:","tdomf").' <input type="text" value="'.$notifyme_email.'" name="notifyme_email" id="notifyme_email" size="40" /></label>';
  }
  
  $output .= $after_widget;
  return $output;
}
tdomf_register_form_widget('Notify Me', 'tdomf_widget_notifyme');

// Widget validate input
//
function tdomf_widget_notifyme_validate($args) {
  extract($args);
  if(tdomf_widget_notifyme_show_email_input()) {
    if(isset($notifyme) && !tdomf_check_email_address($notifyme_email)) {
      return $before_widget.__("You must specify a valid email address to send the notification to.","tdomf").$after_widget;
    }
  }
  return NULL;
}
tdomf_register_form_widget_validate('Notify Me', 'tdomf_widget_notifyme_validate');

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
tdomf_register_form_widget_post('Notify Me', 'tdomf_widget_notifyme_post');

?>
