<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

/////////////////////////////////////////////////////
// Workarounds and hacks required by TDOMF to work // 
/////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////
// There is a "bug" in wordpress if you publish a post using the
// edit menu, the author cannot be a user so if your user is a subscriber,
// it will become the person who published it. This is the only way to
// fix it without hacking the code base. You can avoid using this hack by
// using the modify author tag option.
//
function tdomf_auto_fix_authors() {
  global $wpdb;
  if(get_option(TDOMF_AUTO_FIX_AUTHOR)) {
    // grab posts
    $query = "SELECT ID, post_author, meta_value ";
    $query .= "FROM $wpdb->posts ";
    $query .= "LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
    $query .= "WHERE meta_key = '".TDOMF_KEY_USER_ID."' ";
    $query .= "AND meta_value != post_author ";
    $query .= "ORDER BY ID DESC";
    $posts = $wpdb->get_results( $query );
    if(!empty($posts)) {
      $count = 0;
      foreach($posts as $post) {
        if($post->meta_value != $post->post_author && !empty($post->meta_value) && $post->meta_value > 0 ) {
          $count++;
          tdomf_log_message("Changing author (currently $post->post_author) on post $post->ID to submitter setting $post->meta_value.");
          echo $post->ID.", ";
          $postargs = array (
            "ID"             => $post->ID,
            "post_author"    => $post->meta_value,
          );
          wp_update_post($postargs);
        }
      }
      return $count;
    } else {
      return 0;
    }
  }
  return false;
}
// is this a good place to do it?
add_action("wp_head","tdomf_auto_fix_authors");


/////////////////////////////////////////////////////////////////////////
// Amazingly Wordpress does not use or call session_start so we have to
// do it *before* headers are sent. I just hope it doesn't conflict
// with other plugins, however the code shouldn't call session_start,
// if it's already been started
//
function tdomf_start_session() {
  if(!headers_sent() && !isset($_SESSION)) {
    session_start();
    return;
  } 
  
  if(headers_sent($filename,$linenum) && !isset($_SESSION)) { 
    ?>
      <p><font color="red">
      <b><?php printf(__('TDOMF ERROR: Headers have already been sent in file %s on line %d before <a href="http://www.google.com/search?client=opera&rls=en&q=php+session_start&sourceid=opera&ie=utf-8&oe=utf-8">session_start()</a> could be called.',"tdomf"),$filename,$linenum); ?></b>
      <?php _e('This may be due to...','tdomf'); ?>
      <ul>
        <li><?php _e('Your current wordpress theme inserting HTML before calling the template tag "get_header". This may be as simple as a blank new line. You can confirm this by using the default or classic Wordpress theme and seeing if this error appears. You can also check your theme where it calls "get_header".',"tdomf"); ?></li>
        <li><?php _e("Another plugin inserting HTML before TDOMF's get_header action is activated. You can confirm this by disabling all your other plugins and checking if this error is still reported.","tdomf"); ?></li>
      </ul>
      </font></p>
    <?php 
    tdomf_log_message("Headers are already sent before TDOMF could call session_start in file $filename on line $linenum",TDOMF_LOG_ERROR);
  }
}
add_action("get_header","tdomf_start_session");
//
// Add session_start to admin menus where we allow logged in users to submit!
//
add_action("load-users_page_tdomf_your_submissions","tdomf_start_session");
add_action("load-profile_page_tdomf_your_submissions","tdomf_start_session");

////////////////////////////////////////////////////////////////////////////////
// While you can modify the URL of an attachment to a post, you can't modify
// the URL to the thumbnail (if avaliable). Instead it tries to generate it by
// modifying the basename of the attachment URL and the filename! Bah. So have
// use filters to grab the right thumbnail!
//
function tdomf_upload_attachment_thumb_url($url,$post_ID) {
   $post_ID = intval($post_ID);
   if ( !$post =& get_post( $post_ID ) ) {
      return $url;
   }
   $parent_ID = $post->post_parent;
   $file_ID = $post->menu_order;
   if( !$thumb_path = get_post_meta($parent_ID,TDOMF_KEY_DOWNLOAD_THUMB.$file_ID,true)) {
      return $url;
   }
   return get_bloginfo('wpurl').'/?tdomf_download='.$parent_ID.'&id='.$file_ID.'&thumb';
}
add_filter( 'wp_get_attachment_thumb_url', 'tdomf_upload_attachment_thumb_url', 10, 2);

?>
