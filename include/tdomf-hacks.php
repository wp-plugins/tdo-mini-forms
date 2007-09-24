<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

/* Workarounds and hacks required by TDOMF to work */

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
	if (!$_SESSION) session_start();
}
add_action("get_header","tdomf_start_session");
//
// Add session_start to admin menus where we allow logged in users submit!
//
add_action("load-users_page_tdomf_your_submissions","tdomf_start_session");
add_action("load-profile_page_tdomf_your_submissions","tdomf_start_session");


?>
