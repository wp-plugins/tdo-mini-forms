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

function tdomf_get_mod_posts_url($args) {
    
    $defaults = array('echo' => false,
                      'show' => 'all',
                      'filter' => false,
                      'action' => false,
                      'post_id' => false,
                      'mode' => 'list',
                      'nonce' => false,
                      'revision_id' => false,
                      'edit_id' => false,
                      'limit' => false );
    if(isset($_REQUEST['show'])) { $defaults['show'] = $_REQUEST['show']; }
    if(isset($_REQUEST['mode'])) { $defaults['mode'] = $_REQUEST['mode']; }
    if(isset($_REQUEST['filter'])) { $defaults['filter'] = $_REQUEST['filter']; }
    if(isset($_REQUEST['limit'])) { $defaults['limit'] = intval($_REQUEST['limit']); }
    $args = wp_parse_args($args, $defaults);
    extract($args);
    
    $url = get_bloginfo('wpurl').'/wp-admin/admin.php?page=tdomf_show_mod_posts_menu';
    if($show != 'all') {
        $url .= '&show=' . $show;
    }
    $url .= '&mode=' . $mode;
    if($filter) {
        $url .= '&filter=' . $filter;
    }
    if($action) {
        $url .= '&action=' . $action;
    }
    if($post_id) {
        $url .= '&post=' . $post_id;
    }
    if($revision_id) {
        $url .= '&revision=' . $revision_id;
    }
    if($edit_id) {
        $url .= '&edit=' . $edit_id;
    }
    if($limit) {
        $url .= '&limit=' . $limit;
    }
    if($nonce) {
        $url = wp_nonce_url($url,$nonce);
    }
    if($echo) {
        echo $url; 
    }
    return $url;
}

/* @todo filters: form ids, posts with edits, with no edits, by user, by IP  */

