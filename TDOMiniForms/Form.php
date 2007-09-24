<?php
/* Code for the actual form */

// Can't access get_currentuserinfo via AJAX so must include
// file when not found!
if(!function_exists("get_currentuserinfo")) {
   include_once( ABSPATH . WPINC . "/pluggable.php" );
}

/* Notify Poster */

function tdomf_notify_poster($post_id) {
   global $wpdb;

   $key = TDOMF_NOTIFY.$post_id;
   $email = get_option($key);
   if(!empty($email)){
    $postdata = get_postdata($post_id);
    $title = $postdata['Title'];

    $subject = __("Hi! Your entry \"","tdomf").$title.__("\" has been published","tdomf");
    $notify_message  = __("This is just a quick email to notify you that your post has been approved and published online. You can see it at ","tdomf");
    $notify_message .= get_permalink($post_id)."\r\n\r\n";
    $notify_message .= __("Best Regards","tdomf")."\r\n";
    $notify_message .= get_bloginfo("admin_email");

    @wp_mail($email, $subject, $notify_message);
   }
   delete_option($key);
   return $post_id;
}
function tdomf_delete_notify($post_id) {
   $key = TDOMF_NOTIFY.$post_id;
   delete_option($key);
   return $post_id;
}
add_action('publish_post', 'tdomf_notify_poster');
add_action('delete_post', 'tdomf_delete_notify');

/* Notify Admins */

// Send an email to the admins of a new post awaiting moderation!
function tdomf_notify_admins($title,$content,$notify,$name,$email,$ip,$user,$post_ID){
  global $wpdb;

  $can_ban_user = false;
  if(!empty($name)) {
    $submitter_name = $name;
    if(!empty($email)) {
      $submitter_name .= " ($email)";
    }
  } else if($user->ID != get_option(TDOMF_DEFAULT_AUTHOR)) {
    $submitter_name = __("User ","tdomf").$user->user_login;
    $user_status = get_usermeta($user->ID,TDOMF_KEY_STATUS);
    $can_ban_user = true;
  } else {
    $submitter_name = "N/A";
  }
  
  // construct email!
  $subject = __("Please moderate this new post request from ","tdomf").$submitter_name;
  $notify_message  = __("A new post with title \"","tdomf").$title.__("\" from ","tdomf").$submitter_name;
  $notify_message .= __(" is awaiting your approval.","tdomf")."\r\n\r\n";
  $notify_message .= __("This was submitted from IP ","tdomf").$ip."\r\n\r\n";
  
  $notify_message .= __("You can publish this post by following this link: ","tdomf");
  $notify_message .= get_bloginfo('wpurl')."/wp-admin/edit.php?page=TDOMiniForms/ManageMenu.php&action=publish&post=$post_ID\r\n\r\n";
  $notify_message .= __("You can see all posts awaiting moderation by following this link: ","tdomf");
  $notify_message .= get_bloginfo('wpurl')."/wp-admin/edit.php?page=TDOMiniForms/ManageMenu.php&mode=posts\r\n\r\n";
  if($can_ban_user) {
     $notify_message .= __("Ban ","tdomf").$submitter_name.__(": ","tdomf");
     $notify_message .= get_bloginfo('wpurl')."/wp-admin/edit.php?page=TDOMiniForms/ManageMenu.php&action=ban&user=$user->ID\r\n\r\n";
     $notify_message .= __("Trust ","tdomf").$submitter_name.__(": ","tdomf");
     $notify_message .= get_bloginfo('wpurl')."/wp-admin/edit.php?page=TDOMiniForms/ManageMenu.php&action=trust&user=$user->ID\r\n\r\n";
  }
  $notify_message .= __("Ban ","tdomf").$ip.__(": ","tdomf");
  $notify_message .= get_bloginfo('wpurl')."/wp-admin/edit.php?page=TDOMiniForms/ManageMenu.php&action=ban&ip=$ip\r\n\r\n";
  $notify_message .= __("To moderate and edit this post directly, please visit ","tdomf");
  $notify_message .= get_bloginfo('wpurl')."/wp-admin/post.php?action=edit&post=".$post_ID."\r\n\r\n";
  
  // Add content
  $notify_message .= __("Content of the post: ","tdomf")."\r\n\r\n $content \r\n\r\n";
  
  // grab email addresses
  $email_list = "";
  $level_key = $wpdb->prefix . 'user_level';
  $notify_level = get_option(TDOMF_NOTIFY_LEVEL);
  $notify_roles = get_option(TDOMF_NOTIFY_ROLES);
  // support v0.5 and older and grab users @ notify level
  if($notify_level != false) {
     $query = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '$level_key' AND meta_value ='$notify_level'";
     $admin_ids = $wpdb->get_col( $query );
     foreach($admin_ids as $id) {
        $u = get_userdata($id);
        if(!empty($u->user_email)) {
           $email_list .= $u->user_email.", ";
        }
     }
  // if roles set, grab all users in those roles
  } else if($notify_roles != false) {
     if($notify_roles != false) {
        $users = tdomf_get_all_users();
        $notify_roles = explode(';',$notify_roles);
        foreach($users as $user) {
           $user = get_userdata($user->ID);
           if(!empty($user->user_email)) {
              foreach($notify_roles as $role) {
                 if(!empty($role) && isset($user->{$wpdb->prefix.'capabilities'}[strtolower($role)])){
                    $email_list .= $user->user_email.", ";
                    break;
                 }
              }
           }
        }
     }  
  }
  
  if(!empty($email_list)) {
    return @wp_mail($email_list, $subject, $notify_message);
  } else {
    return false;
  }
  return true;
}

