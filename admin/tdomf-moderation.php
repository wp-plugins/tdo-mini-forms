<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

// make a post draft
//
function tdomf_unpublish_post($post_id) {
   $postargs = array (
     "ID"          => $post_id,
     "post_status" => "draft",
   );
   wp_update_post($postargs);
}


// grab a list of all submitted posts
//
function tdomf_get_submitted_posts($offset = 0, $limit = 0) {
  global $wpdb;
	$query = "SELECT ID, post_title, meta_value, post_status ";
	$query .= "FROM $wpdb->posts ";
	$query .= "LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
   $query .= "WHERE meta_key = '".TDOMF_KEY_FLAG."' ";
	$query .= "ORDER BY ID DESC ";
   if($limit > 0) {
      $query .= "LIMIT $limit ";
   }
   if($offset > 0) {
      $query .= "OFFSET $offset ";
   }
	return $wpdb->get_results( $query );
}

// Return count of submitted posts
//
function tdomf_get_submitted_posts_count() {
  global $wpdb;
	$query = "SELECT count(ID) ";
	$query .= "FROM $wpdb->posts ";
	$query .= "LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
   $query .= "WHERE meta_key = '".TDOMF_KEY_FLAG."' ";
	return intval($wpdb->get_var( $query ));
}

// Grab a list of unmoderated posts
//
function tdomf_get_unmoderated_posts($offset = 0, $limit = 0) {
  global $wpdb;
	$query = "SELECT ID, post_title, meta_value, post_status  ";
	$query .= "FROM $wpdb->posts ";
	$query .= "LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
   $query .= "WHERE meta_key = '".TDOMF_KEY_FLAG."' ";
   $query .= "AND post_status = 'draft' ";
   	$query .= "ORDER BY ID DESC ";
   if($limit > 0) {
         $query .= "LIMIT $limit ";
      }
      if($offset > 0) {
         $query .= "OFFSET $offset ";
   }
	return $wpdb->get_results( $query );
}

// Return a count of unmoderated posts
//
function tdomf_get_unmoderated_posts_count() {
  global $wpdb;
	$query = "SELECT count(ID) ";
	$query .= "FROM $wpdb->posts ";
	$query .= "LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
   $query .= "WHERE meta_key = '".TDOMF_KEY_FLAG."' ";
   $query .= "AND post_status = 'draft' ";
	return intval($wpdb->get_var( $query ));
}

// Grab a list of published submitted posts
//
function tdomf_get_published_posts($offset = 0, $limit = 0) {
  global $wpdb;
	$query = "SELECT ID, post_title, meta_value, post_status  ";
	$query .= "FROM $wpdb->posts ";
	$query .= "LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
   $query .= "WHERE meta_key = '".TDOMF_KEY_FLAG."' ";
   $query .= "AND post_status = 'publish' ";
   	$query .= "ORDER BY ID DESC ";
   if($limit > 0) {
         $query .= "LIMIT $limit ";
      }
      if($offset > 0) {
         $query .= "OFFSET $offset ";
   }
	return $wpdb->get_results( $query );
}

// Return a count of pubilshed posts
//
function tdomf_get_published_posts_count() {
  global $wpdb;
	$query = "SELECT count(ID)  ";
	$query .= "FROM $wpdb->posts ";
	$query .= "LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
   $query .= "WHERE meta_key = '".TDOMF_KEY_FLAG."' ";
   $query .= "AND post_status = 'publish' ";
	return intval($wpdb->get_var( $query ));
}

