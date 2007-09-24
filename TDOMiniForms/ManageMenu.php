<?php
/* Code for the manage posts, users and ip menu */

// TODO: At some stage, move the user menu to it's own file

// grab a list of ips from where posts have been submitted
function tdomf_get_ips() {
    global $wpdb;
    $query = "SELECT DISTINCT meta_value AS ip ";
    $query .= "FROM $wpdb->posts ";
    $query .= "LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
    $query .= "WHERE meta_key = '".TDOMF_KEY_IP."' ";
    $query .= "ORDER BY meta_value DESC";
    return $wpdb->get_results( $query );
}

// grab a list of user ids of users that have submitted a post
function tdomf_get_submitted_users() {
    global $wpdb;
    $query = "SELECT DISTINCT ID AS user_id ";
    $query .= "FROM $wpdb->users ";
    $query .= "LEFT JOIN $wpdb->usermeta ON ($wpdb->users.ID = $wpdb->usermeta.user_id) ";
    $query .= "WHERE meta_key = '".TDOMF_KEY_FLAG."' ";
    $query .= "ORDER BY meta_value DESC";
    return $wpdb->get_results( $query );
}

// publish a post
function tdomf_publish_post($post_id) {
   $postargs = array (
     "ID"          => $post_id,
     "post_status" => "publish",
     /*"no_filter" => true,*/
   );
   wp_update_post($postargs);
}

// make a post draft
function tdomf_unpublish_post($post_id) {
   $postargs = array (
     "ID"          => $post_id,
     "post_status" => "draft",
     /*"no_filter" => true,*/
   );
   wp_update_post($postargs);
}

// grab a list of all submitted posts
function tdomf_get_all_posts() {
  global $wpdb;
	$query = "SELECT ID, post_title, meta_value, post_status ";
	$query .= "FROM $wpdb->posts ";
	$query .= "LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
  $query .= "WHERE meta_key = '".TDOMF_KEY_FLAG."' ";
	$query .= "ORDER BY ID DESC";
	return $wpdb->get_results( $query );
}



// add the manage menu page to wp
function tdomf_add_manage_page() {
  add_management_page(__("TDO Mini Forms Moderation","tdomf"), __("TDOMF","tdomf"), 10, __FILE__, 'tdomf_show_manage_menu');
  add_submenu_page('users.php', __("TDO Mini Forms Moderate Users","tdomf"), __("TDOMF","tdomf"), 10, __FILE__, 'tdomf_show_user_menu');
}
add_action('admin_menu', 'tdomf_add_manage_page');