/* Post Management */

// AJAX function to submit content!
function tdomf_ajax_send_post($title, $content, $notify, $email, $name, $web){
  global $current_user, $userdata;
  
  // repeat user and ip tests before actually submitting!

  // does user have permission to do this?
  get_currentuserinfo();
  $user_status = get_usermeta($current_user->ID,TDOMF_KEY_STATUS);

    // is user banned
  if(is_user_logged_in() && $user_status == "Banned"){
    return __("<b>ERROR</b>. You are currently banned from submitting using this form!","tdomf");
  }
  
  // grab and test ip
  $ip =  $_SERVER['REMOTE_ADDR'];
  $banned_ips = get_option(TDOMF_BANNED_IPS);
  if($banned_ips != false) {
    $banned_ips = split(";",$banned_ips);
    foreach($banned_ips as $banned_ip) {
      if($banned_ip == $ip) {
        // ip not permitted to to this!
        return __("<b>ERROR</b>. Your IP does not currently have permissions to use this form.","tdomf");
      }
    }
  }
  
  // support version 0.5 and earlier
  if(get_option(TDOMF_ACCESS_LEVEL != false)) {
     $permissable_level = (int)get_option(TDOMF_ACCESS_LEVEL);
     // only need to do this check if not everyone can submit
     if($permissable_level >= 0) {
        if(!is_user_logged_in() || $current_user->user_level < $permissable_level){
           // user is not permitted to use this form!
           return __("<b>ERROR</b>. You do not currently have permissions to submit!","tdomf");
        }
     }
  // use roles
  } else if(!current_user_can("publish_posts")){
     $permissable_roles = get_option(TDOMF_ACCESS_ROLES);
     // only need to do this check if not everyone can submit
     if($permissable_roles != false) {
        $permissable_roles = explode(';',$permissable_roles);
        $okay_role = false;
        if(!empty($permissable_roles)) {
              foreach($permissable_roles as $role) {
                 if(!empty($role) && current_user_can(strtolower($role))) {
                    $okay_role = true;
                    break;
                 }
              }
        }
        if($okay_role == false) {
              // user is not permitted to use this form!
              return __("<b>ERROR</b>. You do not currently have permissions to submit!","tdomf");
        }
     }
  }
  
  // default author or current user?
  if(!is_user_logged_in()) {
     $user_id = get_option(TDOMF_DEFAULT_AUTHOR);
     $user = get_userdata($user_id);
  } else {
     $user = $current_user;
  }

  // get notify value
  if($notify == "true") { $notify = true; }
  else { $notify = false; }
  
  return tdomf_add_post($title,$content,$notify,$name,$email,$web,$ip,$user);
}
sajax_export("tdomf_ajax_send_post");

