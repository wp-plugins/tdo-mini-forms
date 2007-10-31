<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

///////////////////
// Uinstall Page //
///////////////////

// grab a list of all posts
//
function tdomf_get_all_posts() {
  global $wpdb;
	$query = "SELECT ID ";
	$query .= "FROM $wpdb->posts ";
	$query .= "ORDER BY ID DESC";
	return $wpdb->get_results( $query );
}

/////////////////////////////////
// Delete pre-configured options
//
function tdomf_reset_options() {
  global $wpdb, $wp_roles;
   
  echo "<span style='width:200px;'>";
  _e("Deleting Options... ","tdomf");
  echo "</span>";
  
  // This includes v0.6 options!
  //
  delete_option(TDOMF_ACCESS_LEVEL);
  delete_option(TDOMF_NOTIFY_LEVEL);
  delete_option(TDOMF_ACCESS_ROLES);
  delete_option(TDOMF_NOTIFY_ROLES);
  delete_option(TDOMF_DEFAULT_CATEGORY);
  delete_option(TDOMF_DEFAULT_AUTHOR);
  delete_option(TDOMF_AUTO_FIX_AUTHOR);
  delete_option(TDOMF_BANNED_IPS);
  delete_option(TDOMF_VERSION_CURRENT);
  delete_option(TDOMF_LOG);
  delete_option(TDOMF_OPTION_MODERATION);
  delete_option(TDOMF_OPTION_TRUST_COUNT);
  delete_option(TDOMF_OPTION_ALLOW_EVERYONE);
  delete_option(TDOMF_OPTION_AJAX);
  delete_option(TDOMF_OPTION_PREVIEW);
  delete_option(TDOMF_OPTION_FROM_EMAIL);
  delete_option(TDOMF_OPTION_AUTHOR_THEME_HACK);
  delete_option(TDOMF_OPTION_ADD_SUBMITTER);
  delete_option(TDOMF_OPTION_FORM_ORDER);  
  delete_option(TDOMF_STAT_SUBMITTED);
  
  echo "<span style='color:green;'>";
  _e("DONE","tdomf");  
  echo "</span><br/>";
  
  echo "<span style='width:200px;'>";
  _e("Resetting role capabilities... ","tdomf");
  echo "</span>";
  if(!isset($wp_roles)) {
    $wp_roles = new WP_Roles();
  }
  $roles = $wp_roles->role_objects;
  foreach($roles as $role) {
     // remove cap as it's not needed
     if(isset($role->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM])){
       $role->remove_cap(TDOMF_CAPABILITY_CAN_SEE_FORM);
     }
  }
  echo "<span style='color:green;'>";
  _e("DONE","tdomf"); 
  echo "</span><br/>";
  
  echo "<span style='width:200px;'>";
  _e("Deleting Widget Options (or at least the ones I can find!)... ","tdomf");
  echo "</span>";
  // Danger will robinson! If the table prefix is "tdomf_", you may end up
  // deleting critical Wordpress core options!
  if($table_prefix != "tdomf_") {
    $alloptions = wp_load_alloptions();
    foreach($alloptions as $id => $val) {
      if(preg_match('#^tdomf_.+#',$id)) {
        delete_option($id);
        echo "<!-- $id -->";
      }
    }
    echo "<span style='color:green;'>";
    _e("DONE","tdomf");
  } else {
    echo "<span style='color:red;'>";
    _e("FAIL","tdomf");
  }
  echo "</span><br/>";
}

