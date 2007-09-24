<?php
/* Code for the options menu */

// add the options menu page to wp
function tdomf_add_options_page() {
  add_options_page(__("TDO Mini Forms Configure","tdomf"), __("TDOMF","tdomf"), 10, __FILE__, 'tdomf_show_options_menu');
}
add_action('admin_menu', 'tdomf_add_options_page');

function tdomf_handle_actions() {
   global $wpdb, $wp_roles;

  // Upgrade from v0.5
  $current_access_level = get_option(TDOMF_ACCESS_LEVEL);
  $current_notify_level = get_option(TDOMF_NOTIFY_LEVEL);
  if($current_access_level != false || $current_notify_level != false) {
    global $wp_roles;
    if (!isset($wp_roles)) { $wp_roles = new WP_Roles(); }
    $message = "Attempting to upgrade your options...<br/>";
    if($current_access_level != false) {
       delete_option(TDOMF_ACCESS_LEVEL);
       $access_roles = "";
       $roles = $wp_roles->roles;
       foreach($roles as $role) { 
       if(isset($role['capabilities']['level_'.$current_access_level])) {
          $access_roles .= $role['name'].';';
	  }
       }
       if(!empty($access_roles)) {
          update_option(TDOMF_ACCESS_ROLES,$access_roles);
       }
       #$message .= "Access roles upgraded to " . $access_roles . ". ";
    }
    if($current_notify_level != false) {
       delete_option(TDOMF_NOTIFY_LEVEL);
       $notify_role = "";
       $roles = $wp_roles->roles;
       foreach($roles as $role) { 
       if(isset($role['capabilities']['level_'.$current_notify_level])) {
          $notify_roles .= $role['name'].';';
	  break;
	  }
       }
       update_option(TDOMF_NOTIFY_ROLES,$notify_roles);
       #$message .= "Notify roles upgraded to " . $notify_roles . ". ";
    }
    $message .= "Upgrade complete. Please confirm new settings.<br/>"; ?>
    <div id="message" class="updated fade"><p><?php echo $message ?></p></div>
  <?php }
  else if(isset($_POST['copy_author'])){
    $fix_aut = get_option(TDOMF_AUTO_FIX_AUTHOR);
    if($fix_aut){ ?> 
      <div id="message" class="updated fade"><p><strong><font color="red"><?php _e("Submitters are automatically copied as Authors. Disable Auto-Correct Author first before using this function!","tdomf"); ?></font></strong></p></div>
    <?php } else {
    $fixed = tdomf_copy_authors_to_submitters(); ?>
    <div id="message" class="updated fade"><p><strong><?php echo __("Modified ","tdomf").$fixed.__(" Posts.","tdomf"); ?></strong></p></div>
    <?php }
  } else if(isset($_POST['tdomf_def_user'])){
       $def_cat = $_POST['tdomf_def_cat'];
       $def_aut = $_POST['tdomf_def_user'];
       $fix_aut = false;
       if(isset($_POST['tdomf_autocorrect_author'])) { $fix_aut = true; }

       $saveit = true;
       $message = "";
       
       $access_roles = "";
       $notify_roles = "";
       if (!isset($wp_roles)) { $wp_roles = new WP_Roles(); }
       $roles = $wp_roles->roles;
       foreach($roles as $role) { 
          if(isset($_POST['tdomf_access_'.$role['name']])){
             $access_roles .= $role['name'].';';
	  }
	  if(isset($_POST['tdomf_notify_'.$role['name']])){
             $notify_roles .= $role['name'].';';
	  }
       }
       if(empty($notify_roles)) {
          $message .= __("Warning: No-one will be notified to approved new submissions!","tdomf").'</br>';	       
       }
       if(empty($access_roles) && !isset($_POST['tdomf_special_access_anyone'])) {
	       $message .= '<font color="red">'.__("You must select either \"Anyone\" or some roles for access to the form!","tdomf").'</font></br>';
          $saveit = false;
       }
       // user id must be valid
       if(!get_userdata($def_aut)) {
	       $saveit = false;
	      $message .= '<font color="red">'.__("Default Author must be a valid user","tdomf").'</font><br/>';
       }
       if($saveit) {
	       if(isset($_POST['tdomf_special_access_anyone'])) {
		       delete_option(TDOMF_ACCESS_ROLES);
	       } else {
		       update_option(TDOMF_ACCESS_ROLES,$access_roles);
	       }
	       update_option(TDOMF_NOTIFY_ROLES,$notify_roles);
	       update_option(TDOMF_DEFAULT_CATEGORY,$def_cat);
	       update_option(TDOMF_DEFAULT_AUTHOR,$def_aut);
	       update_option(TDOMF_AUTO_FIX_AUTHOR,$fix_aut);
	       $message .= "Settings saved.<br/>";
	} 
	?>
         <div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
  <?php } else { 
	// Check for errors
	$message = "";
   
   $def_author = get_option(TDOMF_DEFAULT_AUTHOR);
   $def_author = new WP_User($def_author);
   if($def_author->has_cap("publish_posts")) {
      $message .= "<font color=\"red\"><b>".__("Error: Your default author must not have publish capabilities!").'</b></font><br/>';
   }
       
   $current_notify_roles = get_option(TDOMF_NOTIFY_ROLES);
   if($current_notify_roles == false || empty($current_notify_roles)) {
	   $message .= __("Warning: No role is set to be notified to approved new submissions!").'<br/>';
	}
	# doesn't work!
	#$current_access_roles = get_option(TDOMF_ACCESS_ROLES);
	#if($current_access_roles != false && empty($current_access_roles)) {
	#   $message .= '<font color="darkgrey">'.__("Warning: Only those who can already publish posts will be able to access the form.","tdomf").'</font><br/>';
	#}
	if(!empty($message)) { ?>
	   <div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
	<?php }
	}
}