// Add's the post to the database
function tdomf_add_post($title,$content,$notify,$name,$email,$web,$ip,$user){
   global $wp_rewrite;

   // Need to do this when this function is called via AJAX process
   $wp_rewrite = new WP_Rewrite();

   // just in case
   preg_replace('|<!--tdomf_form1-->|', '', $content);

   // can the user post?
   $post_status = 'draft';
   // if set to default author, do not automatically publish
   if($user->ID != get_option(TDOMF_DEFAULT_AUTHOR)) {
     $testuser = new WP_User($user->ID,$user->user_login);
     $user_status = get_usermeta($user->ID,TDOMF_KEY_STATUS);
     if($testuser->has_cap('publish_posts') || $user_status == "Trusted") {
        $post_status = 'publish';
     }
   }
   
   // grab default cat
   $post_cats = array(get_option(TDOMF_DEFAULT_CATEGORY));

   // build post and post it
   $post = array (
	   "post_content"   => $content,
	   "post_excerpt"   => "",
	   "post_title"     => $title,
	   "post_category"  => $post_cats,
	   "post_author"    => $user->ID,
	   "post_status"    => $post_status,
	   "post_name"      => "",
	   "post_date"      => "",
	   "comment_status" => "",
	   "ping_status"    => ""
   );
   $post_ID = wp_insert_post($post);

   // flag this post!
   add_post_meta($post_ID, TDOMF_KEY_FLAG, true, true);
  
   // Add other meta data as required
   if(!empty($name)) {
     add_post_meta($post_ID, TDOMF_KEY_NAME, $name, true);
   }
   if(!empty($email)) {
     add_post_meta($post_ID, TDOMF_KEY_EMAIL, $email, true);
   }
   if(!empty($web)) {
     add_post_meta($post_ID, TDOMF_KEY_WEB, $web, true);
   }
   if(!empty($ip)){
     add_post_meta($post_ID, TDOMF_KEY_IP, $ip, true);
   }
   // if user not default
   if($user->ID != get_option(TDOMF_DEFAULT_AUTHOR)){
     add_post_meta($post_ID, TDOMF_KEY_USER_ID, $user->ID, true);
     add_post_meta($post_ID, TDOMF_KEY_USER_NAME, $user->user_login, true);
     update_usermeta($user->ID, TDOMF_KEY_FLAG, true);
   }

   $message = "";
   if($post_status == 'draft')
   {
      $admins = "<a href=\"mailto:".get_bloginfo('admin_email')."\">".__("admins","tdomf")."</a>"; 
     
      $message .= __("Your post \"","tdomf").$title.__("\" has been accepted and is now in our moderation queue. ","tdomf");
      $message .= __("It should appear in the next few days. If it doesn't please contact the ","tdomf").$admins.__(". ");
      $message .= __("Thank you for using this service.","tdomf")."<br/><br/>";

      // email admins about it as they have to approve and publish it
      tdomf_notify_admins($title,$content,$notify,$name,$email,$ip,$user,$post_ID);

      if($notify == true && !empty($email)) {
         // make a note of user and post
         $key = TDOMF_NOTIFY.$post_ID;
         update_option($key,$email);
         $message .= __("You will be notified at ","tdomf").$email.__(" when your request is approved.","tdomf")."<br/><br/>";
      }

   } else {
      $message .= __("Your post \"","tdomf").$title.__("\" has been automatically been published. ","tdomf");
      $message .= __('You can see it ',"tdomf").'<a href="'.get_permalink($post_ID).'">'.__("here. ","tdomf").'</a>. ';
      $message .= __("Thank you for using this service.","tdomf")."<br/><br/>";
   }

   return $message;
}

/* Generate Form */