// Show the moderation menu
//
function tdomf_show_mod_posts_menu() {
    
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
   
   $limit = 10; # fixed
   if(isset($_REQUEST['limit'])) { $limit = intval($_REQUEST['limit']); }
   $paged = 1;
   if(isset($_GET['paged'])) { $paged = intval($_GET['paged']); }
   $offset = $limit * ($paged - 1);
   $show = 'all';
   if(isset($_REQUEST['show'])) { $show = $_REQUEST['show']; }
   
   $posts = false;
   $max_pages = 0;
   $max_items = 0;
   if($show == 'all') {
       $posts = tdomf_get_submitted_posts($offset,$limit);
       $max_pages = ceil($all_count / $limit);
       $max_items = $all_count;
   } else if($show == 'pending_submissions') {
       $posts = tdomf_get_unmoderated_posts($offset,$limit);
       $max_pages = ceil($pending_count / $limit);
       $max_items = $pending_count;
   } else if($show == 'scheduled') {
       $posts = tdomf_get_queued_posts($offset,$limit);
       $max_pages = ceil($scheduled_count / $limit);
       $max_items = $scheduled_count;
   } else if($show == 'published') {
       $posts = tdomf_get_published_posts($offset,$limit);
       $max_pages = ceil($published_count / $limit);
       $max_items = $published_count;
   } else if($show == 'spam_submissions') {
       $posts = tdomf_get_spam_posts($offset,$limit);
       $max_pages = ceil($spam_count / $limit);
       $max_items = $spam_count;
   } else if($show == 'pending_edits') {
       $edits = tdomf_get_edits(array('state' => 'unapproved', 'unique_post_ids' => true, 'offset' => $offset, 'limit' => $limit)); 
       $max_pages = ceil($pending_edits_count / $limit);
       $posts = array();
       # a little hacky magic
       foreach($edits as $e) {
           $posts[] = (OBJECT) array( 'ID' => $e->post_id );
       }
       $max_items = $pending_edits_count;
   } else if($show == 'spam_edits') {
       $edits = tdomf_get_edits(array('state' => 'spam', 'unique_post_ids' => true, 'offset' => $offset, 'limit' => $limit)); 
       $max_pages = ceil($spam_edits_count / $limit);
       $posts = array();
       # a little hacky magic
       foreach($edits as $e) {
           $posts[] = (OBJECT) array( 'ID' => $e->post_id );
       }
       $max_items = $spam_edits_count;
   }
   # max is incorrect... doesn't account for form filter...
   
   $mode = 'list';
   if(isset($_GET['mode'])) { $mode = $_GET['mode']; }
   
   $count = 0;
   
   # what bulk actions to support

   $bulk_sub_publish_now = false;
   $bulk_sub_publish = false;
   $bulk_sub_unpublish = false;
   $bulk_sub_spamit = false;
   $bulk_sub_hamit = false;
   $bulk_edit_approve = false;
   $bulk_edit_revert = false;
   $bulk_edit_delete = false;
   $bulk_edit_spamit = false;
   $bulk_edit_hamit = false;

   ?>
   
   <div class="wrap">
   
   <?php screen_icon(); ?>
   <h2><?php _e('Moderation', 'tdomf'); ?>
   </h2>

   <?php /*if(count($posts) <= 0) { ?>
      <div class="clear"></div>
      <p><?php _e('No submissions found','tdomf') ?></p>
      </div> <!-- wrap --><?php 
   return; }*/ ?>
   
   <form id="posts-filter" action="<?php tdomf_get_mod_posts_url(true,$show,0); ?>" method="post">
   
   <!-- hidden vars -->
   
   <ul class="subsubsub">
   <li><a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'show' => 'all')); ?>"<?php if($show == 'all') { ?> class="current"<?php } ?>><?php printf(__('All (%s)','tdomf'),$all_count); ?></a> | </li>
   <?php if($pending_count > 0) { ?>
      <li><a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'show' => 'pending_submissions')); ?>"<?php if($show == 'pending_submissions') { ?> class="current"<?php } ?>><?php printf(__('Pending Submissions (%s)','tdomf'),$pending_count); ?></a> | </li>
   <?php } ?>
   <?php if($scheduled_count > 0) { ?>
      <li><a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'show' => 'scheduled')); ?>"<?php if($show == 'scheduled') { ?> class="current"<?php } ?>><?php printf(__('Scheduled Submissions (%s)','tdomf'),$scheduled_count); ?></a> | </li>
   <?php } ?>
   <?php if($published_count > 0) { ?>
       <li><a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'show' => 'published')); ?>"<?php if($show == 'published') { ?> class="current"<?php } ?>><?php printf(__('Published (%s)','tdomf'),$published_count); ?></a> | </li>
   <?php } ?>
   <?php if($spam_count > 0) { ?>
       <li><a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'show' => 'spam_submissions')); ?>"<?php if($show == 'spam_submissions') { ?> class="current"<?php } ?>><?php printf(__('Spam Submissions (%s)','tdomf'),$spam_count); ?></a> | </li>
   <?php } ?>
   <?php if($pending_edits_count > 0) { ?>
       <li><a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'show' => 'pending_edits')); ?>"<?php if($show == 'pending_edits') { ?> class="current"<?php } ?>><?php printf(__('Pending Edits (%s)','tdomf'),$pending_edits_count); ?></a> | </li>
   <?php } ?>
   <?php if($spam_edits_count > 0) { ?>
       <li><a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'show' => 'spam_edits')); ?>"<?php if($show == 'spam_edits') { ?> class="current"<?php } ?>><?php printf(__('Spam Edits (%s)','tdomf'),$spam_edits_count); ?></a> | </li>
   <?php } ?>
   </ul>

   <div class="tablenav">
   
   <?php
    $page_links = paginate_links( array(
        'base' => add_query_arg( 'paged', '%#%' ),
        'format' => '',
        'prev_text' => __('&laquo;'),
        'next_text' => __('&raquo;'),
        'total' => $max_pages,
        'current' => $paged
     ));
    ?>

    <div class="alignleft actions">
    
    <?php /* Hide bulk actions (from top of page) and fitlers for the time being
    
    
    <select name="action">
    <option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
    <option value="edit"><?php _e('Publish'); ?></option>
    <option value="delete"><?php _e('Delete'); ?></option>
    </select>
    <input type="submit" value="<?php _e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
    <?php wp_nonce_field('tdomf-moderate-bulk'); ?>
    
    <select name='form'>
    <option value="-1" selected="selected"><?php _e('Show All Forms','tdomf'); ?></option>
    <?php foreach($form_ids as $form) { ?>
       <option value="<?php echo $form->form_id; ?>"><?php printf(__('Form #%d','tdomf'),$form->form_id); ?></option>
    <?php } ?>
    </select>
    <input type="submit" id="post-query-submit" value="<?php _e('Filter'); ?>" class="button-secondary" />
    
    */ ?>
    
    </div>

