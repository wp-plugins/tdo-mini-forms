<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

/////////////////////////
// Moderate Posts page //
/////////////////////////

function tdomf_get_queued_posts($offset = 0, $limit = 0) {
  global $wpdb;
	$query = "SELECT ID, post_title, post_status ";
	$query .= "FROM $wpdb->posts ";
	$query .= "LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
    $query .= "WHERE meta_key = '".TDOMF_KEY_FLAG."' ";
       $query .= "AND post_status = 'future' ";
 	$query .= "ORDER BY ID DESC ";
   if($limit > 0) {
      $query .= "LIMIT $limit ";
   }
   if($offset > 0) {
      $query .= "OFFSET $offset ";
   }
	return $wpdb->get_results( $query );
}

function tdomf_get_queued_posts_count($offset = 0, $limit = 0) {
  global $wpdb;
	$query = "SELECT ID, post_title, post_status ";
	$query .= "FROM $wpdb->posts ";
	$query .= "LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
    $query .= "WHERE meta_key = '".TDOMF_KEY_FLAG."' ";
       $query .= "AND post_status = 'future' ";
	return intval($wpdb->get_var( $query ));
}

function tdomf_get_spam_posts($offset = 0, $limit = 0) {
   global $wpdb;
   $query = "SELECT ID, post_title, meta_value, post_status ";
   $query .= "FROM $wpdb->posts ";
   $query .= "LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
   $query .= "WHERE meta_key = '".TDOMF_KEY_SPAM."' ";
   $query .= "ORDER BY ID DESC ";
   if($limit > 0) {
      $query .= "LIMIT $limit ";
   }
   if($offset > 0) {
      $query .= "OFFSET $offset ";
   }
	return $wpdb->get_results( $query );
}

function tdomf_get_spam_posts_count() {
   global $wpdb;
   $query = "SELECT count(ID) ";
   $query .= "FROM $wpdb->posts ";
   $query .= "LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
   $query .= "WHERE meta_key = '".TDOMF_KEY_SPAM."' ";
   return intval($wpdb->get_var( $query ));
}


// make a post draft
//
function tdomf_unpublish_post($post_id) {
   $postargs = array (
     "ID"          => $post_id,
     "post_status" => "draft",
   );
   wp_update_post($postargs);
}

// publish a post
//
function tdomf_publish_post($post_ID,$use_queue=true) {
   $form_id = get_post_meta($post_ID,TDOMF_KEY_FORM_ID,true);
   $current_ts = current_time( 'mysql' );
   $ts = tdomf_queue_date($form_id,$current_ts);
   if($current_ts == $ts || !$use_queue) {
        $post = array (
          "ID"             => $post_ID,
          "post_status"    => 'publish',
          );
    } else {
        tdomf_log_message("Future Post Date = $ts!");
        $post = array (
          "ID"             => $post_ID,
          "post_status"    => 'future',
          "post_date"      => $ts,
          );
    }
     // Use update post instead of publish post because in WP2.3, 
     // update_post doesn't seem to add the date correctly! 
     // Also when it updates a post, if comments aren't set, sets them to
     // empty! (Not so in WP2.2!)
    wp_update_post($post);
    /*wp_publish_post($post_ID);*/
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
  
   /* Using subqueries... only works on newer SQL version, not the minmum 
      supported by WP. Use the second method below */
      
   /*$query = "SELECT ID, post_title, meta_value, post_status  ";
   $query .= "FROM $wpdb->posts ";
   $query .= "LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
   $query .= "WHERE meta_key = '".TDOMF_KEY_FLAG."' ";
   $query .= "AND post_status = 'draft' ";
     $query .= "AND $wpdb->posts.ID NOT IN (SELECT post_id FROM $wpdb->postmeta ";
     $query .=  "WHERE meta_key = '".TDOMF_KEY_SPAM."' ) "; 
   $query .= "ORDER BY ID DESC "; */
  
   $query = "SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->postmeta.meta_value, $wpdb->posts.post_status
             FROM $wpdb->posts 
             LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) 
             LEFT JOIN $wpdb->postmeta tdopm ON $wpdb->posts.id =
                       tdopm.post_id AND tdopm.meta_key ='".TDOMF_KEY_SPAM."' 
             WHERE tdopm.post_id IS NULL AND post_status = 'draft' AND $wpdb->postmeta.meta_key='".TDOMF_KEY_FLAG."'
             ORDER BY $wpdb->posts.ID DESC ";

   if($limit > 0) {
      $query .= "LIMIT $limit ";
   }
   if($offset > 0) {
      $query .= "OFFSET $offset ";
   } 
             
  /*$wpdb->show_errors();*/
  $result = $wpdb->get_results( $query );
  
  /*$query = "SELECT version() ";
  var_dump($wpdb->get_results( $query ));*/
  
  return $result;
}