// Handles any actions and displays results
function tdomf_manage_handler() {
  $message = "";
  
  // automatically fix posts
  $num_fixed_posts = tdomf_auto_fix_authors();
  if($num_fixed_posts != false && $num_fixed_posts > 0) {
    $message .= __("I have automatically fixed ","tdomf"). $num_fixed_posts.__(" posts.","tdomf");
  }
  
  if(isset($_REQUEST['action'])) {
    if(!empty($message)) { $message .= "<br/>"; }
    $action = $_REQUEST['action'];
    if($action == "publish" && isset($_REQUEST['post'])) {
      $post_id = $_REQUEST['post'];
      tdomf_publish_post($post_id);
      $message .= __("Published Post ID ","tdomf").$post_id;
    } else if($action == "unpublish" && isset($_REQUEST['post'])) {
      $post_id = $_REQUEST['post'];
      tdomf_unpublish_post($post_id);
      $message .= __("Moved Post ID ","tdomf").$post_id.__(" back to draft","tdomf");
    } else if($action == "ban" && isset($_REQUEST['user'])) {
       $user_id = $_REQUEST['user'];
       update_usermeta($user_id, TDOMF_KEY_FLAG, true);
       update_usermeta($user_id, TDOMF_KEY_STATUS, "Banned"); 
       $message .= __("Banned User ID ","tdomf").$user_id.__(" status","tdomf");
    } else if($action == "unban" && isset($_REQUEST['user'])) {
       $user_id = $_REQUEST['user'];
       update_usermeta($user_id, TDOMF_KEY_FLAG, true);
       update_usermeta($user_id, TDOMF_KEY_STATUS, "Normal");
       $message .= __("Reset User ID ","tdomf").$user_id.__(" status","tdomf");
    } else if($action == "trust" && isset($_REQUEST['user'])) {
       $user_id = $_REQUEST['user'];
       update_usermeta($user_id, TDOMF_KEY_FLAG, true);
       update_usermeta($user_id, TDOMF_KEY_STATUS, "Trusted");
       $message .= __("User ID ","tdomf").$user_id.__(" is now trusted","tdomf");
    } else if($action == "untrust" && isset($_REQUEST['user'])) {
       $user_id = $_REQUEST['user'];
       update_usermeta($user_id, TDOMF_KEY_FLAG, true);
       update_usermeta($user_id, TDOMF_KEY_STATUS, "Normal");
       $message .= __("Reset User ID ","tdomf").$user_id.__(" status","tdomf");
    } else if($action == "ban" && isset($_REQUEST['ip'])) {
       $banned_ip = $_REQUEST['ip'];
       $banned_ips = get_option(TDOMF_BANNED_IPS);
       if($banned_ips == false) {
         $banned_ips = $banned_ip.";";
       } else {
         $banned_ips .= $banned_ip.";";
       }
       update_option(TDOMF_BANNED_IPS,$banned_ips);
       $message .= __("IP ","tdomf").$banned_ip.__(" is now banned","tdomf");
    } else if($action == "unban" && isset($_REQUEST['ip'])) {
       $banned_ip = $_REQUEST['ip'];
       $banned_ips = get_option(TDOMF_BANNED_IPS);
       if($banned_ips == false) { $banned_ips = array(); }
       else { $banned_ips = split( ";", $banned_ips); }
       $updated_banned_ips = "";
       foreach($banned_ips as $ip) {
         if($ip != $banned_ip && !empty($ip) ) {
           $updated_banned_ips .= $ip.";";
         }
       }
       update_option(TDOMF_BANNED_IPS,$updated_banned_ips);
       $message .= __("IP ","tdomf").$banned_ip.__(" is now un-banned","tdomf");
    } else {
      $message .= __("Unknown/unsupported action \"","tdomf").$action.__("\"","tdomf");
    }
  }

  if(!empty($message)) { ?>
  <div id="message" class="updated fade"><p><strong><?php echo $message; ?></strong></p></div>
  <?php }
}

// Display the menu to manage users, ips and posts for this plugin
function tdomf_show_manage_menu() {
  global $wpdb;
  $options_uri = "options-general.php?page=TDOMiniForms".DIRECTORY_SEPARATOR."OptionsMenu.php";
  $our_uri = "edit.php?page=TDOMiniForms".DIRECTORY_SEPARATOR."ManageMenu.php";
  $users_uri = "users.php?page=TDOMiniForms".DIRECTORY_SEPARATOR."ManageMenu.php";
  
  tdomf_manage_handler(); 
        
  if(!isset($_REQUEST['mode'])){ $mode = "posts"; } 
  else { $mode = $_REQUEST['mode']; }
  
  if($mode == "ips") {
    tdomf_show_menu_header(TDOMF_IPS_INDEX);
    tdomf_get_ip_menu($our_uri);
  } else {
    // default mode is posts
    tdomf_show_menu_header();
    tdomf_get_posts_menu($our_uri);
  }
  echo "</form>";
  tdomf_show_menu_footer();
}

// cutdown version of above page for users!
function tdomf_show_user_menu(){
  $our_uri = "users.php?page=TDOMiniForms".DIRECTORY_SEPARATOR."ManageMenu.php";
  tdomf_manage_handler();
  tdomf_show_menu_header(TDOMF_USERS_INDEX);
  tdomf_get_users_menu($our_uri);
  echo "</form>";
  tdomf_show_menu_footer();
}

