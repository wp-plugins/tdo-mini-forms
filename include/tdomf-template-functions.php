<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

///////////////////////////////////////
// Template Tags and other functions //
///////////////////////////////////////

/////////////////////////////////////////////
// Check if current user can access the form!
//
function tdomf_can_current_user_see_form() {
   global $current_user;
   get_currentuserinfo();

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
  
  if(get_option(TDOMF_OPTION_ALLOW_EVERYONE) == false) {
  	if(!current_user_can("publish_posts")  && !current_user_can(TDOMF_CAPABILITY_CAN_SEE_FORM)) {
      // User doesn't have capability to see form
      return false;
  	}
  }
  
  // all other conditions passed!
  return true;
}

//////////////////////////////////////
// Get the form 
//
function tdomf_get_the_form() {
  return tdomf_generate_form();
}

//////////////////////////////////////
// Display the Form
//
function tdomf_the_form() {
  echo tdomf_get_the_form();
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
  
   if(get_option(TDOMF_OPTION_MODERATION) 
   && get_post_meta($post_ID,TDOMF_KEY_FLAG,true) 
   && $post->post_status == 'draft') {
     
       $publish_link = get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_mod_posts_menu&action=publish&post=$post_ID";
       $publish_link = wp_nonce_url($publish_link,'tdomf-publish_'.$post_ID);
       
       $delete_link = get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_mod_posts_menu&action=delete&post=$post_ID";
       $delete_link = wp_nonce_url($delete_link,'tdomf-delete_'.$post_ID);
     
       return $content.sprintf(__('<p>[<a href="%s">Approve</a>] [<a href="%s">Reject</a>]</p>',"tdomf"),$publish_link,$delete_link);
   }
   return $content;
}
add_filter('the_content', 'tdomf_content_adminbuttons_filter');

?>