// Return a count of unmoderated posts
//
function tdomf_get_unmoderated_posts_count() {
    global $wpdb;
    $query = "SELECT count($wpdb->posts.ID)
             FROM $wpdb->posts 
             LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) 
             LEFT JOIN $wpdb->postmeta tdopm ON $wpdb->posts.id =
                       tdopm.post_id AND tdopm.meta_key ='_tdomf_spam_flag' 
             WHERE tdopm.post_id IS NULL AND post_status = 'draft' AND $wpdb->postmeta.meta_key='_tdomf_flag' ";
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
   if(!tdomf_is_moderation_in_use()) { ?>
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
   } else if(isset($_REQUEST['f']) && $_REQUEST['f'] == "3") {
      $posts = tdomf_get_spam_posts($offset,$limit);
      $max = tdomf_get_spam_posts_count();
   } else if(isset($_REQUEST['f']) && $_REQUEST['f'] == "4") {
      $posts = tdomf_get_queued_posts($offset,$limit);
      $max = tdomf_get_queued_posts_count();
   } else {
   	$posts = tdomf_get_unmoderated_posts($offset,$limit);
   	$max = tdomf_get_unmoderated_posts_count();
   }
   
   $form_count = count(tdomf_get_form_ids());

   ?>

   <div class="wrap">

   <?php if(isset($_REQUEST['f']) && $_REQUEST['f'] == "1") { ?>
       <h2><?php if($offset > 0) { _e("Previous Published Submissions","tdomf"); }
               else { printf(__('Last %d Published Submissions', 'tdomf'),$limit); } ?></h2>
   <?php } else if(isset($_REQUEST['f']) && $_REQUEST['f'] == "2") { ?>
       <h2><?php if($offset > 0) { _e("Previous Submissions","tdomf"); }
               else { printf(__('Last %d Submissions', 'tdomf'),$limit); } ?></h2>
   <?php } else if(isset($_REQUEST['f']) && $_REQUEST['f'] == "3") { ?>
       <h2><?php if($offset > 0) { _e("Previous Spam Submissions","tdomf"); }
               else { printf(__('Last %d Spam Submissions', 'tdomf'),$limit); } ?></h2>
   <?php } else if(isset($_REQUEST['f']) && $_REQUEST['f'] == "4") { ?>
       <h2><?php if($offset > 0) { _e("Previous Scheduled Submissions","tdomf"); }
               else { printf(__('Last %d Scheduled Submissions', 'tdomf'),$limit); } ?></h2>
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
        <?php if(get_option(TDOMF_OPTION_SPAM)) { ?>
            <option value="3" <?php if(isset($_REQUEST['f']) && $_REQUEST['f'] == "3"){ ?> selected <?php } ?>><?php printf(__("Spam (%d)","tdomf"),tdomf_get_spam_posts_count()); ?>
        <?php } ?>
        <option value="4" <?php if(isset($_REQUEST['f']) && $_REQUEST['f'] == "4"){ ?> selected <?php } ?>><?php _e("Scheduled","tdomf"); ?>        
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
    <?php if($form_count > 1) { ?>
      <th scope="col"><?php _e("Form","tdomf"); ?></th>
    <?php } ?>
    <th scope="col"><?php _e("Status","tdomf"); ?></th>
    <?php if(get_option(TDOMF_OPTION_SPAM)) { ?>
        <th scope="col" colspan="5" style="text-align: center"><?php _e("Actions","tdomf"); ?></th>
    <?php } else { ?>
        <th scope="col" colspan="4" style="text-align: center"><?php _e("Actions","tdomf"); ?></th>
    <?php } ?>
   </tr>

   <?php $i = 0;
         foreach($posts as $p) {
         $i++;
         
         #class='unapproved'
         $is_spam = get_post_meta($p->ID, TDOMF_KEY_SPAM); 
                  
         if($is_spam) { ?>
      <tr id='post-<?php echo $p->ID; ?>' class='spam' style='background:#CCCCFF;'>
         <?php } else if($p->post_status == 'future') { ?>
             <tr id='post-<?php echo $p->ID; ?>' class='future' style='background:#99FF99;'>
         <?php } else if(($i%2) == 0) { ?>
		  <tr id='post-<?php echo $p->ID; ?>' class=''>
	     <?php } else { ?>
		  <tr id='post-<?php echo $p->ID; ?>' class='alternate'>
         <?php } ?>

               <td><input type="checkbox" name="moderateposts[]" value="<?php echo $p->ID; ?>" /></td>
               <th scope="row"><?php echo $p->ID; ?></th>
               
		       <td>
               <?php echo $p->post_title; ?>
               
               <?php $form_id = 1;
                     if($form_count > 1) { 
                         $form_id = get_post_meta($p->ID, TDOMF_KEY_FORM_ID, true);
                     } ?>
               
               <?php $fuoptions = tdomf_widget_upload_get_options($form_id);
                     $index = 0;
                     $filelinks = "";
                     while(true) {
                         $filename = get_post_meta($p->ID, TDOMF_KEY_DOWNLOAD_NAME.$index,true); 
                         if($filename == false) { break; }
                         if($fuoptions['nohandler'] && trim($fuoptions['url']) != "") {
                             $uri = trailingslashit($fuoptions['url'])."$p->ID/".$filename;
                         } else {
                             $uri = trailingslashit(get_bloginfo('wpurl')).'?tdomf_download='.$p->ID.'&id='.$i;
                         }
                         $filelinks .= "<a href='$uri' title='".htmlentities($filename)."'>$index</a>, ";
                         $index++;
                     }
                     if(!empty($filelinks)) {  ?>
                         <br/><small><?php _e('Files: ','tdomf'); ?><?php echo $filelinks; ?></small>
                     <?php } ?>
               
               </td>
		       
               <td>
		       <?php $name = get_post_meta($p->ID, TDOMF_KEY_NAME, true);
		             $email = get_post_meta($p->ID, TDOMF_KEY_EMAIL, true);
		             $user_id = get_post_meta($p->ID, TDOMF_KEY_USER_ID, true);
		             if($user_id != false) { ?>
		               <a href="user-edit.php?user_id=<?php echo $user_id;?>" class="edit">
		               <?php $u = get_userdata($user_id);
		                echo $u->user_login; ?></a>
		             <?php } else if(!empty($name) && !empty($email)) {
		                echo $name." (".$email.")";
		             } else if(!empty($name)) {
                   echo $name;
                 } else if(!empty($email)) {
                   echo $email;
                 } else {
                   _e("N/A","tdomf");
                 } ?>
		       </td>
		       <td>
		             <?php echo get_post_meta($p->ID, TDOMF_KEY_IP, true); ?>
		       </td>
           
           <?php if($form_count > 1) { ?>
             <td>
           <?php #$form_id = get_post_meta($p->ID, TDOMF_KEY_FORM_ID, true);
                 if($form_id == false || tdomf_form_exists($form_id) == false) { ?>
                   <?php _e("N/A","tdomf"); ?>
                 <?php } else { ?>
                   <a href="admin.php?page=tdomf_show_options_menu&form=<?php echo $form_id ?>"><?php echo $form_id ?></a>
                 <?php } ?>
                 </td>
           <?php } ?>
           
           <?php $queue = intval(tdomf_get_option_form(TDOMF_OPTION_QUEUE_PERIOD,$form_id));
                 if($queue > 0) { $queue = true; } else { $queue = false; } ?>
           
		       <td>
               
               <?php if($is_spam && $p->post_status == 'draft') { ?>
                      <?php _e('Spam',"tdomf"); ?>
                   <?php } else { 
                       switch($p->post_status) {
                           case 'draft':
                              _e('Draft',"tdomf");
                              break;
                           case 'publish':
                               _e('Published',"tdomf");
                               break;
                           case 'future':
                               _e('Scheduled',"tdomf");
                               break;
                           default:
                               echo _e($p->post_status,"tdomf");
                               break;
                       }
                       if($is_spam) { _e(' (Spam)',"tdomf"); } 
                   } ?>
               </td>
               
		       <td><a href="<?php echo get_permalink($p->ID); ?>" class="edit"><?php _e("View","tdomf"); ?></a></td>

		       <td>

		       <?php if(isset($_REQUEST['f'])) { $farg = "&f=".$_REQUEST['f']; } ?>

               <?php if(get_option(TDOMF_OPTION_SPAM)) { ?>
                   <td>
                   <?php if($is_spam) { ?>
                       <a href="<?php echo wp_nonce_url("admin.php?page=tdomf_show_mod_posts_menu&action=hamit&post=$p->ID$farg&offset=$offset&limit=$limit",'tdomf-hamit_'.$p->ID); ?>" class="notspam"><?php _e("Not Spam","tdomf"); ?></a>
                   <?php } else { ?>
                       <a href="<?php echo wp_nonce_url("admin.php?page=tdomf_show_mod_posts_menu&action=spamit&post=$p->ID$farg&offset=$offset&limit=$limit",'tdomf-spamit_'.$p->ID); ?>" class="isspam"><?php _e("Spam It","tdomf"); ?></a>
                   <?php } ?>
                   </td>
               <?php } ?>
               
               <td>
               <?php if($is_spam) { ?>
                   <!-- N/A -->
		       <?php } else if($p->post_status == "draft") { ?>
                   <?php if($queue) { 
                       $publishnow_link =  wp_nonce_url("admin.php?page=tdomf_show_mod_posts_menu&action=publish&post=$p->ID$farg&offset=$offset&limit=$limit&nofuture=1",'tdomf-publish_'.$p->ID);
                       $publishlater_link = wp_nonce_url("admin.php?page=tdomf_show_mod_posts_menu&action=publish&post=$p->ID$farg&offset=$offset&limit=$limit",'tdomf-publish_'.$p->ID);
                       printf(__('<a href="%s">Publish Now</a> or <a href="%s">Add to Queue</a>','tdomf'),$publishnow_link,$publishlater_link);
                       } else { ?>
                       <a href="<?php echo wp_nonce_url("admin.php?page=tdomf_show_mod_posts_menu&action=publish&post=$p->ID$farg&offset=$offset&limit=$limit",'tdomf-publish_'.$p->ID); ?>" class="publish"><?php _e("Publish","tdomf"); ?></a>
                   <?php } ?>
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
    <?php if(get_option(TDOMF_OPTION_SPAM) && (!isset($_REQUEST['f']) || $_REQUEST['f'] == '0')) { ?>
        <input type="submit" name="recheck_button" value="<?php _e("Recheck Unmoderated Submissions for Spam"); ?>" />
	<?php } ?>
    <input type="submit" name="delete_button" class="delete" value="<?php _e("Delete Checked Posts &raquo;"); ?>" onclick="var numchecked = getNumChecked(document.getElementById('moderateposts')); if(numchecked < 1) { alert('Please select some posts to delete'); return false } return confirm('You are about to delete ' + numchecked + ' posts permanently \n  \'Cancel\' to stop, \'OK\' to delete.')" />
    <?php if(!isset($_REQUEST['f']) || $_REQUEST['f'] == '0' || $_REQUEST['f'] == '2') { ?>
	<input type="submit" name="publish_button" value="<?php _e("Publish Checked Posts &raquo;"); ?>" onclick="var numchecked = getNumChecked(document.getElementById('moderateposts')); if(numchecked < 1) { alert('Please select some posts to publish'); return false } return confirm('You are about to publish ' + numchecked + ' posts \n  \'Cancel\' to stop, \'OK\' to publish')" />
	<?php } ?>
	<?php if(isset($_REQUEST['f']) && ($_REQUEST['f'] == '1' || $_REQUEST['f'] == '2')) { ?>
	<input type="submit" name="unpublish_button" value="<?php _e("Un-Publish Checked Posts &raquo;"); ?>" onclick="var numchecked = getNumChecked(document.getElementById('moderateposts')); if(numchecked < 1) { alert('Please select some posts to publish'); return false } return confirm('You are about to un-publish ' + numchecked + ' posts \n  \'Cancel\' to stop, \'OK\' to publish')" />
	<?php } ?>
    <?php if(get_option(TDOMF_OPTION_SPAM)) { ?>
        <?php if(!isset($_REQUEST['f']) || $_REQUEST['f'] == '1' || $_REQUEST['f'] == '2' || $_REQUEST['f'] == '0') { ?>
        <input type="submit" name="spam_button" value="<?php _e("Mark Checked Posts as Spam &raquo;"); ?>" onclick="var numchecked = getNumChecked(document.getElementById('moderateposts')); if(numchecked < 1) { alert('Please select some posts to publish'); return false } return confirm('You are about to mark ' + numchecked + ' as spam \n  \'Cancel\' to stop, \'OK\' to publish')" />
        <?php } ?>
        <?php if($_REQUEST['f'] == '1' || $_REQUEST['f'] == '2' || $_REQUEST['f'] == '3' ) { ?>
        <input type="submit" name="notspam_button" value="<?php _e("Mark Checked Posts as Not Spam &raquo;"); ?>" onclick="var numchecked = getNumChecked(document.getElementById('moderateposts')); if(numchecked < 1) { alert('Please select some posts to publish'); return false } return confirm('You are about to mark ' + numchecked + ' as not being spam \n  \'Cancel\' to stop, \'OK\' to publish')" />
        <?php } ?>
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

   if(isset($_REQUEST['recheck_button'])) {
       check_admin_referer('tdomf-moderate-bulk');
       $posts = tdomf_get_unmoderated_posts();
       $list = "";
       if(count($posts) > 0) {
           foreach($posts as $post) {
               if(!tdomf_check_submissions_spam($post->ID)) {
                   $list .= $post->ID.", ";
               }
           }
       }
       if($list != "") {
           tdomf_log_message("These posts are actually spam: $list");
           $message = sprintf(__("Marked these posts as spam: %s","tdomf"),$list);
       }
   }
   else if(isset($_REQUEST['delete_button'])) {
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
      $queue = !isset($_REQUEST['nofuture']);
      $list = "";
      foreach($posts as $p) {
         // if we're going to publish the post, then it's not spam!
         tdomf_ham_post($p);
         tdomf_publish_post($p,$queue);
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
   } else if(isset($_REQUEST['spam_button'])) {
       check_admin_referer('tdomf-moderate-bulk');
       $posts = $_REQUEST['moderateposts'];
       $list = "";
       foreach($posts as $p) {
           if(!get_post_meta($p, TDOMF_KEY_SPAM)) {
              tdomf_spam_post($p);
              $list .= $p.",";
           }
       }
       if($list != "") {
           tdomf_log_message("Spammed $list posts");
           $message = sprintf(__("Marked these posts as spam: %s","tdomf"),$list);
       }
   } else if(isset($_REQUEST['notspam_button'])) {
       check_admin_referer('tdomf-moderate-bulk');
       $posts = $_REQUEST['moderateposts'];
       $list = "";
       foreach($posts as $p) {
         if(!get_post_meta($p, TDOMF_KEY_SPAM)) {
            tdomf_ham_post($p);
            $list .= $p.",";
         }
       }
       if($list != "") {
           tdomf_log_message("Hammed $list posts");
           $message = sprintf(__("Marked these posts as not being spam: %s","tdomf"),$list);
       }
   } else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'publish') {
      $post_id = $_REQUEST['post'];
      check_admin_referer('tdomf-publish_'.$post_id);
      $queue = !isset($_REQUEST['nofuture']);
      // if we're going to publish the post, then it's not spam!
      tdomf_ham_post($post_id);
      tdomf_publish_post($post_id,$queue);
      tdomf_log_message("Published post $post_id");
      $message = sprintf(__("Published post <a href=\"%s\">%d</a>.","tdomf"),get_permalink($post_id),$post_id);
   } else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'unpublish') {
      $post_id = $_REQUEST['post'];
      check_admin_referer('tdomf-unpublish_'.$post_id);
      tdomf_unpublish_post($post_id);
      tdomf_log_message("Unpublished post $post_id");
      $message = sprintf(__("Unpublished post %d.","tdomf"),$post_id);
   } else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'spamit') {
      $post_id = $_REQUEST['post'];
      check_admin_referer('tdomf-spamit_'.$post_id);
      if(!get_post_meta($post_id, TDOMF_KEY_SPAM)) {
         tdomf_spam_post($post_id);
         tdomf_log_message("Post $post_id submitted as spam");
         $message = sprintf(__("Post %d flagged as spam","tdomf"),$post_id);
      }
   } else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'hamit') {
      $post_id = $_REQUEST['post'];
      check_admin_referer('tdomf-hamit_'.$post_id);
      if(get_post_meta($post_id, TDOMF_KEY_SPAM)) {
         tdomf_ham_post($post_id);
         tdomf_log_message("Post $post_id submitted as ham");
         $message = sprintf(__("Post %d flagged as not being spam","tdomf"),$post_id);
      }
   }

   if(!empty($message)) { ?>
      <div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
   <?php }
}

?>