// Show the moderation menu
//
function tdomf_show_mod_posts_menu() {
   if(!get_option(TDOMF_OPTION_MODERATION)) { ?>
   <div class="wrap">
       <h2><?php printf(__('Moderation Disabled', 'tdomf'),$limit); ?></h2>
       <p><center><b><?php printf(__('Moderation is currently disabled. You can enable it on the <a href="%s">options</a> page.',"tdomf"),"admin.php?page=tdomf_show_options_menu"); ?></b></center></p>
   </div>
   <?php
      return;
   }

   tdomf_moderation_handler();

   $limit = 15;
   if(isset($_REQUEST['limit'])){ $limit = intval($_REQUEST['limit']); }
   $offset = 0;
   if(isset($_REQUEST['offset'])){ $offset = intval($_REQUEST['offset']); }

   if(isset($_REQUEST['f']) && $_REQUEST['f'] == "1") {
      $posts = tdomf_get_published_posts($offset,$limit);
      $max = tdomf_get_published_posts_count();
   } else if(isset($_REQUEST['f']) && $_REQUEST['f'] == "2") {
      $posts = tdomf_get_submitted_posts($offset,$limit);
      $max = tdomf_get_submitted_posts_count();
   } else {
   	$posts = tdomf_get_unmoderated_posts($offset,$limit);
   	$max = tdomf_get_unmoderated_posts_count();
   }

   ?>

   <div class="wrap">

   <?php if(isset($_REQUEST['f']) && $_REQUEST['f'] == "1") { ?>
       <h2><?php if($offset > 0) { _e("Previous Published Submissions","tdomf"); }
               else { printf(__('Last %d Published Submissions', 'tdomf'),$limit); } ?></h2>
   <?php } else if(isset($_REQUEST['f']) && $_REQUEST['f'] == "2") { ?>
       <h2><?php if($offset > 0) { _e("Previous Submissions","tdomf"); }
               else { printf(__('Last %d Submissions', 'tdomf'),$limit); } ?></h2>
   <?php } else { ?>
       <h2><?php if($offset > 0) { _e("Previous Unmoderated Submissions","tdomf"); }
            else { printf(__('Last %d Unmoderated Submissions', 'tdomf'),$limit); } ?></h2>
   <?php } ?>

    <p><?php _e("From here you can publish, unpublish or delete any submitted post.","tdomf"); ?></p>

   <form method="post" action="admin.php?page=tdomf_show_mod_posts_menu" id="filterposts" name="filterposts" >
   <fieldset>
	  <b><?php _e("Filter Posts","tdomf"); ?></b>
      <select name="f">
      <option value="0" <?php if(!isset($_REQUEST['f']) || (isset($_REQUEST['f']) && $_REQUEST['f'] == "0")){ ?> selected <?php } ?>><?php _e("Unpublished (Awaiting approval)","tdomf"); ?>
        <option value="1" <?php if(isset($_REQUEST['f']) && $_REQUEST['f'] == "1"){ ?> selected <?php } ?>><?php _e("Published","tdomf"); ?>
        <option value="2" <?php if(isset($_REQUEST['f']) && $_REQUEST['f'] == "2"){ ?> selected <?php } ?>><?php _e("All","tdomf"); ?>
      </select>
      <input type="submit" name="submit" value="Show" />
   </fieldset>
   </form>

   <br/>

   <?php if(count($posts) <= 0) { _e("There are no posts to moderate.","tdomf"); }
         else { ?>


<script type="text/javascript">
<!--
function checkAll(form)
{
	for (i = 0, n = form.elements.length; i < n; i++) {
		if(form.elements[i].type == "checkbox") {
			if(form.elements[i].checked == true)
				form.elements[i].checked = false;
			else
				form.elements[i].checked = true;
		}
	}
}

function getNumChecked(form)
{
	var num = 0;
	for (i = 0, n = form.elements.length; i < n; i++) {
		if(form.elements[i].type == "checkbox") {
			if(form.elements[i].checked == true)
				num++;
		}
	}
	return num;
}
//-->
</script>

   <form method="post" action="admin.php?page=tdomf_show_mod_posts_menu" id="moderateposts" name="moderateposts" >

   <table class="widefat">
   <tr>
    <th scope="col" style="text-align: center"><input type="checkbox" onclick="checkAll(document.getElementById('moderateposts'));" /></th>
    <th scope="col"><?php _e("ID","tdomf"); ?></th>
    <th scope="col"><?php _e("Title","tdomf"); ?></th>
    <th scope="col"><?php _e("Submitter","tdomf"); ?></th>
    <th scope="col"><?php _e("IP","tdomf"); ?></th>
    <th scope="col"><?php _e("Status","tdomf"); ?></th>
    <th scope="col" colspan="4" style="text-align: center">Actions</th>
   </tr>

   <?php $i = 0;
         foreach($posts as $p) {
         $i++;
		 if(($i%2) == 0) { ?>
		  <tr id='x' class=''>
	     <?php } else { ?>
		  <tr id='x' class='alternate'>
         <?php } ?>

               <td><input type="checkbox" name="moderateposts[]" value="<?php echo $p->ID; ?>" /></td>
               <th scope="row"><?php echo $p->ID; ?></th>
		       <td><?php echo $p->post_title; ?></td>
		       <td>
		       <?php $name = get_post_meta($p->ID, TDOMF_KEY_NAME, true);
		             $email = get_post_meta($p->ID, TDOMF_KEY_EMAIL, true);
		             $user_id = get_post_meta($p->ID, TDOMF_KEY_USER_ID, true);
		             if($user_id != false) { ?>
		               <a href="user-edit.php?user_id=<?php echo $user_id;?>" class="edit">
		               <?php $u = get_userdata($user_id);
		                echo $u->user_login; ?></a>
		             <?php } else {
		                echo $name." (".$email.")";
		             } ?>
		       </td>
		       <td>
		             <?php echo get_post_meta($p->ID, TDOMF_KEY_IP, true); ?>
		       </td>
		       <td><?php _e($p->post_status,"tdomf"); ?></td>
		       <td><a href="<?php echo get_permalink($p->ID); ?>" class="edit"><?php _e("View","tdomf"); ?></a></td>

		       <td>

		       <?php if(isset($_REQUEST['f'])) { $farg = "&f=".$_REQUEST['f']; } ?>

		       <?php if($p->post_status == "draft") { ?>
		          <a href="<?php echo wp_nonce_url("admin.php?page=tdomf_show_mod_posts_menu&action=publish&post=$p->ID$farg&offset=$offset&limit=$limit",'tdomf-publish_'.$p->ID); ?>" class="publish"><?php _e("Publish","tdomf"); ?></a>
		       <?php } else { ?>
		          <a href="<?php echo wp_nonce_url("admin.php?page=tdomf_show_mod_posts_menu&action=unpublish&post=$p->ID$farg&offset=$offset&limit=$limit",'tdomf-unpublish_'.$p->ID); ?>" class="draft"><?php _e("Un-Publish","tdomf"); ?></a>
		       <?php } ?>
		       </td>

		       <td><a href="post.php?action=edit&post=<?php echo $p->ID ?>" class="edit"><?php _e("Edit","tdomf"); ?></a></td>
		       <td><a href="<?php echo wp_nonce_url("post.php?action=delete&amp;post=$p->ID", 'delete-post_' . $p->ID); ?>" class='delete' ><?php _e("Delete","tdomf"); ?></a></td>

           </tr>

         <?php } ?>

   </table>

   <?php $farg = "0"; if(isset($_REQUEST['f'])) { $farg = $_REQUEST['f']; } ?>

   <input type="hidden" name="limit" id="limit" value="<?php echo $limit; ?>" />
   <input type="hidden" name="offset" id="offset" value="<?php echo $offset; ?>" />
   <input type="hidden" name="f" id="f" value="<?php echo $farg; ?>" />

   <p class="submit">
    <input type="submit" name="delete_button" class="delete" value="<?php _e("Delete Checked Posts &raquo;"); ?>" onclick="var numchecked = getNumChecked(document.getElementById('moderateposts')); if(numchecked < 1) { alert('Please select some posts to delete'); return false } return confirm('You are about to delete ' + numchecked + ' posts permanently \n  \'Cancel\' to stop, \'OK\' to delete.')" />
    <?php if(!isset($_REQUEST['f']) || $_REQUEST['f'] == '0' || $_REQUEST['f'] == '2') { ?>
	<input type="submit" name="publish_button" value="<?php _e("Publish Checked Posts &raquo;"); ?>" onclick="var numchecked = getNumChecked(document.getElementById('moderateposts')); if(numchecked < 1) { alert('Please select some posts to publish'); return false } return confirm('You are about to publish ' + numchecked + ' posts \n  \'Cancel\' to stop, \'OK\' to publish')" />
	<?php } ?>
	<?php if(isset($_REQUEST['f']) && ($_REQUEST['f'] == '1' || $_REQUEST['f'] == '2')) { ?>
	<input type="submit" name="unpublish_button" value="<?php _e("Un-Publish Checked Posts &raquo;"); ?>" onclick="var numchecked = getNumChecked(document.getElementById('moderateposts')); if(numchecked < 1) { alert('Please select some posts to publish'); return false } return confirm('You are about to un-publish ' + numchecked + ' posts \n  \'Cancel\' to stop, \'OK\' to publish')" />
	<?php } ?>
	<?php if(function_exists('wp_nonce_field')){ wp_nonce_field('tdomf-moderate-bulk'); } ?>
   </p>

   </form>

   <br/><br/>

   <div class="navigation">
   <?php if(($max - ($offset + $limit)) > 0 ) { ?>
      <div class="alignleft"><a href="admin.php?page=tdomf_show_mod_posts_menu&offset=<?php echo $offset + $limit; ?><?php if(isset($_REQUEST['f'])) { echo "&f=".$_REQUEST['f']; } ?>">&laquo; <?php _e("Previous Entries","tdomf"); ?></a></div>
   <?php } ?>

   <?php if($offset > 0){ ?>
      <div class="alignright"><a href="admin.php?page=tdomf_show_mod_posts_menu&offset=<?php echo $offset - $limit; ?><?php if(isset($_REQUEST['f'])) { echo "&f=".$_REQUEST['f']; } ?>"><?php _e("Next Entries","tdomf"); ?> &raquo;</a></div>
   <?php } ?>
   </div>

   <br/><br/>

   <?php } ?>

   </div> <!-- wrap -->

   <?php

}