// display a table of posts!
function tdomf_get_posts_menu($oururi){
  $posts = tdomf_get_all_posts();
  if(!isset($_REQUEST['f'])){ 
    $f = "0"; 
  } else { 
    $f = $_REQUEST['f']; 
  } 
  // okay, so this is *not* the most efficent way to do this
  // but I'm lazy!
  if(!isset($_REQUEST['p']) || !is_numeric($_REQUEST['p'])){ 
    $page = 0; 
  } else { 
    $page = (int) $_REQUEST['p']; 
  } 
  // generate a filtered list
  if($f != "2") {
    $filtered_posts = array();
      foreach($posts as $p) {
        if(($f == "0" && $p->post_status == "draft")
        || ($f == "1" && $p->post_status == "publish")) {
          $filtered_posts[] = $p;
      }
    }
    $posts = $filtered_posts;
  }
  $max = count($posts);
  if($max > 0) {
    $offset_start = TDOMF_ITEMS_PER_PAGE * $page;
    $offset_end = $offset_start + TDOMF_ITEMS_PER_PAGE;
  } else {
    $offset_start = 0;
    $offset_end = TDOMF_ITEMS_PER_PAGE;
  }
  // print a title
  if($f == "0") { ?> <h3><?php _e("Unpublished Posts (Awaiting Approval) Submitted via TDOMiniForms","tdomf"); ?></h3> <?php }
  else if($f == "1") { ?> <h3><?php _e("Published/Moderated Posts Submitted via TDOMiniForms","tdomf"); ?></h3> <?php }
  else if($f == "2") { ?> <h3><?php _e("All Posts Submitted via TDOMiniForms","tdomf"); ?></h3> <?php }  
  ?>
  
    <p><?php _e("From here you can publish, unpublish or delete any submitted post. If you publish a post and the submitter left an email address they will be sent an email saying their post was published.","tdomf"); ?></p>
  
  <input type="hidden" name="mode" id="mode" value="posts"/>
  <input type="hidden" name="p" id="p" value="<?php $page; ?>"/>
  <?php /* TODO: Workaround to prevent "edit-post-panel" from marking post as not TDOMF! */ ?>
  <input type="hidden" name="tdomf_flag" id="tdomf_flag" value="1"/>
  <fieldset>
	  <b><?php _e("Filter Posts","tdomf"); ?></b>
      <select name="f">
      <option value="0" <?php if($f == "0"){ ?> selected <?php } ?>><?php _e("Unpublished (Awaiting approval)","tdomf"); ?>
        <option value="1" <?php if($f == "1"){ ?> selected <?php } ?>><?php _e("Published","tdomf"); ?>
        <option value="2" <?php if($f == "2"){ ?> selected <?php } ?>><?php _e("All","tdomf"); ?>
      </select>
      <input type="submit" name="submit" value="Show" /> 
  </fieldset>
  
<?php if($max > 0) { ?>

<table id="the-list-x" width="100%" cellpadding="3" cellspacing="3">
  <tr>
    <th scope="col"><?php _e("ID","tdomf"); ?></th>
    <th scope="col"><?php _e("Title","tdomf"); ?></th>
    <th scope="col"><?php _e("Submitter","tdomf"); ?></th>
    <th scope="col"><?php _e("IP","tdomf"); ?></th>
    <th scope="col"><?php _e("Status","tdomf"); ?></th>
    <th scope="col"><!-- view --></th>
    <th scope="col"><!-- un/publish--></th>
    <th scope="col"><!-- edit --></th>
    <th scope="col"><!-- delete --></th>
  </tr>

 <?php $showed = 0; 
     foreach($posts as $p) {
     $count++;
     if($count >= $offset_end) {
       break;
     }
     if($count >= $offset_start) {
       $showed++;
       if(($showed%2) == 0) { ?>
         <tr id='page-2' class=''>
      <?php } else { ?>
         <tr id='page-2' class='alternate'>
      <?php } ?>
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
      <?php /* TODO: Including "tdomf_flag" as a workaround to prevent "edit-post-panel" from marking post as not TDOMF! */ ?>
      <?php if($p->post_status == "draft") { ?>
         <a href="<?php echo $oururi."&action=publish&amp;post=$p->ID"; ?>&mode=posts&p=<?php echo $page; ?>&f=<?php echo $f; ?>&tdomf_flag=1" class="edit"><?php _e("Publish","tdomf"); ?></a>
      <?php } else { ?>
         <a href="<?php echo $oururi."&action=unpublish&amp;post=$p->ID"; ?>&mode=posts&p=<?php echo $page; ?>&f=<?php echo $f; ?>&tdomf_flag=1" class="edit"><?php _e("Un-publish","tdomf"); ?></a>
      <?php } ?>
      </td>
      <td><a href="post.php?action=edit&post=<?php echo $p->ID ?>" class="edit"><?php _e("Edit","tdomf"); ?></a></td>
      <td><a href="<?php echo wp_nonce_url("post.php?action=delete&amp;post=$p->ID", 'delete-post_' . $p->ID); ?>" class='delete' ><?php _e("Delete","tdomf"); ?></a></td>
  
    </tr>
 <?php } } ?>

 </table>

 
 
  <div class="navigation">
  <?php if($count < $max) { ?>
      <div class="alignleft"><a href="<?php echo $oururi; ?>&mode=posts&p=<?php echo ($page + 1); ?>&f=<?php echo $f; ?>">&laquo; <?php _e("Previous Entries","tdomf"); ?></a></div>
  <?php } ?>
    <?php if($page > 0) { ?>
      <div class="alignright"><a href="<?php echo $oururi; ?>&mode=posts&p=<?php echo ($page - 1); ?>&f=<?php echo $f; ?>"><?php _e("Next Entries","tdomf"); ?> &raquo;</a></div>
    <?php } ?>
  </div>

<?php } else { ?>

<p><?php _e("There are no posts to moderate.","tdomf"); ?></p>

<?php } /* post count */?>
  
  <?php
}

