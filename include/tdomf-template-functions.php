<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

///////////////////////////////////////
// Template Tags and other functions //
///////////////////////////////////////

/////////////////////////////////////////////
// Check if current user can access the form!
//
function tdomf_can_current_user_see_form($form_id = 1) {
   global $current_user;
   get_currentuserinfo();

   // if using default id
   if(!tdomf_form_exists($form_id) && $form_id == 1){
     $form_id = tdomf_get_first_form_id();
   }
   
   if(is_user_logged_in()) {
       $user_status = get_usermeta($current_user->ID,TDOMF_KEY_STATUS);
       if($user_status == TDOMF_USER_STATUS_BANNED) {
         // User Banned
         return false;
       }
   }

  $ip =  $_SERVER['REMOTE_ADDR'];
  $banned_ips = get_option(TDOMF_BANNED_IPS);
  if($banned_ips != false) {
  	$banned_ips = split(";",$banned_ips);
  	foreach($banned_ips as $banned_ip) {
		if($banned_ip == $ip) {
      // IP banned
      return false;
		}
	 }
  }
  
    if(tdomf_get_option_form(TDOMF_OPTION_ALLOW_EVERYONE,$form_id) == false) {

        if(current_user_can(TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id)) {
            // has cap
            return true;
        }
        
        if(tdomf_get_option_form(TDOMF_OPTION_ALLOW_PUBLISH,$form_id) == true && current_user_can("publish_posts")) {
            // can already publish
            return true;
        }
        
        if(!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        $roles = $wp_roles->role_objects;
        foreach($roles as $role) {
            if($role->name == get_option('default_role')) {
                $def_role = $role->name;
                break;
            }
        }
        if(is_user_logged_in() && get_option('users_can_register') && isset($def_role->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id])) {
            // logged in (and default role + free reg enabled)
            return true;
        }

        $access_caps = tdomf_get_option_form(TDOMF_OPTION_ALLOW_CAPS,$form_id);
        if(is_array($access_caps)) {
            foreach($access_caps as $cap) {
                if(current_user_can($cap)) {
                    // has specific cap
                    return true;
                }
            }
        }

        $allow_users = tdomf_get_option_form(TDOMF_OPTION_ALLOW_USERS,$form_id);           
        if(is_array($allow_users) && is_user_logged_in() && in_array($current_user->ID,$allow_users)) {
            // is one of the specific users
            return true;
        }             
        
        // if get to here => fail
        return false;
    }
  
  // all other conditions passed!
  return true;
}

//////////////////////////////////////
// Get the form 
//
function tdomf_get_the_form($form_id = 1) {
  return tdomf_generate_form($form_id);
}

//////////////////////////////////////
// Display the Form
//
function tdomf_the_form($form_id = 1) {
  echo tdomf_get_the_form($form_id);
}

//////////////////////////////////////
// Get the submitter of the post
//
function tdomf_get_the_submitter($post_id = 0){
  global $post;
  if($post_id == 0 && isset($post)) { $post_id = $post->ID; }
  else if($post_id == 0){ return ""; }

  $flag = get_post_meta($post_id, TDOMF_KEY_FLAG, true);
  if(!empty($flag)) {
     $submitter_user_id = get_post_meta($post_id, TDOMF_KEY_USER_ID, true);
     if(!empty($submitter_user_id) && $submitter_user_id != get_option(TDOMF_DEFAULT_AUTHOR)) {
        $user = get_userdata($submitter_user_id);
        if(isset($user)) {
          $retValue = "";
          // bit of a crappy hack to make sure that if it's only "http://" it isn't printed
          $web_url = trim($user->user_url);
          if(strlen($web_url) < 8 || strpos($web_url, "http://", 0) !== 0 ) {
            $web_url = "";
          }
          if(!empty($web_url)) {
            $retValue .= "<a href=\"$web_url\" rel=\"nofollow\">";
          }
          $retValue .= $user->display_name;
          if(!empty($web_url)) {
            $retValue .= "</a>";
          }
          return $retValue;
        } else {
          #return "{ ERROR: bad submitter id for this post }";
          return "";
        }
     } else {
        $submitter_web = get_post_meta($post_id, TDOMF_KEY_WEB, true);
        $submitter_name = get_post_meta($post_id, TDOMF_KEY_NAME, true);
        if(empty($submitter_name)) {
          #return "{ ERROR: no submitter name set for this post }";
          return "";
        } else {
          $retValue = "";
          $web_url = trim($submitter_web);
          if(strlen($web_url) < 8 || strpos($web_url, "http://") !== 0) {
            $web_url = "";
          }
          if(!empty($web_url)) {
            $retValue .= "<a href=\"$web_url\" rel=\"nofollow\">";
          }
          $retValue .= $submitter_name;
          if(!empty($web_url)) {
            $retValue .= "</a>";
          }
          return $retValue;
        }
     }
  }
  else {
    return "";
  }
}

//////////////////////////////////////
// Display the Submitter of the post
//
function tdomf_the_submitter($post_id = 0){
  echo tdomf_get_the_submitter($post_id);
}

////////////////////////////////////////////////////////////////////////
// Display the email address of the submitter (must be used in the loop)
//
function tdomf_the_submitter_email() {
  echo tdomf_get_the_submitter_email();
}