<?php if ( $page_links ) { ?>
<div class="tablenav-pages"><?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
	number_format_i18n( $offset ),
	number_format_i18n( $offset + count($posts) ),
	number_format_i18n( $max_items ),
	$page_links
); echo $page_links_text; ?></div>
<?php } ?>

<div class="view-switch">
	<a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'mode' => 'list')); ?>"><img <?php if ( 'list' == $mode ) echo 'class="current"'; ?> id="view-switch-list" src="../wp-includes/images/blank.gif" width="20" height="20" title="<?php _e('List View') ?>" alt="<?php _e('List View') ?>" /></a>
	<a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'mode' => 'excerpt')); ?>"><img <?php if ( 'excerpt' == $mode ) echo 'class="current"'; ?> id="view-switch-excerpt" src="../wp-includes/images/blank.gif" width="20" height="20" title="<?php _e('Excerpt View') ?>" alt="<?php _e('Excerpt View') ?>" /></a>
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
           <?php if($post->post_status == 'future') { 
               $bulk_sub_publish_now = true; ?>
               <span class="publish"><a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'action' => 'publish_now', 'post_id' => $p->ID, 'nonce' => 'tdomf-publish_' . $p->ID)) ?>" title="<?php echo htmlentities(__('Publish this submission now','tdomf')); ?>"><?php _e('Publish Now','tdomf'); ?></a> |</span>
           <?php } else if($post->post_status != 'publish') { ?>
               <?php if($queue) { 
                   $bulk_sub_publish_now = true; 
                   $bulk_sub_publish = true; ?>
                   <span class="publish"><a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'action' => 'publish', 'post_id' => $p->ID, 'nonce' => 'tdomf-publish_' . $p->ID)) ?>" title="<?php echo htmlentities(__('Add submission to publish queue','tdomf')); ?>"><?php _e('Queue','tdomf'); ?></a> |</span>
                   <span class="publish"><a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'action' => 'publish_now', 'post_id' => $p->ID, 'nonce' => 'tdomf-publish_' . $p->ID)) ?>" title="<?php echo htmlentities(__('Publish submission now','tdomf')); ?>"><?php _e('Publish Now','tdomf'); ?></a> |</span>
               <?php } else { 
                   $bulk_sub_publish = true; ?>
                   <span class="publish"><a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'action' => 'publish_now', 'post_id' => $p->ID, 'nonce' => 'tdomf-publish_' . $p->ID)) ?>" title="<?php echo htmlentities(__('Publish submission','tdomf')); ?>"><?php _e('Publish','tdomf'); ?></a> |</span>
               <?php } ?>
           <?php } else if($post->post_status == 'publish')  { 
               $bulk_sub_unpublish = true; ?>
               <span class="publish"><a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'action' => 'unpublish', 'post_id' => $p->ID, 'nonce' => 'tdomf-unpublish_' . $p->ID)) ?>" title="<?php echo htmlentities(__('Set submission to draft/unmoderated status.','tdomf')); ?>"><?php _e('Un-publish','tdomf'); ?></a> |</span>
           <?php } ?>
           <span class='delete'><a class='submitdelete' title='Delete this submission' href='<?php echo wp_nonce_url("post.php?action=delete&amp;post=$p->ID", 'delete-post_' . $p->ID); ?>' onclick="if ( confirm('<?php echo js_escape(sprintf(__("You are about to delete this post \'%s\'\n \'Cancel\' to stop, \'OK\' to delete.",'tdomf'),$post->post_title)); ?>') ) { return true;}return false;"><?php _e('Delete','tdomf'); ?></a> | </span>
           <?php if($post->post_status == 'publish') { ?>
           <span class='view'><a href="<?php echo get_permalink($p->ID); ?>" title="<?php echo htmlentities(sprintf(__('View \'%s\'','tdomf'),$post->post_title)); ?>" rel="permalink"><?php _e('View','tdomf'); ?></a> | </span>
           <?php } ?>
            <span class='edit'><a href="post.php?action=edit&amp;post=<?php echo $p->ID; ?>" title="<?php echo htmlentities(__('Edit this submission','tdomf')); ?>"><?php _e('Edit','tdomf'); ?></a>
           <?php if(get_option(TDOMF_OPTION_SPAM)) { ?> |</span><?php } ?>
           <?php if(get_option(TDOMF_OPTION_SPAM)) { 
                 if(!$is_spam) { 
                     $bulk_sub_spamit = true; ?>
               <span class="spam"><a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'action' => 'spamit', 'post_id' => $p->ID, 'nonce' => 'tdomf-spamit_' . $p->ID)) ?>" onclick="if ( confirm('<?php echo js_escape(sprintf(__("You are about to flag this submission \'%s\' as spam\n \'Cancel\' to stop, \'OK\' to delete.",'tdomf'),$post->post_title)); ?>') ) { return true;}return false;"><?php _e('Spam','tdomf');  ?></a></span>
           <?php } else { 
                    $bulk_sub_hamit = true; ?>
              <span class="spam" title="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'action' => 'hamit', 'post_id' => $p->ID, 'nonce' => 'tdomf-hamit_' . $p->ID)) ?>" ><?php _e('Not Spam','tdomf'); ?></span>
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
        <li><?php $user_id = $last_edit->user_id;
                  
                  $name = __("N/A","tdomf");
                  if(isset($last_edit_data[TDOMF_KEY_NAME])) {
                     $name = $last_edit_data[TDOMF_KEY_NAME];
                  }
                  $email = __("N/A","tdomf");
                  if(isset($last_edit_data[TDOMF_KEY_EMAIL])) {
                     $email = $last_edit_data[TDOMF_KEY_EMAIL];
                  }
                  
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
        
        <?php  /* nothing to do if revisioning is disabled for the edits... */
               if($last_edit->revision_id != 0) { ?>
        
           <?php if($last_edit->state != 'approved') { ?>
              <span class='view'><a href="revision.php?revision=<?php echo $last_edit->revision_id; ?>"><?php _e('View','tdomf'); ?></a> |<span>
           <?php }?> 
           <?php if($last_edit->state == 'approved') { ?>
              <span class="edit"><a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'action' => 'revert_edit', 'edit_id' => $last_edit->edit_id, 'nonce' => 'tdomf-revert_edit_' . $last_edit->edit_id)) ?>"><?php _e('Revert','tdomf'); ?></a> | </span>
           <?php } else if($last_edit->state == 'unapproved' || $last_edit->state == 'spam') { ?>
               <span class="delete"><a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'action' => 'delete_edit', 'edit_id' => $last_edit->edit_id, 'nonce' => 'tdomf-delete_edit_' . $last_edit->edit_id)) ?>"><?php _e('Delete','tdomf'); ?></a> | </span>
               <span class="edit"><a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'action' => 'approve_edit', 'edit_id' => $last_edit->edit_id, 'nonce' => 'tdomf-approve_edit_' . $last_edit->edit_id)) ?>"><?php _e('Approve','tdomf'); ?></a> | </span>
           <?php } ?>
        <span class="edit"><a href="revision.php?action=diff&right=<?php echo $last_edit->revision_id; ?>&left=<?php echo $last_edit->current_revision_id; ?>"><?php _e('Compare','tdomf'); ?>
        <?php if(get_option(TDOMF_OPTION_SPAM)) { ?> |<?php } ?></span>           
        <?php if(get_option(TDOMF_OPTION_SPAM)) { 
                 if($last_edit->state == 'spam') { ?>
             <span class="spam"><a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'action' => 'hamit_edit', 'edit_id' => $last_edit->edit_id, 'nonce' => 'tdomf-hamit_edit_' . $last_edit->edit_id)) ?>" title="<?php echo htmlentities(__('Flag contributation as not being spam','tdomf')); ?>" ><?php _e('Not Spam','tdomf'); ?></span>
         <php    } else { ?>
              <span class="spam"><a href="<?php tdomf_get_mod_posts_url(array('echo'=> true, 'action' => 'spamit_edit', 'edit_id' => $last_edit->edit_id, 'nonce' => 'tdomf-spamit_edit_' . $last_edit->edit_id)) ?>" title="<?php echo htmlentities(__('Flag contributation as being spam','tdomf')); ?>" onclick="if ( confirm('<?php echo js_escape(__("You are about to flag this contribution as spam\n \'Cancel\' to stop, \'OK\' to delete.",'tdomf')); ?>') ) { return true;}return false;"><?php _e('Spam','tdomf');  ?></a></span>
        <?php    } }?>
        
        <?php } ?>
            
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

    <!-- Approve Edit
         Revert Last Edit
         Flag Edit as Spam
         Flag Edit as Not Spam
         Recheck Edits for Spam -->