function tdomf_get_users_menu($oururi){
  global $current_user;
  
  if(!isset($_REQUEST['f'])){ 
    $f = "0"; 
  } else { 
    $f = $_REQUEST['f']; 
  }
  $users = tdomf_get_submitted_users();
 
  // okay, so this is *not* the most efficent way to do this
  // but I'm lazy!
  if(!isset($_REQUEST['p']) || !is_numeric($_REQUEST['p'])){ 
    $page = 0; 
  } else { 
    $page = (int) $_REQUEST['p']; 
  } 
  // generate a filtered list
  $filtered_users = array();
    foreach($users as $u) {
      if(is_numeric($u->user_id)) {
         $u1 = get_userdata($u->user_id);
         $status = get_usermeta($u1->ID,TDOMF_KEY_STATUS);
         $trust = false;
         $ban = false;
         if($status == "Trusted"){ $trust = true; }
         else if($status == "Banned"){ $ban = true; }
         
         if(($f == "0") ||
           ($f == "1" && $ban) ||
           ($f == "2" && !$ban) ||
           ($f == "3" && $trust) || 
           ($f == "4" && !$trust)) {
            $filtered_users[] = $u1;
         }
     }
  }
  $users = $filtered_users;
  $max = count($users);
  
  $showed = 0;
  if($max > 0) {
    $offset_start = TDOMF_ITEMS_PER_PAGE * $page;
    $offset_end = $offset_start + TDOMF_ITEMS_PER_PAGE;
  } else {
    $offset_start = 0;
    $offset_end = TDOMF_ITEMS_PER_PAGE;
  }
  // print a title
  if($f == "0") { ?> <h3><?php _e("All Users who have a post submitted via TDOMiniForms","tdomf"); ?></h3> <?php }
  else if($f == "1") { ?> <h3><?php _e("Users banned from using TDOMiniForms","tdomf"); ?></h3> <?php }
  else if($f == "2") { ?> <h3><?php _e("Users (not including banned) who have a post submitted via TDOMiniForms","tdomf"); ?></h3> <?php }
  else if($f == "3") { ?> <h3><?php _e("Trusted Users who have a post submitted via TDOMiniForms","tdomf"); ?></h3> <?php }
  else if($f == "4") { ?> <h3><?php _e("Users (not including trusted) who have a post submitted via TDOMiniForms","tdomf"); ?></h3> <?php }
  ?>
 
  <p><?php _e("You can ban (and un-ban) any registered user (besides admins). This means the user cannot use the forms. It has no impact on anything else. i.e. they can still read, comment and post. You can also make a user \"trusted\". This means that anything they submit using the form is automatically published as if they were an Editor or an Admin.","tdomf"); ?></p>

  <?php $def_id = get_option(TDOMF_DEFAULT_AUTHOR); 
        $def_aut = get_userdata($def_id);
        echo "<p>".__("Default User is ","tdomf")."<b>$def_aut->user_login</b> ($def_id).</p>"; ?>
  
  <?php get_currentuserinfo();
        echo "<p>".__("You are ","tdomf")."<b>$current_user->user_login</b> ($current_user->ID).</p>"; ?>

  <input type="hidden" name="mode" id="mode" value="users"/>
  <input type="hidden" name="p" id="p" value="<?php $page; ?>"/>
  <fieldset>
	  <b><?php _e("Filter Users","tdomf"); ?></b>
      <select name="f">
      <option value="0" <?php if($f == "0"){ ?> selected <?php } ?>><?php _e("All Users","tdomf"); ?>
        <option value="1" <?php if($f == "1"){ ?> selected <?php } ?>><?php _e("Banned Users","tdomf"); ?>
        <option value="2" <?php if($f == "2"){ ?> selected <?php } ?>><?php _e("Hide Banned Users","tdomf"); ?>
        <option value="3" <?php if($f == "3"){ ?> selected <?php } ?>><?php _e("Trusted Users","tdomf"); ?>
        <option value="4" <?php if($f == "4"){ ?> selected <?php } ?>><?php _e("Hide Trusted Users","tdomf"); ?>
      </select>
      <input type="submit" name="submit" value="<?php _e("Show","tdomf"); ?>" /> 
  </fieldset>
  
<?php if($max > 0) { ?>

<table id="the-list-x" width="100%" cellpadding="3" cellspacing="3">
  <tr>
    <th scope="col"><?php _e("ID","tdomf"); ?></th>
    <th scope="col"><?php _e("Login","tdomf"); ?></th>
    <th scope="col"><?php _e("Display Name","tdomf"); ?></th>
    <th scope="col"><?php _e("Level","tdomf"); ?></th>
    <th scope="col"><?php _e("Status","tdomf"); ?></th>
    <th scope="col"><!-- un/trust --></th>
    <th scope="col"><!-- ban --></th>
  </tr>

 <?php foreach($users as $u) {

     $status = get_usermeta($u->ID,TDOMF_KEY_STATUS);
     if($status == false){ $status = "Normal"; }
     $trust = false;
     $ban = false;
     if($status == "Trusted"){ $trust = true; }
     else if($status == "Banned"){ $ban = true; }
   
       $count++;
       if($count >= $offset_end) {
         break;
       }
       if($count >= $offset_start) {
         $showed++;
         if(($showed%2) == 0) { ?>
           <tr id='page-2' class=''>
        <?php } else { ?>
           <tr id='page-2' class='alternate'>
        <?php } ?>
        
         <th scope="row"><?php echo $u->ID; ?></th>
         <td><a href="edit.php?author=<?php echo $u->ID; ?>" ><?php echo $u->user_login; ?></a></td>
         <td><a href="user-edit.php?user_id=<?php echo $u->ID ?>" ><?php echo $u->display_name; ?></a></td>
         <td><?php echo $u->user_level; ?></td>
         <td>
    <?php
    if($u->user_level >= 10) { ?> <?php _e("N/A","tdomf"); ?> <?php }
    else if($ban) { ?> <?php _e("Banned","tdomf"); ?> <?php }
    else { _e($status,"tdomf"); } ?>
    </td>
    <td>
    <?php
    if($u->user_level >= 10) { ?> <?php _e("N/A","tdomf"); ?> <?php }
    else if($ban) { ?> <?php _e("Banned","tdomf"); ?> <?php }
    else if($trust) { ?>
    <a href="<?php echo $oururi."&action=untrust&amp;user=$u->ID"; ?>&mode=users&p=<?php echo $page; ?>&f=<?php echo $f; ?>" class="edit"><?php _e("Un-Trust","tdomf"); ?></a>
    <?php } else { ?>
    <a href="<?php echo $oururi."&action=trust&amp;user=$u->ID"; ?>&mode=users&p=<?php echo $page; ?>&f=<?php echo $f; ?>" class="edit"><?php _e("Trust","tdomf"); ?></a>
    <?php } ?>
    </td>
    <td>
    <?php
    if($u->user_level >= 10) { ?> <?php _e("N/A","tdomf"); ?> <?php }
     else if($ban) { ?>
     <a href="<?php echo $oururi."&action=unban&amp;user=$u->ID"; ?>&mode=users&p=<?php echo $page; ?>&f=<?php echo $f; ?>" class="edit"><?php _e("Un-Ban","tdomf"); ?></a>
    <?php } else { ?>
    <a href="<?php echo $oururi."&action=ban&amp;user=$u->ID"; ?>&mode=users&p=<?php echo $page; ?>&f=<?php echo $f; ?>" class="edit"><?php _e("Ban","tdomf"); ?></a>
    <?php } ?>
    </td>
    </tr>
    
 <?php } } ?>

 </table>

 
 
  <div class="navigation">
  <?php if($count < $max ) { ?>
      <div class="alignleft"><a href="<?php echo $oururi; ?>&mode=users&p=<?php echo ($page + 1); ?>&f=<?php echo $f; ?>">&laquo; <?php _e("Previous Entries","tdomf"); ?></a></div>
  <?php } ?>
    <?php if($page > 0) { ?>
      <div class="alignright"><a href="<?php echo $oururi; ?>&mode=users&p=<?php echo ($page - 1); ?>&f=<?php echo $f; ?>"><?php _e("Next Entries","tdomf"); ?> &raquo;</a></div>
    <?php } ?>
  </div>

<?php } else { ?>

<p><?php _e("There are no users to moderate.","tdomf"); ?></p>

<?php } /* post count */?>
  
  <?php
}