////////////////////////////////////////////////////////////////////////
// Get the email address of the submitter (must be used in the loop)
//
function tdomf_get_the_submitter_email() {
   global $post, $authordata;
   $email = strtolower(get_the_author_email());
   $flag = get_post_meta($post->ID, TDOMF_KEY_FLAG, true);
   if($flag != false && !empty($flag)) {
     $submitter_user_id = get_post_meta($post->ID, TDOMF_KEY_USER_ID, true);
     if($submitter_user_id != false && !empty($submitter_user_id) && $submitter_user_id != get_option(TDOMF_DEFAULT_AUTHOR)) {
        $submitter_data = get_userdata($submitter_user_id);
        $email = strtolower($submitter_data->user_email);  
     } else {
        $email = strtolower(get_post_meta($post->ID, TDOMF_KEY_EMAIL, true));
     }
   } 
   return $email;
}


//////////////////////////////////////
// Modify the_author template tag with user
//
function tdomf_author_filter($author=''){
   if(get_option(TDOMF_OPTION_AUTHOR_THEME_HACK)) {
	   $submitter = tdomf_get_the_submitter();
	   if($submitter != "") {
		return $submitter;
	   }
   }
   return $author;
}
add_filter('the_author', 'tdomf_author_filter');

//////////////////////////////////////
// Add submitter info to end of content
//
function tdomf_content_submitter_filter($content=''){
   if(get_option(TDOMF_OPTION_ADD_SUBMITTER)) {
	   $submitter = tdomf_get_the_submitter();
	   if($submitter != "") {
		return $content."<p>".sprintf(__("This post was submitted by %s.","tdomf"),$submitter)."</p>";
	   }
   }
   return $content;
}
add_filter('the_content', 'tdomf_content_submitter_filter');

//////////////////////////////////////
// Add TDOMF stylesheet link to template
//
function tdomf_stylesheet(){
   ?>
   <link rel="stylesheet" href="<?php echo TDOMF_URLPATH; ?>tdomf-style-form.css" type="text/css" media="screen" />
   <?php
}
add_action('wp_head','tdomf_stylesheet');

function tdomf_content_adminbuttons_filter($content=''){
  global $post;
  $post_ID = 0;
  if(isset($post)) { $post_ID = $post->ID; }
  else if($post_ID == 0){ return $content; }

  // use some form of the form_id
  $form_id = get_post_meta($post_ID,TDOMF_KEY_FORM_ID,true);
   if($form_id == false || !tdomf_form_exists($form_id)){
     $form_id = tdomf_get_first_form_id();
   }
  
   if(/*tdomf_get_option_form(TDOMF_OPTION_MODERATION,$form_id) 
   &&*/ get_post_meta($post_ID,TDOMF_KEY_FLAG,true) 
   && $post->post_status == 'draft'
   && current_user_can('publish_posts')) {
     
       $output = "<p>";
   
       $queue = intval(tdomf_get_option_form(TDOMF_OPTION_QUEUE_PERIOD,$form_id));
       if($queue > 0) { $queue = true; } else { $queue = false; }
   
       if($queue) {
           $publishnow_link = get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_mod_posts_menu&action=publish&post=$post_ID&nofuture=1";
           $publishnow_link = wp_nonce_url($publishnow_link,'tdomf-publish_'.$post_ID);
       }
       
       $publish_link = get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_mod_posts_menu&action=publish&post=$post_ID";
       $publish_link = wp_nonce_url($publish_link,'tdomf-publish_'.$post_ID);

       $delete_link = get_bloginfo('wpurl')."/wp-admin/post.php?action=delete&post=$post_ID";
       $delete_link = wp_nonce_url($delete_link,'delete-post_'.$post_ID);
       
       if($queue) {
           $output .= sprintf(__('[<a href="%s">Publish Now</a>] [<a href="%s">Add to Queue</a>] [<a href="%s">Delete</a>]',"tdomf"),$publishnow_link, $publish_link,$delete_link);
       } else {
           $output .= sprintf(__('[<a href="%s">Publish</a>] [<a href="%s">Delete</a>]',"tdomf"),$publish_link,$delete_link);
       }
       
       if(get_option(TDOMF_OPTION_SPAM)) {
           $spam_link = get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_mod_posts_menu&action=spamit&post=$post_ID";
           $spam_link = wp_nonce_url($spam_link,'tdomf-spamit_'.$post_ID);
         
           $ham_link = get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_mod_posts_menu&action=hamit&post=$post_ID";
           $ham_link = wp_nonce_url($ham_link,'tdomf-hamit_'.$post_ID);
           
            if(get_post_meta($post_ID, TDOMF_KEY_SPAM)) {
                 $output .= sprintf(__(' [<a href="%s">Not Spam</a>]',"tdomf"),$ham_link);
            } else {
                 return $content.sprintf(__(' [<a href="%s">Spam</a>]',"tdomf"),$spam_link);
            }
       } 
       
       $output .= '</p>';
       
       return $content.$output;
   }
   return $content;
}
add_filter('the_content', 'tdomf_content_adminbuttons_filter');

//////////////////////////////////////////////////////////
// Is the current user the default user? (error checking)
//
function tdomf_current_user_default_author() {
    global $current_user;
    get_currentuserinfo();
    if(!is_user_logged_in()) { return false; }
    return ($current_user->ID == get_option(TDOMF_DEFAULT_AUTHOR));
}

//////////////////////////////
// Is the current user trusted
//
function tdomf_current_user_trusted() {
    global $current_user;
    get_currentuserinfo();
    if(!is_user_logged_in()) { return false; }
    return (TDOMF_USER_STATUS_TRUSTED == get_usermeta($current_user->ID,TDOMF_KEY_STATUS));
}


?>
