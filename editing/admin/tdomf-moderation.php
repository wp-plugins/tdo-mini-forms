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
          /* edit date required for wp 2.7 */
          "edit_date"      => $ts,
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

function tdomf_get_mod_posts_url($echo=false,$show='all',$filter='0') {
    $url = 'admin.php?page=tdomf_show_mod_posts_menu';
    if($show != 'all') {
        $url .= '&show=' . $show;
    }
    if($filter != '0') {
        $url .= '&filter=' . $filter;
    }
    if($echo) {
        echo $url; 
    }
    return $url;
}

/* @todo hide posts that are pending/draft due to edits from "Pending Submissions"
   @todo unapproved edits status
   @todo filters: form ids, posts with edits, with no edits, by user, by IP 
   @todo spam edits */

// Show the moderation menu
//
function tdomf_show_mod_posts_menu() {
    
   /* if(!tdomf_is_moderation_in_use()) { ?>
   <div class="wrap">
       <h2><?php printf(__('Moderation Disabled', 'tdomf'),$limit); ?></h2>
       <p><center><b><?php printf(__('Moderation is currently disabled. You can enable it on the <a href="%s">options</a> page.',"tdomf"),"admin.php?page=tdomf_show_options_menu"); ?></b></center></p>
   </div>
   <?php
      return;
   } */

   tdomf_moderation_handler();

   $filter = 0;
   if(isset($_REQUEST['filter'])) { $filter = intval($_REQUEST['filter']); }
   
   $pending_count = tdomf_get_unmoderated_posts_count();
   $scheduled_count = tdomf_get_queued_posts_count();
   $published_count = tdomf_get_published_posts_count();
   $spam_count = tdomf_get_spam_posts_count();
   $all_count = tdomf_get_submitted_posts_count();
   $form_ids = tdomf_get_form_ids();
   $pending_edits_count = tdomf_get_edits(array('state' => 'unapproved', 'count' => true, 'unique_post_ids' => true));
   $spam_edits_count = tdomf_get_edits(array('state' => 'spam', 'count' => true, 'unique_post_ids' => true)); 
   
   $limit = 15; # fixed
   $paged = 1;
   if(isset($_GET['paged'])) { $paged = intval($_GET['paged']); }
   $offset = $limit * ($paged - 1);
   $show = 'all';
   if(isset($_REQUEST['show'])) { $show = $_REQUEST['show']; }
   
   $posts = false;
   $max = 0;
   if($show == 'all') {
       $posts = tdomf_get_submitted_posts($offset,$limit);
       $max = ceil($all_count / $limit);
   } else if($show == 'pending_submissions') {
       $posts = tdomf_get_unmoderated_posts($offset,$limit);
       $max = ceil($pending_count / $limit);
   } else if($show == 'scheduled') {
       $posts = tdomf_get_queued_posts($offset,$limit);
       $max = ceil($scheduled_count / $limit);
   } else if($show == 'published') {
       $posts = tdomf_get_published_posts($offset,$limit);
       $max = ceil($published_count / $limit);
   } else if($show == 'spam_submissions') {
       $posts = tdomf_get_spam_posts($offset,$limit);
       $max = ceil($spam_count / $limit);
   } else if($show == 'pending_edits') {
       $edits = tdomf_get_edits(array('state' => 'unapproved', 'unique_post_ids' => true, 'offset' => $offset, 'limit' => $limit)); 
       $max = ceil($pending_edits_count / $limit);
       $posts = array();
       # a little hacky magic
       foreach($edits as $e) {
           $posts[] = (OBJECT) array( 'ID' => $e->post_id );
       }
   } else if($show == 'spam_edits') {
       $edits = tdomf_get_edits(array('state' => 'spam', 'unique_post_ids' => true, 'offset' => $offset, 'limit' => $limit)); 
       $max = ceil($spam_edits_count / $limit);
       $posts = array();
       # a little hacky magic
       foreach($edits as $e) {
           $posts[] = (OBJECT) array( 'ID' => $e->post_id );
       }
   }
   # max is incorrect... doesn't account for form filter...
   
   $mode = 'list';
   if(isset($_GET['mode'])) { $mode = $_GET['mode']; }
   
   $count = 0;
   
   ?>
   
   <div class="wrap">
   
   <?php screen_icon(); ?>
   <h2><?php _e('Moderation', 'tdomf'); ?>
   </h2>

   <form id="posts-filter" action="<?php tdomf_get_mod_posts_url(true,$show,0); ?>" method="post">
   
   <!-- hidden vars -->
   
   <ul class="subsubsub">
   <li><a href="<?php tdomf_get_mod_posts_url(true,'all',$filter); ?>"<?php if($show == 'all') { ?> class="current"<?php } ?>><?php printf(__('All (%s)','tdomf'),$all_count); ?></a> | </li>
   <?php if($pending_count > 0) { ?>
      <li><a href="<?php tdomf_get_mod_posts_url(true,'pending_submissions',$filter); ?>"<?php if($show == 'pending_submissions') { ?> class="current"<?php } ?>><?php printf(__('Pending Submissions (%s)','tdomf'),$pending_count); ?></a> | </li>
   <?php } ?>
   <?php if($scheduled_count > 0) { ?>
      <li><a href="<?php tdomf_get_mod_posts_url(true,'scheduled',$filter); ?>"<?php if($show == 'scheduled') { ?> class="current"<?php } ?>><?php printf(__('Scheduled Submissions (%s)','tdomf'),$scheduled_count); ?></a> | </li>
   <?php } ?>
   <?php if($published_count > 0) { ?>
       <li><a href="<?php tdomf_get_mod_posts_url(true,'published',$filter); ?>"<?php if($show == 'published') { ?> class="current"<?php } ?>><?php printf(__('Published (%s)','tdomf'),$published_count); ?></a> | </li>
   <?php } ?>
   <?php if($spam_count > 0) { ?>
       <li><a href="<?php tdomf_get_mod_posts_url(true,'spam_submissions',$filter); ?>"<?php if($show == 'spam_submissions') { ?> class="current"<?php } ?>><?php printf(__('Spam Submissions (%s)','tdomf'),$spam_count); ?></a> | </li>
   <?php } ?>
   <?php if($pending_edits_count > 0) { ?>
       <li><a href="<?php tdomf_get_mod_posts_url(true,'pending_edits',$filter); ?>"<?php if($show == 'pending_edits') { ?> class="current"<?php } ?>><?php printf(__('Pending Edits (%s)','tdomf'),$pending_edits_count); ?></a> | </li>
   <?php } ?>
   <?php if($spam_edits_count > 0) { ?>
       <li><a href="<?php tdomf_get_mod_posts_url(true,'spam_edits',$filter); ?>"<?php if($show == 'spam_edits') { ?> class="current"<?php } ?>><?php printf(__('Spam Edits (%s)','tdomf'),$spam_edits_count); ?></a> | </li>
   <?php } ?>
   </ul>

   <div class="tablenav">
   
   <?php
    $page_links = paginate_links( array(
        'base' => add_query_arg( 'paged', '%#%' ),
        'format' => '',
        'prev_text' => __('&laquo;'),
        'next_text' => __('&raquo;'),
        'total' => $max,
        'current' => $paged
     ));
    ?>

    <!-- Hide bulk actions (from top of page) and fitlers for the time being
    
    <div class="alignleft actions">
    <select name="action">
    <option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
    <option value="edit"><?php _e('Publish'); ?></option>
    <option value="delete"><?php _e('Delete'); ?></option>
    </select>
    <input type="submit" value="<?php _e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
    <?php wp_nonce_field('bulk-posts'); ?>
    
    <select name='form'>
    <option value="-1" selected="selected"><?php _e('Show All Forms','tdomf'); ?></option>
    <?php foreach($form_ids as $form) { ?>
       <option value="<?php echo $form->form_id; ?>"><?php printf(__('Form #%d','tdomf'),$form->form_id); ?></option>
    <?php } ?>
    </select>
    <input type="submit" id="post-query-submit" value="<?php _e('Filter'); ?>" class="button-secondary" />
    
    -->
</div>

<?php if ( $page_links ) { ?>
<div class="tablenav-pages"><?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
	number_format_i18n( $offset ),
	number_format_i18n( $offset+$limit ),
	number_format_i18n( $max ),
	$page_links
); echo $page_links_text; ?></div>
<?php } ?>