// Display the menu to configure options for this plugin
function tdomf_show_options_menu() {
  global $wpdb, $wp_roles;
  
  tdomf_handle_actions();
  tdomf_show_menu_header(TDOMF_OPTIONS_INDEX); 
  
  ?>
  <p><?php _e("This plugin allows you to add a form to any page, post or template	that will allow non-registered or subscribers to submit a post that you can approve and publish.","tdomf"); ?></p>
  
  <h3><?php _e("Adding the form to a post or page","tdomf"); ?></h3>
<p>
<?php _e("When writing any post or page just insert this code in the textbox. The plugin will automatically replace it with the form when you publish the post.","tdomf"); ?>
<pre>
&lt;!--tdomf_form1--&gt;
</pre>
</p>

  <h3><?php _e("Adding the form to a template","tdomf"); ?></h3>
  <p>
<?php _e("You can use the code below to insert the form in any template.","tdomf"); ?>  
<pre>
&lt;?php tdomf_show_form(); ?&gt;
</pre>
</p>

  <h3><?php _e("Showing who submitted the post","tdomf"); ?></h3>
  <p>
<?php _e("You can add this code to your template, within \"the loop\" to show the submitter of a post.","tdomf"); ?>  
<pre>
&lt;?php tdomf_the_submitter(); ?&gt;
</pre>
<?php _e("or"); ?>
<pre>
&lt;?php echo tdomf_get_the_submitter(); ?&gt;
</pre>
</p>


	<h3><?php _e("Who can access the form?","tdomf"); ?></h3>
  
	<p><?php _e("You can control access to the form based on user users roles. You can chose \"Anyone\" if you want even unregistered users to be able to access the form. If a user can publish their own posts, when they use the form, the post will be automatically published. (Only roles that cannot publish are listed here).","tdomf"); ?> 
	 <br/><br/>

	 <?php $access_roles = get_option(TDOMF_ACCESS_ROLES);
	       if($access_roles != false) { $access_roles = explode(';', $access_roles); }
	 ?>
	 
   <!-- TODO: javascript to turn on/off different roles -->
	 
   <?php if (!isset($wp_roles)) { $wp_roles = new WP_Roles(); }
	       $roles = $wp_roles->roles; ?>

          <label for="tdomf_special_access_anyone">
   <input value="tdomf_special_access_anyone" type="checkbox" name="tdomf_special_access_anyone" id="tdomf_special_access_anyone" <?php if($access_roles == false) { ?>checked<?php } ?> onclick="tdomf_special_access_noone.checked=false" />
   Anyone (Special: Enabling this means anyone, registered and non-registed users can access the form and overrides all other options)<label>
   <br/><br/>
   
	 <?php foreach($roles as $role) {
		  if(!isset($role['capabilities']['publish_posts']) || ($access_roles != false && in_array($role['name'],$access_roles))) { ?>
		     <label for="tdomf_access_<?php echo ($role['name']); ?>">
		     <input value="tdomf_access_<?php echo ($role['name']); ?>" type="checkbox" name="tdomf_access_<?php echo ($role['name']); ?>" id="tdomf_access_<?php echo ($role['name']); ?>" <?php if($access_roles != false && in_array($role['name'],$access_roles)) { ?>checked<?php } ?> /> 
		      <?php echo $role['name']; ?>
            <?php if(strtolower($role['name']) == strtolower(get_option('default_role'))) { ?>
               (Default: Enabling this means that anyone who creates a new account will be able to access the form)
            <?php } ?>
           </label>
          <br/>
		     <?php
		  }
	       } ?>
   <br/>
   
         <input type="submit" name="tdomf_save_button" id="tdomf_save_button" value="<?php _e("Save","tdomf"); ?>" />
	 </p>
	       
        <h3><?php _e("Who gets notified?","tdomf"); ?></h3>
        
	<p><?php _e("When a form is submitted by someone who can't automatically publish their entry, someone who can approve or publish the posts will be notified. You can chose which roles will be notified.","tdomf"); ?> 
     <br/><br/>
	   
	 <?php $notify_roles = get_option(TDOMF_NOTIFY_ROLES);
	       if($notify_roles != false) { $notify_roles = explode(';', $notify_roles); } ?>
   
	 <?php if (!isset($wp_roles)) {
	          $wp_roles = new WP_Roles();
	       }
	       $roles = $wp_roles->roles;
	       foreach($roles as $role) { 
           if((isset($role['capabilities']['edit_others_posts'])
	           && isset($role['capabilities']['publish_posts'])) 
                   || ($notify_roles != false && in_array($role['name'],$notify_roles))) { ?>
		     <label for="tdomf_notify_<?php echo ($role['name']); ?>">
		     <input value="tdomf_notify_<?php echo ($role['name']); ?>" type="checkbox" name="tdomf_notify_<?php echo ($role['name']); ?>" id="tdomf_notify_<?php echo ($role['name']); ?>" <?php if($notify_roles != false && in_array($role['name'],$notify_roles)) { ?>checked<?php } ?> /> 
		      <?php echo $role['name']; ?> <br/>
		     </label>
		     <?php
		  }
	       } ?>
         <br/>
	 
	 <input type="submit" name="tdomf_save_button" id="tdomf_save_button" value="<?php _e("Save","tdomf"); ?>" />
	 </p>
	       
	<h3><?php _e("Default Category","tdomf"); ?></h3>

       <p><?php _e("You can select a default category that the entry will be added to by default. You can change always edit the entry before publishing.","tdomf"); ?>
	   <br/><br/>
	   
	         <?php $def_cat = get_option(TDOMF_DEFAULT_CATEGORY); ?>
	   
	   <b><?php _e("Default Category","tdomf"); ?></b> 

      <?php /* TODO: dropdown_categories($def_cat); */ ?>
	   
	   <SELECT NAME="tdomf_def_cat" id="tdomf_def_cat">   
	   <?php
           // display categories
        $query  = "SELECT cat_ID, cat_name, category_nicename ";
        $query .= "FROM $wpdb->categories ";
        $query .= "WHERE cat_ID > 0 ";
        $query .= "ORDER BY cat_ID DESC";
        $cats = $wpdb->get_results($query);
        if(!empty($cats)) {
           foreach($cats as $c) {
		   if($c->cat_ID == $def_cat ) {
	             echo "<OPTION VALUE=\"$c->cat_ID\" selected>$c->category_nicename\n";
		   } else {
                      echo "<OPTION VALUE=\"$c->cat_ID\">$c->category_nicename\n";
		   }
          }
        } ?>
	</select>
  <input type="submit" name="tdomf_save_button" id="tdomf_save_button" value="<?php _e("Save","tdomf"); ?>" />
	</p>
	
  <h3><?php _e("Default Author","tdomf"); ?></h3>
  
  <?php /* TODO: Provide a link to create a "dummy" user */ ?>
  
	<p><?php _e("You <b>must</b> pick a default user to be used as the \"author\" of the post. This user cannot be able to publish or edit posts.","tdomf"); ?> 
	  <br/><br/>
	  
	  <?php $def_aut = get_option(TDOMF_DEFAULT_AUTHOR); ?>
	  
	  <?php /* <b><?php _e("Default User ID","tdomf"); ?></b> <input type="text" name="tdomf_def_user" id="tdomf_def_user" size="3" tabindex="4" value="<?php echo $def_aut; ?>" /> */ ?>
    <b><?php _e("Default User","tdomf"); ?></b> 
    <select id="tdomf_def_user" name="tdomf_def_user">
    <?php $users = tdomf_get_all_users();
          foreach($users as $user) {
            $status = get_usermeta($user->ID,TDOMF_KEY_STATUS); 
            $user_obj = new WP_User($user->ID);
            #var_dump($user_obj);
            if($user->ID == $def_aut || !$user_obj->has_cap("publish_posts")) { ?>
              <option value="<?php echo $user->ID; ?>" <?php if($user->ID == $def_aut) { ?> selected <?php } ?> ><?php if($user_obj->has_cap("publish_posts")) {?><font color="red"><?php }?><?php echo $user->user_login; ?><?php if(!empty($status) && $status == "Banned") { ?> (Banned User) <?php } ?><?php if($user_obj->has_cap("publish_posts")) {?> (Error) </font><?php }?></option>
          <?php } } ?>
    </select>
    <input type="submit" name="tdomf_save_button" id="tdomf_save_button" value="<?php _e("Save","tdomf"); ?>" />
	  </p>
    
    <h3><?php _e("Author and Submitter fix","tdomf"); ?></h3>
    
	<p>
	<?php _e("If an entry is submitted by a subscriber and is published using the normal wordpress interface, the author can be changed to the person who published it, not submitted. Select this option if you want this to be automatically corrected. This problem only occurs on blogs that have more than one user who can publish.","tdomf"); ?>
	<br/><br/>
	
	<?php $fix_aut = get_option(TDOMF_AUTO_FIX_AUTHOR); ?>
	
	<b><?php _e("Auto-correct Author","tdomf"); ?></b>
	<input type="checkbox" name="tdomf_autocorrect_author" id="tdomf_autocorrect_author" tabindex="5" 	<?php if($fix_aut) echo "checked"; ?> >
  <input type="submit" name="tdomf_save_button" id="tdomf_save_button" value="<?php _e("Save","tdomf"); ?>" />
	</p>
	
	</form>

  <?php /* As of version 0.4, you can edit the submitter on the edit post page! 
  <h3><?php _e("Changing Post Submitters","tdomf"); ?></h3>
  
  <p>
  <?php _e("You may wish to modify the original submitter of a post. To do this, uncheck the auto-correct author option above, modify the post to use the author you want and then use the button below to copy the authors as the submitters of the post. Posts with the default author will be ignored. Only posts submitted via TDO Mini Forms will be updated.","tdomf"); ?>
  <br/><br/>
  <form method="post" action="<?php echo $_SERVER[REQUEST_URI]; ?>">
      <input type="hidden" name="copy_author" value="0" />
      <input type="submit" name="submit" value="<?php _e("Copy Existing Authors as Submitters","tdomf"); ?>" /> 
  </form>
  </p> */ ?>
  
<?php  
tdomf_show_menu_footer(); 
}