// Handle operations for this form
//
function tdomf_moderation_handler() {
   $message = "";

   if(isset($_REQUEST['delete_button'])) {
      check_admin_referer('tdomf-moderate-bulk');
      $posts = $_REQUEST['moderateposts'];
      $list = "";
      foreach($posts as $p) {
         wp_delete_post($p);
         $list .= $p.",";
      }
      tdomf_log_message("Deleted $list posts");
      $message = sprintf(__("Deleted posts: %s","tdomf"),$list);
   } else if(isset($_REQUEST['publish_button'])) {
      check_admin_referer('tdomf-moderate-bulk');
      $posts = $_REQUEST['moderateposts'];
      $list = "";
      foreach($posts as $p) {
         wp_publish_post($p);
         $list .= "<a href=\"".get_permalink($p)."\">".$p."</a>,";
      }
      tdomf_log_message("Published $list posts");
      $message = sprintf(__("Published posts: %s","tdomf"),$list);
   } else if(isset($_REQUEST['unpublish_button'])) {
      check_admin_referer('tdomf-moderate-bulk');
      $posts = $_REQUEST['moderateposts'];
      $list = "";
      foreach($posts as $p) {
         tdomf_unpublish_post($p);
         $list .= $p.",";
      }
      tdomf_log_message("Unpublished $list posts");
      $message = sprintf(__("Unpublished posts: %s","tdomf"),$list);
   } else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'publish') {
      $post_id = $_REQUEST['post'];
      check_admin_referer('tdomf-publish_'.$post_id);
      wp_publish_post($post_id);
      tdomf_log_message("Published post $post_id");
      $message = sprintf(__("Published post <a href=\"%s\">%d</a>.","tdomf"),get_permalink($post_id),$post_id);
   } else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'unpublish') {
      $post_id = $_REQUEST['post'];
      check_admin_referer('tdomf-unpublish_'.$post_id);
      tdomf_unpublish_post($post_id);
      tdomf_log_message("Unpublished post $post_id");
      $message = sprintf(__("Unpublished post %d.","tdomf"),$post_id);
   }

   if(!empty($message)) { ?>
      <div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
   <?php }
}

?>