<?php 
if(count($posts) > 0) { 
?>
    <div class="alignleft actions">
    <select name="action">
    <option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
    <?php if($bulk_sub_publish_now) { ?>
       <option value="publish_now"><?php _e('Publish Submission (Now)','tdomf'); ?></option>
    <?php } ?>
    <?php if($bulk_sub_publish) { ?>
       <option value="publish"><?php _e('Publish/Queue Submission','tdomf'); ?></option>
    <?php } ?>
    <?php if($bulk_sub_unpublish) { ?>
       <option value="unpublish"><?php _e('Un-publish Submission','tdomf'); ?></option>
    <?php } ?>
    <option value="delete"><?php _e('Delete Submission','tdomf'); ?></option>
    <?php if($bulk_sub_spamit) { ?>
       <option value="spamit"><?php _e('Mark Submission as Spam','tdomf'); ?></option>
    <?php } ?>
    <?php if($bulk_sub_hamit) { ?>
       <option value="hamit"><?php _e('Mark Submission as Not Spam','tdomf'); ?></option>
    <?php } ?>
    <?php if($bulk_sub_hamit || $bulk_sub_spamit) { ?>
       <option value="spam_recheck"><?php _e('Recheck Submssions for Spam','tdomf'); ?></option>
    <?php } ?>
    </select>
    <input type="submit" value="<?php _e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
    <?php wp_nonce_field('tdomf-moderate-bulk'); ?>
<?php 
} 
?>

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