// marks current authors of submitted posts as the original submitters 
function tdomf_copy_authors_to_submitters() {
  global $wpdb;
  
  // grab posts
  $query = "SELECT ID, post_author ";
  $query .= "FROM $wpdb->posts ";
  $query .= "LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
  $query .= "WHERE meta_key = '".TDOMF_KEY_FLAG."' ";
  $query .= "ORDER BY ID DESC";
  $posts = $wpdb->get_results( $query );

  // default author
  $def_aut = get_option(TDOMF_DEFAULT_AUTHOR);
  
  // scan and correct posts
  if(!empty($posts)) {
    $count = 0;
    foreach($posts as $post) {
      $org_submitter_user_id = get_post_meta($post->ID, TDOMF_KEY_USER_ID, true);
      $org_author_id = $post->post_author;
      if($org_author_id != $def_aut && 
        ($org_submitter_user_id == false || $org_submitter_user_id != $org_author_id)){
          //echo "<!-- $post->ID : $org_author_id ; $def_aut || $org_submitter_user_id ; $org_author_id -->\n\n";
          $count++;
          $user = get_userdata($org_author_id);
          // do I have to d this for any post meta's I update?
          delete_post_meta($post->ID, TDOMF_KEY_USER_ID);
          delete_post_meta($post->ID, TDOMF_KEY_USER_NAME);
          add_post_meta($post->ID, TDOMF_KEY_USER_ID, $user->ID, true);
          add_post_meta($post->ID, TDOMF_KEY_USER_NAME, $user->user_login, true);
          update_usermeta($user->ID, TDOMF_KEY_FLAG, true);
          //echo "<!-- $user->ID : $user->user_login -->";
      }
    }
    return $count;
  } else {
    return 0;
  }
  return false;
}

?>