function tdomf_get_form(){
  global $current_user,$tdomf_ajax_progress_icon;

  get_currentuserinfo();
  
  $admins = "<a href=\"mailto:".get_bloginfo('admin_email')."\">".__("admins","tdomf")."</a>";
  
  // can current user modify form?
  if(is_user_logged_in()) {
     $user_status = get_usermeta($current_user->ID,TDOMF_KEY_STATUS);
     if($user_status == "Banned") {
        return __("You are banned from using this form. If this is an error please contact ","tdomf").$admins;
     }
  }
  // are you banned via your ip?
  $ip =  $_SERVER['REMOTE_ADDR'];
  $banned_ips = get_option(TDOMF_BANNED_IPS);
  if($banned_ips != false) {
    $banned_ips = split(";",$banned_ips);
    foreach($banned_ips as $banned_ip) {
      if($banned_ip == $ip) {
        // can't access this form!
        return __("Your IP does not currently have permissions to use this form. If this is an error please contact ","tdomf").$admins;
      }
    }
  }

  // support version 0.5 and earlier
  if(get_option(TDOMF_ACCESS_LEVEL != false)) {
        $permissable_level = (int)get_option(TDOMF_ACCESS_LEVEL);
        if($permissable_level >= 0) {
           if(!is_user_logged_in() || $current_user->user_level < $permissable_level){
              // user is not permitted to use this form!
              return __("You do not currently have permissions to use this form. If this is an error please contact ","tdomf").$admins;
           }
        }
  // use roles
  } else if(!current_user_can("publish_posts")){
     $permissable_roles = get_option(TDOMF_ACCESS_ROLES);
     // only need to do this check if not everyone can submit
     if($permissable_roles != false) {
        $permissable_roles = explode(';',$permissable_roles);
        $okay_role = false;
        if(!empty($permissable_roles)) {
              foreach($permissable_roles as $role) {
                 if(!empty($role) && current_user_can(strtolower($role))) {
                    $okay_role = true;
                    break;
                 }
              }
        }
        if($okay_role == false) {
              // user is not permitted to use this form!
              return __("You do not currently have permissions to use this form. If this is an error please contact ","tdomf").$admins;
        }
     }
  }
  
  // get email and user info
  $default_email = "";
  $who_are_you = "";
  if(is_user_logged_in()) {
    $default_email = $current_user->user_email;
    $who_are_you = "<p>(".__("You are logged in as ","tdomf").$current_user->display_name.")</p>";
  } else {
    $our_uri = $_SERVER['REQUEST_URI'];
    $login_uri = get_bloginfo('wpurl').'/wp-login.php?redirect_to='.$our_uri;
    $reg_uri = get_bloginfo('wpurl').'/wp-register.php?redirect_to='.$our_uri;
    $who_are_you  = "<p>";
    $who_are_you .= __("We do not know who you are. Please supply your name and email address. Alternatively you can ","tdomf");
    $who_are_you .= "<a href=\"$login_uri\">".__("log in","tdomf")."</a>".__(" if you have a user account or ","tdomf");
    $who_are_you .= "<a href=\"$reg_uri\">".__("register","tdomf")."</a>".__(" for a user account if you do not have one.","tdomf");
    $who_are_you .= "<br/><br/>\n<b>".__("Name:","tdomf").'</b> <input type="text" value="" name="tdomf_form1_name" id="tdomf_form1_name" /><br/><br/>';
    $who_are_you .= "<b>".__("Email: ","tdomf")."</b> <input type=\"text\" size=\"40\" value=\"".$default_email."\" name=\"tdomf_form1_email\" id=\"tdomf_form1_email\" /><br/><br/>";
    $who_are_you .= "<b>".__("Webpage: ","tdomf")."</b> <input type=\"text\" size=\"40\" value=\"http://\" name=\"tdomf_form1_web\" id=\"tdomf_form1_web\" /></p>";
  }
  
  // do you want to be notified?
  $do_you_want_to_be_notified = ""; 
  if(is_user_logged_in() && !current_user_can('publish_posts') && $user_status != "Trusted") {
    $do_you_want_to_be_notified = "<p>";
    $do_you_want_to_be_notified .= '<input type="checkbox" name="tdomf_form1_notify" id="tdomf_form1_notify" checked>';
    $do_you_want_to_be_notified .=  __("Do you wish to be notified when your post has been published?","tdomf");
    $do_you_want_to_be_notified .= "<br/><br/><b>".__("Email: ","tdomf")."</b>"; 
    $do_you_want_to_be_notified .= '<input type="text" size="40" value="'.$default_email.'" name="tdomf_form1_email" id="tdomf_form1_email" /></p>';
  } else if(!is_user_logged_in()) {
    $do_you_want_to_be_notified = "<p>";
    $do_you_want_to_be_notified .= '<input type="checkbox" name="tdomf_form1_notify" id="tdomf_form1_notify" checked>';
    $do_you_want_to_be_notified .= __("Do you wish to be notified when your post has been published?","tdomf");
    $do_you_want_to_be_notified .= "</p>";
  }
  
  $allowed_tags = __("Allowed HTML Tags: ","tdomf").allowed_tags();
  $title_title = __("Title: ","tdomf");
  $title_text = __("Text: ","tdomf");
  $send_text = __("Send","tdomf");
  $clear_text = __("Clear","tdomf");
  
  $the_form = tdomf_get_form_js();
  $the_form .= <<<EOT
  
  <div id="tdomf_form1_msg_div"></div>
  <div id="tdomf_form1_div">
  
  <p>
  <b>$title_title</b>
    <input type="text" name="tdomf_form1_title" id="tdomf_form1_title" size="30" value="" id="title" />
  </p>

  <p>
    <b>$title_text</b><br/>
    <small>$allowed_tags</small><br/>
    <textarea title="true" rows="10" cols="40" name="tdomf_form1_content" id="tdomf_form1_content" ></textarea>
  </p>

  $do_you_want_to_be_notified

  $who_are_you
  
   <table border="0" align="left"><tr>
   <td width="10px"><input type="button" value="$clear_text" id="tdomf_form1_clear_but" onclick="tdomf_clear_form(); return false;" /></td>
    <td width="10px"><input type="button" value="$send_text" id="tdomf_form1_send_but" onclick="tdomf_send_form(); return false;" /></td>
  </tr></table>
</div>
EOT;

  return $the_form;
}