</form>

   <?php
}

// Handle operations for this form
//
function tdomf_moderation_handler() {
   $message .= "";

   # this means a post was deleted
   #
   if(isset($_REQUEST['deleted'])) {
       $message .= __("Submissions deleted. ","tdomf");
   }
   
   // bulk actions
   
   if(isset($_REQUEST['doaction']) && isset($_REQUEST['action']) && isset($_REQUEST['post'])) {
      $posts = $_REQUEST['post'];
      $action = $_REQUEST['action'];
      if( $action != -1 && is_array($posts) && !empty($posts))
      {
         check_admin_referer('tdomf-moderate-bulk');
         switch($action) {
             
            case 'spam_recheck' :
                
                $spam_list = array();
                $ham_list = array();
                foreach($posts as $post) {
                   if(tdomf_check_submissions_spam($post)) {
                       $ham_list [] = $post;
                   } else {
                       $spam_list [] = $post;
                   }
                }
                tdomf_log_message('Akismet thinks these submissions are spam: ' .implode(", ", $spam_list) );
                $message .= sprintf(__("Marked these submissions as spam: %s.","tdomf"),implode(", ", $spam_list));
                tdomf_log_message('Akismet thinks these posts are not spam: ' .implode(", ", $ham_list) );
                $message .= " ";
                $message .= sprintf(__("Marked these submissions as not spam: %s.","tdomf"),implode(", ", $ham_list));
                break;
                
            case 'delete' :
                
                foreach($posts as $p) {
                   wp_delete_post($p);
                }
                tdomf_log_message('Deleted ' . implode(", ", $posts) . ' posts');
                $message .= sprintf(__("Deleted submissions: %s","tdomf"),implode(", ", $posts));
                break;
                
            case 'publish_now' :

               $list = "";
               foreach($posts as $p) {
                  if(!get_post_meta($p, TDOMF_KEY_SPAM)) {
                     // if we're going to publish the post, then it's not spam!
                     tdomf_ham_post($p);
                  }
                  tdomf_publish_post($p,false);
                  $list .= "<a href=\"".get_permalink($p)."\">".$p."</a>, ";
               }
               tdomf_log_message("Published $list posts");
               $message .= sprintf(__("Attempted to published these submissions immediately: %s","tdomf"),$list);                
               break;
               
           case 'publish' :

               $list = "";
               foreach($posts as $p) {
                  if(!get_post_meta($p, TDOMF_KEY_SPAM)) {
                     // if we're going to publish the post, then it's not spam!
                     tdomf_ham_post($p);
                  }
                  tdomf_publish_post($p);
                  $list .= "<a href=\"".get_permalink($p)."\">".$p."</a>, ";
               }
               tdomf_log_message("Published or queued $list posts");
               $message .= sprintf(__("Attempted to publish or queue these submissions: %s","tdomf"),$list);                
               break;
               
           case 'unpublish' :

               foreach($posts as $p) {
                  tdomf_unpublish_post($p);
               }
               tdomf_log_message("Un-published " .  implode(", ", $posts) . " posts");
               $message .= sprintf(__("Attempted to un-publish theses submissions: %s","tdomf"),implode(", ", $posts));                
               break;
               
           case 'spamit' :

               $spams = array();
               foreach($posts as $p) {
                  if(!get_post_meta($post, TDOMF_KEY_SPAM)) {
                     tdomf_spam_post($p);
                     $spams [] = $p;
                  }
               }
               tdomf_log_message("Marked as spam " .  implode(", ", $spams) . " posts");
               $message .= sprintf(__("Marked these submissions as spam: %s","tdomf"),implode(", ", $spams));                
               break;
               
           case 'hamit' :

               $hams = array();
               foreach($posts as $p) {
                   if(get_post_meta($post, TDOMF_KEY_SPAM)) {
                       tdomf_spam_post($p);
                       $hams [] = $p;
                   }
               }
               if(!empty($hams)) {
                 tdomf_log_message("Marked as ham " .  implode(", ", $hams) . " posts");
                 $message .= sprintf(__("Marked these submissions as not spam: %s","tdomf"),implode(", ", $hams));
               }
               break;
               
            default :
                tdomf_log_message('Unexpected bulk action ' . $action . ' in moderation screen!',TDOMF_LOG_BAD);
                $message .= sprintf(__("Unrecognised bulk action %s,","tdomf"),$action);
                break;
         }
      } // else no posts selected or bulk actions
      
   // individual actions
   
   // operations on posts/pages (submissions)
      
   } else  if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'publish_now') {
       
      $post_id = $_REQUEST['post'];
      check_admin_referer('tdomf-publish_'.$post_id);
      // if we're going to publish the post, then it's not spam!
      tdomf_ham_post($post_id);
      tdomf_publish_post($post_id,false);
      tdomf_log_message("Published post $post_id");
      $message .= sprintf(__("Published post <a href=\"%s\">%d</a>.","tdomf"),get_permalink($post_id),$post_id);
      
   } else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'publish') {
       
      $post_id = $_REQUEST['post'];
      check_admin_referer('tdomf-publish_'.$post_id);
      // if we're going to publish the post, then it's not spam!
      tdomf_ham_post($post_id);
      tdomf_publish_post($post_id);
      tdomf_log_message("Published post $post_id");
      $message .= sprintf(__("Published post <a href=\"%s\">%d</a>.","tdomf"),get_permalink($post_id),$post_id);
      
   } else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'unpublish') {
       
      $post_id = $_REQUEST['post'];
      check_admin_referer('tdomf-unpublish_'.$post_id);
      tdomf_unpublish_post($post_id);
      tdomf_log_message("Unpublished post $post_id");
      $message .= sprintf(__("Unpublished post %d.","tdomf"),$post_id);
      
   } else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'spamit') {
       
      $post_id = $_REQUEST['post'];
      check_admin_referer('tdomf-spamit_'.$post_id);
      if(!get_post_meta($post_id, TDOMF_KEY_SPAM)) {
         tdomf_spam_post($post_id);
         tdomf_log_message("Post $post_id submitted as spam");
         $message .= sprintf(__("Post %d flagged as spam","tdomf"),$post_id);
      } else {
         $message .= sprintf(__("Did not flag post %d as being spam as it is already flagged appropriately,","tdomf"),$post_id);
      }
      
   } else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'hamit') {
       
      $post_id = $_REQUEST['post'];
      check_admin_referer('tdomf-hamit_'.$post_id);
      if(get_post_meta($post_id, TDOMF_KEY_SPAM)) {
         tdomf_ham_post($post_id);
         tdomf_log_message("Post $post_id submitted as ham");
         $message .= sprintf(__("Post %d flagged as not being spam","tdomf"),$post_id);
      } else {
         $message .= sprintf(__("Did not flag post %d as not being spam as it is already flagged appropriately,","tdomf"),$post_id);
      }

   }
   
   // operations on edits (contributions)
   
   else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'approve_edit' ) {
       
       $edit_id = $_REQUEST['edit'];
       check_admin_referer('tdomf-approve_edit_'.$edit_id);
       
       $edit = tdomf_get_edit($edit_id);
       if($edit && ($edit->state == 'spam' || $edit->state == 'unapproved')) {
           if( $edit->state == 'spam') {
               tdomf_hamit_edit($edit);
           }
           wp_restore_post_revision($edit->revision_id);
           tdomf_set_state_edit('approved',$edit_id);
           tdomf_log_message("Edit $edit_id has been approved on post " . $edit->post_id);
           $message .= sprintf(__('Contribution to <a href="%s">Post %d</a> has been approved and published',"tdomf"),get_permalink($edit->post_id),$edit->post_id);
       } else {
           tdomf_log_message("Invalid $action performed on edit $edit_id",TDOMF_LOG_BAD);
           $message .= sprintf(__('Invalid action %s or invalid edit identifier %d!','tdomf'),$_REQUEST['action'],$edit_id);
       }
   } else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'revert_edit' ) {
       
       $edit_id = $_REQUEST['edit'];
       check_admin_referer('tdomf-revert_edit_'.$edit_id);

       $edit = tdomf_get_edit($edit_id);
       if($edit && $edit->state == 'approved' && $edit->revision_id != 0 
                && $edit->current_revision_id != 0) {
           wp_restore_post_revision($edit->current_revision_id);
           tdomf_set_state_edit('unapproved',$edit_id);
           tdomf_log_message("Edit $edit_id on post " . $edit->post_id . " has been reverted");
           $message .= sprintf(__('Contribution to <a href="%s">Post %d</a> has reverted to previous revision',"tdomf"),get_permalink($edit->post_id),$edit->post_id);
        } else {
           tdomf_log_message("Invalid $action performed on edit $edit_id",TDOMF_LOG_BAD);
           $message .= sprintf(__('Invalid action %s or invalid edit identifier %d!','tdomf'),$_REQUEST['action'],$edit_id);
        }
   } else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete_edit' ) {
       
       $edit_id = $_REQUEST['edit'];
       check_admin_referer('tdomf-delete_edit_'.$edit_id);
       
       $edit = tdomf_get_edit($edit_id);
       if($edit && $edit->state != 'approved') {
           $post_id = $edit->post_id;
           if($edit->revision_id != 0) {
               wp_delete_post_revision( $edit->revision_id );
               tdomf_log_message("Deleting revision $revision_id on post " . $post_id);
           }
           if($edit->current_revision_id != 0) {
               wp_delete_post_revision( $edit->current_revision_id );
               tdomf_log_message("Deleting revision $current_revision_id on post " . $post_id);
           }
           tdomf_delete_edits(array($edit_id));
           tdomf_log_message("Edit $edit_id on post " . $post_id . " has been deleted");
           $message .= sprintf(__('Contribution to <a href="%s">Post %d</a> has deleted',"tdomf"),get_permalink($edit->post_id),$edit->post_id);
        } else {
           tdomf_log_message("Invalid $action performed on edit $edit_id",TDOMF_LOG_BAD);
           $message .= sprintf(__('Invalid action %s or invalid edit identifier %d!','tdomf'),$_REQUEST['action'],$edit_id);
        }
   } else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'spamit_edit' ) {
       
       $edit_id = $_REQUEST['edit'];
       check_admin_referer('tdomf-spamit_edit_'.$edit_id);
       
       $edit = tdomf_get_edit($edit_id);
       if($edit && $edit->state != 'spam') {
           tdomf_spamit_edit($edit);
           tdomf_log_message("Marking edit $edit_id as spam!");
           $message .= sprintf(__('Contribution to <a href="%s">Post %d</a> has been flagged as spam',"tdomf"),get_permalink($edit->post_id),$edit->post_id);
       } else {
           tdomf_log_message("Invalid $action performed on edit $edit_id",TDOMF_LOG_BAD);
           $message .= sprintf(__('Invalid action %s or invalid edit identifier %d!','tdomf'),$_REQUEST['action'],$edit_id);
       }
   } else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'hamit_edit' ) {
       
       $edit_id = $_REQUEST['edit'];
       check_admin_referer('tdomf-hamit_edit_'.$edit_id);
       
       $edit = tdomf_get_edit($edit_id);
       if($edit && $edit->state == 'spam') {
           tdomf_spamit_edit($edit);
           tdomf_log_message("Marking edit $edit_id as not spam!");
           $message .= sprintf(__('Contribution to <a href="%s">Post %d</a> has been flagged as not being spam',"tdomf"),get_permalink($edit->post_id),$edit->post_id);
       } else {
           tdomf_log_message("Invalid $action performed on edit $edit_id",TDOMF_LOG_BAD);
           $message .= sprintf(__('Invalid action %s or invalid edit identifier %d!','tdomf'),$_REQUEST['action'],$edit_id);
       }
   }
   
   if(!empty($message)) { ?>
      <div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
   <?php }
}

?>