// generate ip moderation menu
function tdomf_get_ip_menu($oururi){
  if(!isset($_REQUEST['f'])){ 
    $f = "0"; 
  } else { 
    $f = $_REQUEST['f']; 
  }
  $submitted_ips = tdomf_get_ips();
  $banned_ips = get_option(TDOMF_BANNED_IPS);
  if($banned_ips == false) { $banned_ips = array(); }
  else { $banned_ips = split( ";", $banned_ips); }
  // construct array of ip status
  $ips = array();
  if($f == "0" || $f == "2") {
    foreach($submitted_ips as $ip) {
      if(!empty($ip->ip)) {
        $ips[$ip->ip] = "Normal";
      }
    }
  }
  foreach($banned_ips as $ip) {
    if(!empty($ip)) {
      if(isset($ips[$ip]) && $f == "2") {
        // we want to remove banned!
        unset($ips[$ip]);
      } else {
        $ips[$ip] = "Banned";
      }
    }
  }
  $max = count($ips);
 
  // okay, so this is *not* the most efficent way to do this
  // but I'm lazy!
  if(!isset($_REQUEST['p']) || !is_numeric($_REQUEST['p'])){ 
    $page = 0; 
  } else { 
    $page = (int) $_REQUEST['p']; 
  } 
  $showed = 0;
  if($max > 0) {
    $offset_start = TDOMF_ITEMS_PER_PAGE * $page;
    $offset_end = $offset_start + TDOMF_ITEMS_PER_PAGE;
  } else {
    $offset_start = 0;
    $offset_end = TDOMF_ITEMS_PER_PAGE;
  }
  // print a title
  if($f == "0") { ?> <h3><?php _e("All IPs of posts submitted via TDOMiniForms","tdomf"); ?></h3> <?php }
  else if($f == "1") { ?> <h3><?php _e("IPs banned from using TDOMiniForms","tdomf"); ?></h3> <?php }
  else if($f == "2") { ?> <h3><?php _e("IPs (not including banned) that have a post submitted via TDOMiniForms","tdomf"); ?></h3> <?php }
  ?>

  <p><?php _e("You can ban an IP address. This means no-one with this IP address can use the form. It has no impact on anything else. i.e. they can still read, comment and post.","tdomf"); ?></p>

  <input type="hidden" name="mode" id="mode" value="ips"/>
  <input type="hidden" name="p" id="p" value="<?php $page; ?>"/>
  <fieldset>
	  <b><?php _e("Filter IPs","tdomf"); ?></b>
      <select name="f">
      <option value="0" <?php if($f == "0"){ ?> selected <?php } ?>><?php _e("All IPs","tdomf"); ?>
        <option value="1" <?php if($f == "1"){ ?> selected <?php } ?>><?php _e("Banned IPs","tdomf"); ?>
        <option value="2" <?php if($f == "2"){ ?> selected <?php } ?>><?php _e("Hide Banned IPs","tdomf"); ?>
      </select>
      <input type="submit" name="submit" value="<?php _e("Show","tdomf"); ?>" /> 
  </fieldset>
  
<?php if($max > 0) { ?>

<table id="the-list-x" width="100%" cellpadding="3" cellspacing="3">
  <tr>
    <th scope="col"><?php _e("IP","tdomf"); ?></th>
    <th scope="col"><?php _e("Status","tdomf"); ?></th>
    <th scope="col"><!-- ban --></th>
  </tr>

 <?php foreach($ips as $ip => $status ) {
       $count++;
       if($count >= $offset_end) {
         break;
       }
       if($count >= $offset_start) {
         $showed++;
         if(($showed%2) == 0) { ?>
           <tr id='page-2' class=''>
        <?php } else { ?>
           <tr id='page-2' class='alternate'>
        <?php } ?>
        
         <th scope="row"><?php echo $ip; ?></th>
         <td><?php _e($status,"tdomf"); ?></td>
    <td>
    <?php
    if($status == "Banned") { ?>
    <a href="<?php echo $oururi."&action=unban&amp;ip=$ip"; ?>&mode=ips&p=<?php echo $page; ?>&f=<?php echo $f; ?>" class="edit"><?php _e("Un-Ban","tdomf"); ?></a>
    <?php } else { ?>
    <a href="<?php echo $oururi."&action=ban&amp;ip=$ip"; ?>&mode=ips&p=<?php echo $page; ?>&f=<?php echo $f; ?>" class="edit"><?php _e("Ban","tdomf"); ?></a>
    <?php } ?>
    </td>
    </tr>
    
 <?php } } ?>

 </table>

 
 
  <div class="navigation">
  <?php if($count < $max) { ?>
      <div class="alignleft"><a href="<?php echo $oururi; ?>&mode=users&p=<?php echo ($page + 1); ?>&f=<?php echo $f; ?>">&laquo; <?php _e("Previous Entries","tdomf"); ?></a></div>
  <?php } ?>
    <?php if($page > 0) { ?>
      <div class="alignright"><a href="<?php echo $oururi; ?>&mode=users&p=<?php echo ($page - 1); ?>&f=<?php echo $f; ?>"><?php _e("Next Entries","tdomf"); ?> &raquo;</a></div>
    <?php } ?>
  </div>

<?php } else { ?>

<p><?php _e("There are no IPs to moderate.","tdomf"); ?></p>

<?php } /* post count */?>
  
  <?php
}

?>