// javascript for form
function tdomf_get_form_js() {
  global $tdomf_ajax_progress_icon;
  
  $sajax_js = sajax_get_javascript();
  $title_warning = __("You must specify a title.","tdomf");
  $text_warning = __("You must add some text.","tdomf");
  $name_warning = __("You must specify a name so we know who it comes from.","tdomf");
  $email_warning = __("The email you specified is invalid.","tdomf"); 
  
  $header = <<<EOT
  <script type="text/javascript">
  <!--
  
  $sajax_js
  
  // DHTML email validation script. Courtesy of SmartWebby.com (http://www.smartwebby.com/dhtml/)
  function echeck(str) {
    var at="@"
    var dot="."
    var lat=str.indexOf(at)
    var lstr=str.length
		var ldot=str.indexOf(dot)
		if (str.indexOf(at)==-1){
		   //alert("Invalid E-mail ID")
		   return false
		}
		if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr){
		   //alert("Invalid E-mail ID")
		   return false
		}
		if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr){
		    //alert("Invalid E-mail ID")
		    return false
		}
		 if (str.indexOf(at,(lat+1))!=-1){
		    //alert("Invalid E-mail ID")
		    return false
		 }
		 if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){
		    //alert("Invalid E-mail ID")
		    return false
		 }
		 if (str.indexOf(dot,(lat+2))==-1){
		    //alert("Invalid E-mail ID")
		    return false
		 }
		 if (str.indexOf(" ")!=-1){
		    //alert("Invalid E-mail ID")
		    return false
		 }
     return true					
  }
  function tdomf_clear_form() {
    document.getElementById("tdomf_form1_title").value = "";
    document.getElementById("tdomf_form1_content").value = "";
    var e1 = document.getElementById("tdomf_form1_notify");
    if(e1 != null) { e1.checked = true; }
    var e2 = document.getElementById("tdomf_form1_email");
    if(e2 != null) { e2.value = ""; }
    var e3 = document.getElementById("tdomf_form1_name");
    if(e3 != null) { e3.value = ""; }
    document.getElementById("tdomf_form1_msg_div").innerHTML = "";
    document.getElementById("tdomf_form1_div").style.display = 'block';
  }
  function tdomf_send_form_cb(message) {
    document.getElementById("tdomf_form1_msg_div").innerHTML = message;
  }
  function tdomf_send_form() {
    var message = document.getElementById("tdomf_form1_msg_div");
    
    var title = document.getElementById("tdomf_form1_title").value;
    var content = document.getElementById("tdomf_form1_content").value;

    var error = "";
    if(title.length == 0){ error += "$title_warning<br/>"; }
    if(content.length == 0){ error += "$text_warning<br/>"; }
    
    var name = "";
    var e1 = document.getElementById("tdomf_form1_name");
    if(e1 != null){ 
      name = e1.value; 
      if(name.length == 0){ 
        error += "$name_warning<br/>"; 
      }
    }
    var email = "";
    var e2 = document.getElementById("tdomf_form1_email");
    if(e2 != null){ 
      email = e2.value; 
      if(echeck(email) == false){ 
        error += "$email_warning<br/>"; 
      }
    }
    
    var web = "";
    var e3 = document.getElementById("tdomf_form1_web");
    if(e3 != null){
      web = e3.value;
    }
    
    var notify = false;
    var e3 = document.getElementById("tdomf_form1_notify");
    if(e3 != null){ notify = e3.checked; }
    
    if(error.length > 0) {
      message.innerHTML = '<font color="red">' + error + '</font>';
    } else {
      var theform = document.getElementById("tdomf_form1_div");
      theform.style.display = 'none';
      message.innerHTML = '<center><img src="$tdomf_ajax_progress_icon" /></center>';
      x_tdomf_ajax_send_post(title, content, notify, email, name, web, tdomf_send_form_cb);
    }
  }
  //-->
  </script>
  
EOT;
  return $header;
}

/* Finally add the form! */

// You can use this as a template function
function tdomf_show_form(){
  echo tdomf_get_form();
}

// Replaces <!--tdomf_form1--> with actual form!
function tdomf_form_filter($content=''){
   if (('' == $content) || (! preg_match('|<!--tdomf_form1-->|', $content))) { return $content; }
   $the_form = tdomf_get_form();
   return preg_replace('|(<p>)(\n)*<!--tdomf_form1-->(\n)*(</p>)|', $the_form, $content);
}
add_filter('the_content', 'tdomf_form_filter');

?>