<div class="view-switch">
	<a href="<?php echo clean_url(add_query_arg('mode', 'list', $_SERVER['REQUEST_URI'])) ?>"><img <?php if ( 'list' == $mode ) echo 'class="current"'; ?> id="view-switch-list" src="../wp-includes/images/blank.gif" width="20" height="20" title="<?php _e('List View') ?>" alt="<?php _e('List View') ?>" /></a>
	<a href="<?php echo clean_url(add_query_arg('mode', 'excerpt', $_SERVER['REQUEST_URI'])) ?>"><img <?php if ( 'excerpt' == $mode ) echo 'class="current"'; ?> id="view-switch-excerpt" src="../wp-includes/images/blank.gif" width="20" height="20" title="<?php _e('Excerpt View') ?>" alt="<?php _e('Excerpt View') ?>" /></a>
</div>

<div class="clear"></div>

</div> <!-- tablenav -->

<div class="clear"></div>

<table class="widefat post fixed" cellspacing="0">

	<thead>
	<tr>
	<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
	<th scope="col" id="title" class="manage-column column-title" style="">Post</th>
	<th scope="col" id="submitted" class="manage-column column-submitted" style="">Submitted</th>
	<th scope="col" id="edited" class="manage-column column-edited" style="">Most Recent Edit</th>
	<th scope="col" id="status" class="manage-column column-status" style="">Status</th>
	</tr>
	</thead>

	<tfoot>
	<tr>
	<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
	<th scope="col" id="title" class="manage-column column-title" style="">Post</th>
	<th scope="col" id="submitted" class="manage-column column-submitted" style="">Submitted</th>
	<th scope="col" id="edited" class="manage-column column-edited" style="">Most Recent Edit</th>
	<th scope="col" id="status" class="manage-column column-status" style="">Status</th>
	</tr>
	</tfoot>
    
    <tbody>
    <?php foreach($posts as $p) { $count++; ?>

        <?php $post = &get_post( $p->ID ); /* seems I need this later */ ?> 
        <?php $last_edit = tdomf_get_edits(array('post_id' => $p->ID, 'limit' => 1)); /* and need this earlier too */ ?>
        <?php $queue = intval(tdomf_get_option_form(TDOMF_OPTION_QUEUE_PERIOD,$form_id));
              if($queue > 0) { $queue = true; } else { $queue = false; } ?>
        <?php $is_spam = get_post_meta($p->ID, TDOMF_KEY_SPAM); ?>

        <tr id='post-<?php echo $p->ID; ?>' class='<?php if(($count%2) != 0) { ?>alternate <?php } ?>status-<?php echo $post->post_status; ?> iedit' valign="top">

        <th scope="row" class="check-column"><input type="checkbox" name="post[]" value="<?php echo $p->ID; ?>" /></th>
        <td class="post-title column-title"><strong><a class="row-title" href="post.php?action=edit&amp;post=<?php echo $p->ID; ?>" title="Edit"><?php echo $post->post_title; ?></a></strong>
              
        <?php if(get_option(TDOMF_OPTION_SPAM)) { ?> <?php } ?>
        
        <?php if ( 'excerpt' == $mode ){
                 # Have to create our own excerpt, the_excerpt() doesn't cut it
                 # here :(
      
                 if ( empty($post->post_excerpt) ) {
                    $excerpt = apply_filters('the_content', $post->post_content);
                 } else { 
                    $excerpt = apply_filters('the_excerpt', $post->post_excerpt);
                 }
                 $excerpt = str_replace(']]>', ']]&gt;', $excerpt);
                 $excerpt = wp_html_excerpt($excerpt, 252);
                 if(strlen($excerpt) == 252){ $excerpt .= '...'; }; 
                 echo $excerpt;
        } ?>

        <div class="row-actions">
           <span class='edit'><a href="post.php?action=edit&amp;post=<?php echo $p->ID; ?>" title="<?php echo htmlentities(__('Edit this submission','tdomf')); ?>"><?php _e('Edit','tdomf'); ?></a> | </span>
           <?php if($post->post_status == 'future') { ?>
               <span class="publish"><a href="#" title="<?php echo htmlentities(__('Publish this submission now','tdomf')); ?>"><?php _e('Publish Now','tdomf'); ?></a> |</span>
           <?php } else if($post->post_status != 'publish') { ?>
               <?php if($queue) { ?>
                   <span class="publish"><a href="#" title="<?php echo htmlentities(__('Add submission to publish queue','tdomf')); ?>"><?php _e('Queue','tdomf'); ?></a> |</span>
                   <span class="publish"><a href="#" title="<?php echo htmlentities(__('Publish submission now','tdomf')); ?>"><?php _e('Publish Now','tdomf'); ?></a> |</span>
               <?php } else { ?>
                   <span class="publish"><a href="#" title="<?php echo htmlentities(__('Publish submission','tdomf')); ?>"><?php _e('Publish','tdomf'); ?></a> |</span>
               <?php } ?>
           <?php } else if($post->post_status == 'publish')  { ?>
               <span class="publish"><a href="#" title="<?php echo htmlentities(__('Set submission to draft/unmoderated status.','tdomf')); ?>"><?php _e('Un-publish','tdomf'); ?></a> |</span>
           <?php } ?>
           <span class='delete'><a class='submitdelete' title='Delete this submission' href='<?php echo wp_nonce_url("post.php?action=delete&amp;post=$p->ID", 'delete-post_' . $p->ID); ?>' onclick="if ( confirm('<?php echo js_escape(sprintf(__("You are about to delete this post \'%s\'\n \'Cancel\' to stop, \'OK\' to delete.",'tdomf'),$post->post_title)); ?>') ) { return true;}return false;"><?php _e('Delete','tdomf'); ?></a> | </span>
           <span class='view'><a href="http://localhost/wordpress/?p=16" title="View &quot;go go go&quot;" rel="permalink"><?php _e('View','tdomf'); ?></a> 
           <?php if(get_option(TDOMF_OPTION_SPAM)) { ?> |</span><?php } ?>
           <?php if(get_option(TDOMF_OPTION_SPAM)) { 
                 if($is_spam) { ?>
               <span class="spam"><a href="#" onclick="if ( confirm('<?php echo js_escape(sprintf(__("You are about to flag this submission \'%s\' as spam\n \'Cancel\' to stop, \'OK\' to delete.",'tdomf'),$post->post_title)); ?>') ) { return true;}return false;"><?php _e('Spam','tdomf');  ?></a></span>
           <?php } else { ?>
              <span class="spam" title="<?php echo htmlentities(__('Flag submission as not being spam','tdomf')); ?>" ><?php _e('Not Spam','tdomf'); ?></span>
           <?php } } ?>
        </div>
        </td>
        
        <td class="column-submitted">
       
        <ul style="font-size: 11px;">
        <li>
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
         / <?php echo get_post_meta($p->ID, TDOMF_KEY_IP, true); ?> </li>
        <li>
        <?php $form_id = get_post_meta($p->ID, TDOMF_KEY_FORM_ID, true); 
              if($form_id == false || tdomf_form_exists($form_id) == false) { ?>
                 <?php _e("Unknown or deleted form","tdomf"); ?>
              <?php } else { 
                 $form_edit_url = "admin.php?page=tdomf_show_form_options_menu&form=$form_id";
                 $form_name = tdomf_get_option_form(TDOMF_OPTION_NAME,$form_id);
                 echo '<a href="'.$form_edit_url.'">'.sprintf(__('Form #%d: %s</a>','tdomf'),$form_id,$form_name).'</a>';
                    } ?>
        </li>
        <li><?php echo mysql2date(__('Y/m/d'), $post->post_date_gmt); ?></li>
        </ul>
        </td>

        <td class="column-edited">
        <?php $last_edit = tdomf_get_edits(array('post_id' => $p->ID, 'limit' => 1));
              if($last_edit == false || empty($last_edit)) { ?>
                      <!-- no edits -->
        <?php } else { 
              $last_edit = $last_edit[0]; # only care about the first entry
              $last_edit_data = maybe_unserialize($last_edit->data); ?>
        <ul style="font-size: 11px;">
        <li><?php # if set... must wait till "Who Am I" widget is done
                  $name = "";
                  $email = "";
                  $user_id = $last_edit->user_id;
              if($user_id != 0) { ?>
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
         / <?php echo $last_edit->ip; ?>
         </li>
        <li>
        <?php $form_id = $last_edit->form_id; 
              if($form_id == false || tdomf_form_exists($form_id) == false) { ?>
                 <?php _e("Unknown or deleted form","tdomf"); ?>
              <?php } else { 
                 $form_edit_url = "admin.php?page=tdomf_show_form_options_menu&form=$form_id";
                 $form_name = tdomf_get_option_form(TDOMF_OPTION_NAME,$form_id);
                 echo '<a href="'.$form_edit_url.'">'.sprintf(__('Form #%d: %s</a>','tdomf'),$form_id,$form_name).'</a>';
                    } ?>
         </li>
         <li><?php echo mysql2date(__('Y/m/d'), $last_edit->date_gmt); ?></li>
        <li><?php switch($last_edit->state) {
                           case 'unapproved':
                              _e('Unapproved',"tdomf");
                              break;
                           case 'approved':
                               _e('Approved',"tdomf");
                               break;
                           case 'spam':
                               _e('Spam',"tdomf");
                               break;
                           default:
                               echo _e($last_edit->state,"tdomf");
                               break;
                       } ?>
         </li>
        </ul>
        
        <div class="row-actions">
           <?php if($last_edit->revision_id != 0 
                 && $last_edit->state != 'approved') { ?>
              <span class='view'><a href="http://localhost/wordpress/wp-admin/revision.php?revision=<?php echo $last_edit->revision_id; ?>"><?php _e('View','tdomf'); ?></a> |<span>
           <?php }?> 
           <?php if($last_edit->state == 'approved') { ?>
              <span class="edit">Revert
              <?php if(get_option(TDOMF_OPTION_SPAM)) { ?> |<?php } ?></span>
           <?php } else if($last_edit->state == 'unapproved') { ?>
              <span class="edit">Approve| </span>
              <span class="edit">Compare
              <?php if(get_option(TDOMF_OPTION_SPAM)) { ?> |<?php } ?></span>
           <?php } ?>
        <?php if(get_option(TDOMF_OPTION_SPAM)) { 
                 if($last_edit->state == 'spam') { ?>
             <span class="spam" title="<?php echo htmlentities(__('Flag contributation as not being spam','tdomf')); ?>" ><?php _e('Not Spam','tdomf'); ?></span>
         <php    } else { ?>
              <span class="spam"><a href="#" title="<?php echo htmlentities(__('Flag contributation as being spam','tdomf')); ?>" onclick="if ( confirm('<?php echo js_escape(__("You are about to flag this contribution as spam\n \'Cancel\' to stop, \'OK\' to delete.",'tdomf')); ?>') ) { return true;}return false;"><?php _e('Spam','tdomf');  ?></a></span>
        <?php    } }?>
           </div>
        
        <?php } ?>
        
        </td>
        
         <td class="status column-status">
         <!-- todo take into account edited status -->
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
    <?php } ?>
    
    </tbody>
    
