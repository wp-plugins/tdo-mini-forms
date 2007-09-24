<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

/* Template Tags */

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

function tdomf_stylesheet(){
   ?>
   <link rel="stylesheet" href="<?php echo TDOMF_URLPATH; ?>tdomf-style-form.css" type="text/css" media="screen" />
   <?php
}
add_action('wp_head','tdomf_stylesheet');
   
?>