// Uninstall everything else!
//
function tdomf_uninstall() {
  tdomf_reset_options();
  
  echo "<span style='width:200px;'>";
  _e("Removing info from all users (this may take a few minutes depending on number of users)... ","tdomf");
  echo "</span>";
  $users = tdomf_get_all_users();
  foreach($users as $user) {
    delete_usermeta($user->ID, TDOMF_KEY_FLAG);
    delete_usermeta($user->ID, TDOMF_KEY_STATUS);
  }
  echo "<span style='color:green;'>";
  _e("DONE","tdomf"); 
  echo "</span><br/>";
  
  // This includes v0.6 options!
  //
  echo "<span style='width:200px;'>";
  _e("Removing info from all posts (this may take a few minutes depending on number of posts)... ","tdomf");
  echo "</span>";
  $posts = tdomf_get_all_posts();
  foreach($posts as $post) {
    delete_option(TDOMF_NOTIFY.$post_id);
    delete_post_meta($post->ID, TDOMF_KEY_NOTIFY_EMAIL);
    delete_post_meta($post->ID, TDOMF_KEY_FLAG);
    delete_post_meta($post->ID, TDOMF_KEY_NAME);
    delete_post_meta($post->ID, TDOMF_KEY_EMAIL);
    delete_post_meta($post->ID, TDOMF_KEY_WEB);
    delete_post_meta($post->ID, TDOMF_KEY_IP);
    delete_post_meta($post->ID, TDOMF_KEY_USER_ID);
    delete_post_meta($post->ID, TDOMF_KEY_USER_NAME);
  }
  echo "<span style='color:green;'>";
  _e("DONE","tdomf"); 
  echo "</span><br/>";
    
  /*echo "<span style='width:200px;'>";
  _e("Removing any users created by TDOMF... ","tdomf");
  echo "</span>";
  // TODO: Delete created authors!
  echo "<span style='color:red;'>";
  _e("NOT DONE","tdomf"); echo "<br/>";
  echo "</span>";*/
}

// Display a help page
//
function tdomf_show_uninstall_menu() {
  ?>

  <div class="wrap">

    <h2><?php _e('Uninstall TDO Mini Forms', 'tdomf') ?></h2>

    <?php $plugin_name = TDOMF_FOLDER.'/tdomf.php';
          $deactivate_url = wp_nonce_url("plugins.php?action=deactivate&plugin=$plugin_name","deactivate-plugin_$plugin_name"); ?>
                    
    <?php if(isset($_REQUEST['action'])) {
            $action = $_REQUEST['action']; ?>
            <p>
    <?php   if($action == "reset_options") {
              check_admin_referer('tdomf-reset-options');
              tdomf_reset_options();
              ?>
              <p><a href='<?php echo $deactivate_url; ?>' title='Deactivate tdomf' class="delete">Final Step: Deactivate TDO Mini Forms Plugin</a></p>
              <?php
            } else if($action == "uninstall") {
              check_admin_referer('tdomf-uninstall');
              tdomf_uninstall();
              ?>
              <p><a href='<?php echo $deactivate_url; ?>' title='Deactivate tdomf' class="delete">Final Step: Deactivate TDO Mini Forms Plugin</a></p>
              <?php
            }  ?>
            </p>  
    <?php } else { ?>
            
            <p><?php _e("From here you can uninstall and remove some or all of TDO Mini Form's options and information.","tdomf"); ?></p>
            </div>
            
            <div class="wrap">
            <p><?php _e("You can simply remove just the settings/options. This will preserve submitter information on posts and users if you re-enable TDO Mini Forms later.","tdomf"); ?></p>
            <a href="<?php echo wp_nonce_url("admin.php?page=tdomf_show_uninstall_menu&action=reset_options",'tdomf-reset-options'); ?>" class='delete' ><?php _e("Remove Options","tdomf"); ?></a><br/>
            </div>
            
            <div class="wrap">
            <p><?php _e("This removes nearly <b>everything</b>. Any posts submitted, users created or pages created are not removed. However submitted posts are stripped of any information about TDO Mini Forms. If you re-enable TDO Mini Forms, posts previousily submitted will not turn up as submitted posts any more.","tdomf"); ?></p>
            <a href="<?php echo wp_nonce_url("admin.php?page=tdomf_show_uninstall_menu&action=uninstall",'tdomf-uninstall'); ?>" class='delete' ><?php _e("Uninstall Nearly Everything!","tdomf"); ?></a>
            </div>
            
    <?php } ?>
    
    <br/>

</div>

<?php
}
?>