</table>

<div class="tablenav">

<?php
if ( $page_links )
	echo "<div class='tablenav-pages'>$page_links_text</div>";
?>

    <!-- Publish (Now)
         Add to Publish Queue
         Unpublish/Remove from Queue
         Approve Edit
         Revert Last Edit
         Spam
         Not Spam
         Recheck if Spam -->

    <div class="alignleft actions">
    <select name="action">
    <option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
    <option value="edit"><?php _e('Publish'); ?></option>
    <option value="delete"><?php _e('Delete'); ?></option>
    </select>
    <input type="submit" value="<?php _e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
    <?php wp_nonce_field('bulk-posts'); ?>
    
    <!-- hide filters
    
    <select name='form'>
    <option value="-1" selected="selected"><?php _e('Show All Forms','tdomf'); ?></option>
    <?php foreach($form_ids as $form) { ?>
       <option value="<?php echo $form->form_id; ?>"><?php printf(__('Form #%d','tdomf'),$form->form_id); ?></option>
    <?php } ?>
    </select>
    
    -->
    
    <br class="clear" />

    </div> <!-- tablenav -->
    
    <br class="clear" />
    
</div> <!-- wrap -->

<?php /* } else { // have_posts() ?>
<div class="clear"></div>
<p><?php _e('No posts found') ?></p>
<?php } */ ?>

</form>

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
