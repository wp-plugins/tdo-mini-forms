<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

// TODO: Tidy up warnings!

// Return a count of posts from unregistered users
//
function tdomf_get_unregistered_users_posts_count() {
  global $wpdb;
  // This function doesn't work yet...
  $def_aut = get_option(TDOMF_DEFAULT_AUTHOR);
  if($def_aut != false) {
  	$query = "SELECT count(ID) ";
    $query .= "FROM $wpdb->posts ";
    $query .= "LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
    $query .= "WHERE meta_key = '".TDOMF_KEY_FLAG."' ";
    $query .= "WHERE post_author = '$def_aut' ";
    $query .= "OR post_author = '0' ";
    return intval($wpdb->get_var( $query ));
  }
  return 0;
}

function tdomf_overview_menu()  {
	global $wpdb,$wp_roles;

    // Initilise the plugin for the first time here. This gets called when you click the TDOMF button in the menu.
    // Doing it here means you can delete all the options!
    tdomf_init();

	// get feed_messages
	require_once(ABSPATH . WPINC . '/rss.php');
  
  if(!isset($wp_roles)) {
  	$wp_roles = new WP_Roles();
  }
  $roles = $wp_roles->role_objects;

?>
  <div class="wrap">
    <h2><?php _e('Welcome to TDO Mini Forms', 'tdomf') ?></h2>

    <div id="zeitgeist">
    	  <h2><?php _e('Latest Activity', 'tdomf') ?></h2>

    	  <h3><?php _e('Log', 'tdomf') ?><?php if(current_user_can('manage_options')) { ?><a href="admin.php?page=tdomf_show_log_menu" title="Full Log...">&raquo;</a><?php } ?></h3>

    	  <p><?php echo tdomf_get_log(5); ?></p>

        <?php if(get_option(TDOMF_OPTION_MODERATION)) { ?>

          <?php $posts = tdomf_get_unmoderated_posts(0,10);
          if(!empty($posts)) { ?>
            
        	  <h3><?php _e('Latest Submissions', 'tdomf'); ?><?php if(current_user_can('edit_others_posts')) { ?><a href="admin.php?page=tdomf_show_mod_posts_menu&f=0" title="Moderate Submissions...">&raquo;</a><?php } ?></h3>

          <ul>

              
                <?php foreach($posts as $p) { ?>
    	  			<li>"<?php echo $p->post_title; ?>" from <?php echo get_post_meta($p->ID, TDOMF_KEY_NAME, true); ?></li>
                <?php } } ?>
    	  </ul>

    	  <?php } ?>
        
          <?php $posts = tdomf_get_published_posts(0,10);
                if(!empty($posts)) { ?>

    	  <h3><?php _e('Latest Approved Submissions', 'tdomf'); ?><?php if(current_user_can('edit_others_posts')) { ?><a href="admin.php?page=tdomf_show_mod_posts_menu&f=1" title="Moderate Posts...">&raquo;</a><?php } ?></h3>

    	  <ul>
                  
                  
              <?php	foreach($posts as $p) { ?>
    	  			<li><a href="<?php echo get_permalink($p->ID); ?>">"<?php echo $p->post_title; ?>"</a> from <?php echo get_post_meta($p->ID, TDOMF_KEY_NAME, true); ?></li>
                <?php } } ?>
    	  </ul>

          

    	  <h3><?php _e('Stats', 'tdomf'); ?></h3>

          <?php $stat_sub_ever  = get_option('TDOMF_STAT_SUBMITTED');
                $stat_unmod     = tdomf_get_unmoderated_posts_count();
                $stat_sub_cur   = tdomf_get_submitted_posts_count();
                $stat_mod       = $stat_sub_cur - $stat_unmod; ?>

    	  <p><?php printf(__("You are using version %s (build %d) of the TDO Mini Forms plugin. There has been %d posts submitted and %d posts approved.","tdomf"),TDOMF_VERSION,get_option(TDOMF_VERSION_CURRENT),$stat_sub_ever,$stat_mod); ?>
        
        <?php /* TODO: Latest from thedeadone.net forum. Current forum plugin does not yet support RSS! */ ?>
        
        <?php $rss = fetch_rss('http://wordpress.org/support/rss/tags/tdo-mini-forms');
               if ( isset($rss->items) && 0 != count($rss->items) ) {
                 $rss->items = array_slice($rss->items, 0, 5); 
                 echo "<h3>".__('Latest Wordpress.org Comments','tdomf')."</h3><ul>";
                 foreach ($rss->items as $item) { ?>
                 <li><a href='<?php echo wp_filter_kses($item['link']); ?>'><?php echo wp_specialchars($item['title']); ?></a></li>
        <?php    }
                 echo "</ul>";
              } ?>
        
    </div>

    <?php echo "<p>".$message = tdomf_get_error_messages()."</p>";  ?>
    
    <p><?php _e("Use these links to get started:","tdomf"); ?></p>

    <ul>
      <li><a href="admin.php?page=tdomf_show_options_menu">Configure TDO Mini Forms</a></li>
      <li><a href="admin.php?page=tdomf_show_form_menu">Create your form</a></li>
      <li><a href="users.php?page=tdomf_your_submissions#tdomf_form1">See the form</a></li>
    </ul>

<p><?php _e('Need help with TDO Mini Forms? Please see the <a href="admin.php?page=tdomf_show_help_page">help page</a> or visit the <a href="http://thedeadone.net/forum">support forums</a>.',"tdomf"); ?></p>

    <h3><?php _e('Welcome', 'tdomf') ?></h3>

    <p>
    <?php _e("TDO Mini Forms plugin allows you to provide a form to your readers and users so that they can submit posts to your blog, even if they don't have rights to do so. You can control what type of users, such as unregistered users and subscribers, can access and use the form. Posts are submitted as draft so that you can approve them before they are published. (You can optionally turn this off so that submissions are automatically published). As of version 0.7, you can now also customise the form using widgets.","tdomf"); ?>
    </p>

    <div id="devnews">
    <h3><?php _e('Latest TDO Mini Forms News!',  'tdomf') ?></h3>

    <?php
      $rss = fetch_rss('http://thedeadone.net/index.php?tag=tdomf&feed=rss2');

      if ( isset($rss->items) && 0 != count($rss->items) )
      {
        $rss->items = array_slice($rss->items, 0, 1);
        foreach ($rss->items as $item)
        {
        ?>
          <h4><a href='<?php echo wp_filter_kses($item['link']); ?>'><?php echo wp_specialchars($item['title']); ?></a> &#8212; <?php echo human_time_diff(strtotime($item['pubdate'], time())); ?></h4>
          <p><?php echo '<strong>'.date("F, jS", strtotime($item['pubdate'])).'</strong> - '.$item['description']; ?></p>
        <?php
        }
      }
      else
      {
        ?>
        <p><?php printf(__('Newsfeed could not be loaded.  Check the <a href="%s">thedeadone.net</a> to check for updates.', 'tdomf'), 'http://thedeadone.net/index.php?tag=tdomf') ?></p>
        <?php
      }
    ?>
    </div>
    <br style="clear: both" />
   </div>
<?php
}
?>