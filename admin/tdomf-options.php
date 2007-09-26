<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

///////////////////////////////
// Code for the options menu //
///////////////////////////////

// TODO: Save Settings >> See Form

// Display the menu to configure options for this plugin
//
function tdomf_show_options_menu() {
  global $wpdb, $wp_roles;

  tdomf_handle_options_actions();

  ?>

  <div class="wrap">

    <h2><?php _e('TDOMF Options', 'tdomf') ?></h2>

    <?php $create_form_link = "admin.php?page=tdomf_show_options_menu&action=create_form_page";
          if(function_exists('wp_nonce_url')){
          	$create_form_link = wp_nonce_url($create_form_link, 'tdomf-create-form-page');
          } ?>

    <a href="<?php echo $create_form_link; ?>"><?php _e("Create a page with the form automatically","tdomf"); ?> &raquo;</a>

    <form method="post" action="admin.php?page=tdomf_show_options_menu">

    <?php if(function_exists('wp_nonce_field')){ wp_nonce_field('tdomf-options-save'); } ?>

      	<h3><?php _e("Who can access the form?","tdomf"); ?></h3>

	<p><?php _e("You can control access to the form based on user users roles. You can chose \"Unregistered Users\" if you want anyone to be able to access the form. If a user can publish their own posts, when they use the form, the post will be automatically published. (Only roles that cannot publish are listed here).","tdomf"); ?>

   <br/><br/>

	<?php if (!isset($wp_roles)) { $wp_roles = new WP_Roles(); }
	       $roles = $wp_roles->role_objects;
          $access_roles = array();
          foreach($roles as $role) {
             if(!isset($role->capabilities['publish_posts'])) {
                if($role->name != get_option('default_role')) {
                   array_push($access_roles,$role->name);
                } else {
                   $def_role = $role->name;
                }
             }
          } ?>

          <script type="text/javascript">
         //<![CDATA[
          function tdomf_unreg_user() {
            var flag = document.getElementById("tdomf_special_access_anyone").checked;
            if(flag) {
            <?php if(isset($def_role)) {?>
               document.getElementById("tdomf_access_<?php echo $def_role; ?>").checked = !flag;
            <?php } ?>
            <?php foreach($access_roles as $role) { ?>
               document.getElementById("tdomf_access_<?php echo $role; ?>").checked = !flag;
            <?php } ?>
            }
            <?php if(isset($def_role)) {?>
            document.getElementById("tdomf_access_<?php echo $def_role; ?>").disabled = flag;
            <?php } ?>
            <?php foreach($access_roles as $role) { ?>
            document.getElementById("tdomf_access_<?php echo $role; ?>").disabled = flag;
            <?php } ?>
           }
           <?php if(isset($def_role)) { ?>
           function tdomf_def_role() {
              var flag = document.getElementById("tdomf_access_<?php echo $def_role; ?>").checked;
              if(flag) {
              <?php foreach($access_roles as $role) { ?>
               //document.getElementById("tdomf_access_<?php echo $role; ?>").disabled = flag;
               document.getElementById("tdomf_access_<?php echo $role; ?>").checked = flag;
              <?php } ?>
              } else {
              <?php foreach($access_roles as $role) { ?>
               //document.getElementById("tdomf_access_<?php echo $role; ?>").disabled = flag;
              <?php } ?>
              }
           }
           <?php } ?>
           //-->
           </script>

          <label for="tdomf_special_access_anyone">
   <input value="tdomf_special_access_anyone" type="checkbox" name="tdomf_special_access_anyone" id="tdomf_special_access_anyone" <?php if(get_option(TDOMF_OPTION_ALLOW_EVERYONE) != false) { ?>checked<?php } ?> onClick="tdomf_unreg_user();" />
   <?php _e("Unregistered Users"); ?>
           </label><br/>

   <?php if(isset($def_role)) { ?>
             <label for="tdomf_access_<?php echo ($def_role); ?>">
             <input value="tdomf_access_<?php echo ($def_role); ?>" type="checkbox" name="tdomf_access_<?php echo ($def_role); ?>" id="tdomf_access_<?php echo ($def_role); ?>"  <?php if(isset($wp_roles->role_objects[$def_role]->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM])) { ?> checked <?php } ?> onClick="tdomf_def_role()" <?php if(get_option(TDOMF_OPTION_ALLOW_EVERYONE) != false) { ?> disabled <?php } ?> />
             <?php echo $wp_roles->role_names[$def_role]." ".__("(default role for new users)"); ?>
             </label><br/>
          <?php } ?>

          <?php foreach($access_roles as $role) { ?>
             <label for="tdomf_access_<?php echo ($role); ?>">
             <input value="tdomf_access_<?php echo ($role); ?>" type="checkbox" name="tdomf_access_<?php echo ($role); ?>" id="tdomf_access_<?php echo ($role); ?>" <?php if(isset($wp_roles->role_objects[$role]->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM])) { ?> checked <?php } ?> <?php if(get_option(TDOMF_OPTION_ALLOW_EVERYONE) != false) { ?> disabled <?php } ?> />
             <?php echo $wp_roles->role_names[$role]; ?>
             </label><br/>
          <?php } ?>
	 </p>

        <h3><?php _e("Who gets notified?","tdomf"); ?></h3>

	<p><?php _e("When a form is submitted by someone who can't automatically publish their entry, someone who can approve or publish the posts will be notified by email. You can chose which roles will be notified. If you select no role, no-one will be notified.","tdomf"); ?>
     <br/><br/>

	 <?php $notify_roles = get_option(TDOMF_NOTIFY_ROLES);
	       if($notify_roles != false) { $notify_roles = explode(';', $notify_roles); }  ?>

	 <?php foreach($roles as $role) {
           if(isset($role->capabilities['edit_others_posts'])
	           && isset($role->capabilities['publish_posts'])) { ?>
		     <label for="tdomf_notify_<?php echo ($role->name); ?>">
		     <input value="tdomf_notify_<?php echo ($role->name); ?>" type="checkbox" name="tdomf_notify_<?php echo ($role->name); ?>" id="tdomf_notify_<?php echo ($role->name); ?>" <?php if($notify_roles != false && in_array($role->name,$notify_roles)) { ?>checked<?php } ?> />
		      <?php echo $wp_roles->role_names[$role->name]; ?> <br/>
		     </label>
		     <?php
		  }
	       } ?>
         <br/>

	 </p>

	<h3><?php _e("Default Category","tdomf"); ?></h3>

       <p><?php _e("You can select a default category that the entry will be added to by default. You can change always edit the entry before publishing.","tdomf"); ?>
	   <br/><br/>

	         <?php $def_cat = get_option(TDOMF_DEFAULT_CATEGORY); ?>

	   <b><?php _e("Default Category","tdomf"); ?></b>

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
	</p>

  <h3><?php _e("Default Author","tdomf"); ?></h3>

	<p><?php _e("You <b>must</b> pick a default user to be used as the \"author\" of the post. This user cannot be able to publish or edit posts.","tdomf"); ?>
	  <br/><br/>

	  <?php $def_aut = get_option(TDOMF_DEFAULT_AUTHOR);
           $def_aut_bad = false; ?>

	 <b><?php _e("Default Author","tdomf"); ?></b>
    <select id="tdomf_def_user" name="tdomf_def_user">
    <?php $users = tdomf_get_all_users();
          $cnt_users = 0;
          foreach($users as $user) {
            $status = get_usermeta($user->ID,TDOMF_KEY_STATUS);
            $user_obj = new WP_User($user->ID);
            if($user->ID == $def_aut || (!$user_obj->has_cap("publish_posts") && !$user_obj->has_cap(TDOMF_CAPABILITY_CAN_SEE_FORM))) {
               $cnt_users++;
               ?>
              <option value="<?php echo $user->ID; ?>" <?php if($user->ID == $def_aut) { ?> selected <?php } ?> ><?php if($user_obj->has_cap("publish_posts")) {?><font color="red"><?php }?><?php echo $user->user_login; ?><?php if(!empty($status) && $status == TDOMF_USER_STATUS_BANNED) { ?> (Banned User) <?php } ?><?php if($user_obj->has_cap("publish_posts")) { $def_aut_bad = true; ?> (Error) </font><?php }?></option>
          <?php } } ?>
    </select>

    <br/><br/>

    <?php if($def_aut_bad || $cnt_users <= 0) { ?>

    <?php $create_user_link = "admin.php?page=tdomf_show_options_menu&action=create_dummy_user";
	      if(function_exists('wp_nonce_url')){
	          $create_user_link = wp_nonce_url($create_user_link, 'tdomf-create-dummy-user');
          } ?>

    <a href="<?php echo $create_user_link; ?>">Create a dummy user &raquo;</a>
    <?php } ?>

    </p>

    <h3><?php _e("Author and Submitter fix","tdomf"); ?></h3>

	<p>
	<?php _e("If an entry is submitted by a subscriber and is published using the normal wordpress interface, the author can be changed to the person who published it, not submitted. Select this option if you want this to be automatically corrected. This problem only occurs on blogs that have more than one user who can publish.","tdomf"); ?>
	<br/><br/>

	<?php $fix_aut = get_option(TDOMF_AUTO_FIX_AUTHOR); ?>

	<b><?php _e("Auto-correct Author","tdomf"); ?></b>
	<input type="checkbox" name="tdomf_autocorrect_author" id="tdomf_autocorrect_author"  	<?php if($fix_aut) echo "checked"; ?> >
	</p>

	<h3><?php _e('Auto Trust Submitter Count',"tdomf"); ?></h3>

	<p>
	<?php _e('This only counts for submitters who register with your blog and submit using a user account. You can have the user automatically changed to "trusted" after a configurable number of approved submissions. Setting it the value to 0, means that a registered user is automatically trusted. Settign it to -1, disables the feature. A trusted user can still be banned.',"tdomf"); ?>
	</p>

	<p>
	<b><?php _e("Auto Trust Submitter Count","tdomf"); ?></b>
	<input type="text" name="tdomf_trust_count" id="tdomf_trust_count" size="3" value="<?php echo get_option(TDOMF_OPTION_TRUST_COUNT); ?>" />
	</p>

	<h3><?php _e('Turn On/Off Moderation',"tdomf"); ?> </h3>

	<p>
	<?php _e('<b>It is not recommended to turn off moderation.</b> Someone should always approve submissions from anonoymous users otherwise your webpage becomes a source for spammers and bots. However this feature has been requested too many times to not include. I recommend you use the "Auto Trust Submitter Count" instead if you want to enable automatic posting from users. Turning off moderation does not prevent you from banning specific users and IP address or deleting or setting to draft submitted posts.',"tdomf"); ?>
    </p>

    <?php $on_mod = get_option(TDOMF_OPTION_MODERATION); ?>

	</p>
	<b><?php _e("Enable Moderation","tdomf"); ?></b>
	<input type="checkbox" name="tdomf_moderation" id="tdomf_moderation"  	<?php if($on_mod) echo "checked"; ?> >
	</p>

    <h3><?php _e('Preview',"tdomf"); ?> </h3>

	<p>
	<?php _e('If your chosen widgets support preview, you can allow users to preview their post before submission',"tdomf"); ?>
    </p>

    <?php $on_preview = get_option(TDOMF_OPTION_PREVIEW); ?>

	</p>
	<b><?php _e("Enable Preview","tdomf"); ?></b>
	<input type="checkbox" name="tdomf_preview" id="tdomf_preview"  <?php if($on_preview) echo "checked"; ?> >
	</p>

    <?php /* Not supported in this version

	<h3><?php _e('AJAX',"tdomf"); ?> </h3>

	<p>
	<?php _e('You enable or disable AJAX as long as your chosen widgets all support AJAX.',"tdomf"); ?>
    </p>

    <?php $on_ajax = get_option(TDOMF_OPTION_AJAX); ?>

	</p>
	<b><?php _e("Enable AJAX","tdomf"); ?></b>
	<input type="checkbox" name="tdomf_ajax" id="tdomf_ajax"  <?php if($on_ajax) echo "checked"; ?> >
	</p>

   */ ?>

	<h3><?php _e('From Email Address for Notifications',"tdomf"); ?> </h3>

	<p>
	<?php _e('You can set a different email address for notifications here. If you leave this field blank, the default for your blog will be used.',"tdomf"); ?>
    </p>

    <?php $from_email = get_option(TDOMF_OPTION_FROM_EMAIL); ?>

	</p>
	<b><?php _e("From Email Address","tdomf"); ?></b>
	<input type="text" name="tdomf_from_email" id="tdomf_from_email" value="<?php if($from_email) { echo $from_email; } ?>" >
	</p>

    <h3><?php _e('Change author to submitter automatically',"tdomf"); ?> </h3>

	<p>
	<?php _e('If your theme displays the author of a post, you can automatically have it display the submitter info instead, if avaliable. It is recommended to use the "Who Am I" widget to get the full benefit of this option. The default and classic themes in Wordpress do not display the author of a post.',"tdomf"); ?>
    </p>

    <?php $on_author_theme_hack = get_option(TDOMF_OPTION_AUTHOR_THEME_HACK); ?>

	</p>
	<b><?php _e("Use submitter info for author in your theme","tdomf"); ?></b>
	<input type="checkbox" name="tdomf_author_theme_hack" id="tdomf_author_theme_hack"  <?php if($on_author_theme_hack) echo "checked"; ?> >
	</p>

    <h3><?php _e('Add submitter link automatically to post',"tdomf"); ?> </h3>

	<p>
	<?php _e('You can automatically add submitter info to the end of a post. This works on all themes.',"tdomf"); ?>
    </p>

    <?php $on_add_submitter = get_option(TDOMF_OPTION_ADD_SUBMITTER); ?>

	</p>
	<b><?php _e("Add submitter to end of post","tdomf"); ?></b>
	<input type="checkbox" name="tdomf_add_submitter" id="tdomf_add_submitter"  <?php if($on_add_submitter) echo "checked"; ?> >
	</p>


    <br/><br/>

    <table border="0"><tr>

    <td>
    <input type="hidden" name="save_settings" value="0" />
    <input type="submit" name="tdomf_save_button" id="tdomf_save_button" value="<?php _e("Save","tdomf"); ?> &raquo;" />
	</form>
    </td>

    <td>
    <form method="post" action="admin.php?page=tdomf_show_options_menu">
    <input type="submit" name="refresh" value="Refresh" />
    </form>
    </td>

    </tr></table>

   </div>

<?php

}

////////////////////
// Manage options //
////////////////////

// Generate a dummy user
//
function tdomf_create_dummy_user() {
   $rand_username = "tdomf_".tdomf_random_string(5);
   $rand_password = tdomf_random_string(8);
   tdomf_log_message("Attempting to create dummy user $rand_username");
   $user_id = wp_create_user($rand_username,$rand_password);
   $user = new WP_User($user_id);
   if($user->has_cap("publish_posts")) {
      $user->remove_cap("publish_posts");
   }
   if($user->has_cap(TDOMF_CAPABILITY_CAN_SEE_FORM)) {
      $user->remove_cap(TDOMF_CAPABILITY_CAN_SEE_FORM);
   }
   update_option(TDOMF_DEFAULT_AUTHOR,$user_id);
   tdomf_log_message("Dummy user created for default author, user id = $user_id");
   return $user_id;
}

// Taken from http://www.tutorialized.com/view/tutorial/PHP-Random-String-Generator/13903
//
function tdomf_random_string($length)
{
    // Generate random 32 charecter string
    $string = md5(time());

    // Position Limiting
    $highest_startpoint = 32-$length;

    // Take a random starting point in the randomly
    // Generated String, not going any higher then $highest_startpoint
    $tdomf_random_string = substr($string,rand(0,$highest_startpoint),$length);

    return $tdomf_random_string;

}

// Create a page with the form embedded
//
function tdomf_create_form_page() {
   global $current_user;

   $post = array (
	   "post_content"   => "[tdomf_form1]",
	   "post_title"     => __("Submit A Post","tdomf"),
	   "post_author"    => $current_user->ID,
	   "post_status"    => 'publish',
	   "post_type"      => "page"
   );
   $post_ID = wp_insert_post($post);

   return $post_ID;
}

// Handle actions for this form
//
function tdomf_handle_options_actions() {
   global $wpdb, $wp_roles;

   $message = "";

  if(!isset($wp_roles)) {
  	$wp_roles = new WP_Roles();
  }
  $roles = $wp_roles->role_objects;

  if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'create_dummy_user') {
     check_admin_referer('tdomf-create-dummy-user');
     tdomf_create_dummy_user();
     $message = "Dummy user created for Default Author!<br/>";
  } else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'create_form_page') {
     check_admin_referer('tdomf-create-form-page');
     $page_id = tdomf_create_form_page();
     $message = sprintf(__("A page with the form has been created. <a href='%s'>View page &raquo;</a><br/>","tdomf"),get_permalink($page_id));
  } else if(isset($_REQUEST['save_settings'])) {

      check_admin_referer('tdomf-options-save');

     // Who can access the form?

      if(isset($_REQUEST['tdomf_special_access_anyone']) && get_option(TDOMF_OPTION_ALLOW_EVERYONE) == false) {
         add_option(TDOMF_OPTION_ALLOW_EVERYONE,true);
     	foreach($roles as $role) {
     	    // remove cap as it's not needed
		    if(isset($role->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM])){
   				$role->remove_cap(TDOMF_CAPABILITY_CAN_SEE_FORM);
		    }
 	  	}
      } else if(!isset($_REQUEST['tdomf_special_access_anyone'])){
         delete_option(TDOMF_OPTION_ALLOW_EVERYONE);
         // add cap to right roles
         foreach($roles as $role) {
		    if(isset($_REQUEST["tdomf_access_".$role->name])){
				$role->add_cap(TDOMF_CAPABILITY_CAN_SEE_FORM);
		    } else if(isset($role->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM])){
   				$role->remove_cap(TDOMF_CAPABILITY_CAN_SEE_FORM);
		    }
 	  	}
      }

      // Who gets notified?

      $notify_roles = "";
	  foreach($roles as $role) {
		if(isset($_REQUEST["tdomf_notify_".$role->name])){
			$notify_roles .= $role->name.";";
	    }
      }
      if(!empty($notify_roles)) {
        update_option(TDOMF_NOTIFY_ROLES,$notify_roles);
      } else {
        delete_option(TDOMF_NOTIFY_ROLES);
      }

      // Default Category

      $def_cat = $_POST['tdomf_def_cat'];
      update_option(TDOMF_DEFAULT_CATEGORY,$def_cat);

      // Default Author

      $def_aut = $_POST['tdomf_def_user'];
      update_option(TDOMF_DEFAULT_AUTHOR,$def_aut);

      // Author and Submitter fix

      $fix_aut = false;
      if(isset($_POST['tdomf_autocorrect_author'])) { $fix_aut = true; }
      update_option(TDOMF_AUTO_FIX_AUTHOR,$fix_aut);

      //Auto Trust Submitter Count

      $cnt = (int)$_POST['tdomf_trust_count'];
      update_option(TDOMF_OPTION_TRUST_COUNT,$cnt);

      //Turn On/Off Moderation

      $mod = false;
      if(isset($_POST['tdomf_moderation'])) { $mod = true; }
      update_option(TDOMF_OPTION_MODERATION,$mod);

      //Preview

      $preview = false;
      if(isset($_POST['tdomf_preview'])) { $preview = true; }
      update_option(TDOMF_OPTION_PREVIEW,$preview);

      //Ajax

      $ajax = false;
      if(isset($_POST['tdomf_ajax'])) { $ajax = true; }
      update_option(TDOMF_OPTION_AJAX,$ajax);

      //From email

      if(trim($_POST['tdomf_from_email']) == "") {
       	delete_option(TDOMF_OPTION_FROM_EMAIL);
       } else {
        update_option(TDOMF_OPTION_FROM_EMAIL,$_POST['tdomf_from_email']);
       }

      //Author theme hack

      $author_theme_hack = false;
      if(isset($_POST['tdomf_author_theme_hack'])) { $author_theme_hack = true; }
      update_option(TDOMF_OPTION_AUTHOR_THEME_HACK,$author_theme_hack);

      //Add submitter info

      $add_submitter = false;
      if(isset($_POST['tdomf_add_submitter'])) { $add_submitter = true; }
      update_option(TDOMF_OPTION_ADD_SUBMITTER,$add_submitter);

      $message .= "Options Saved!<br/>";
      tdomf_log_message("Options Saved");

   }

   // Warnings

   $message .= tdomf_get_error_messages(false);

   if(!empty($message)) { ?>
   <div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
   <?php }
}

function tdomf_get_error_messages($show_links=true) {
  global $wpdb, $wp_roles;
  if(!isset($wp_roles)) {
  	$wp_roles = new WP_Roles();
  }
  $roles = $wp_roles->role_objects;
  $message = "";
  if(get_option(TDOMF_OPTION_ALLOW_EVERYONE) == false) {
          $test_see_form = false;
          foreach($roles as $role) {
          if(!isset($role->capabilities['publish_posts']) && isset($role->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM])){
            $test_see_form = true;
          }
          }
          if($test_see_form == false) {
            if($show_links) {
              $message .= "<font color=\"red\">".__("<b>Warning</b>: Only users who can <i>already publish posts</i>, can see the form! <a href=\"admin.php?page=tdomf_show_options_menu\">Configure on Options Page &raquo;</a>")."</font><br/>";
            } else {
              $message .= "<font color=\"red\">".__("<b>Warning</b>: Only users who can <i>already publish posts</i>, can see the form!")."</font><br/>";
            }
            tdomf_log_message("Option Allow Everyone not set and no roles set to see the form",TDOMF_LOG_BAD);
          }
        }

       $create_user_link = "admin.php?page=tdomf_show_options_menu&action=create_dummy_user";
	    if(function_exists('wp_nonce_url')){
	          $create_user_link = wp_nonce_url($create_user_link, 'tdomf-create-dummy-user');
    }
	  if(get_option(TDOMF_DEFAULT_AUTHOR) == false) {
	 	  $message .= "<font color=\"red\">".sprintf(__("<b>Error</b>: No default author set! <a href=\"%s\">Create dummy user for default author automatically &raquo;</a>","tdomf"),$create_user_link)."</font><br/>";
	 	  tdomf_log_message("Option Default Author not set!",TDOMF_LOG_BAD);
 	  } else {
 	  	$def_aut = new WP_User(get_option(TDOMF_DEFAULT_AUTHOR));
      if(empty($def_aut->data->ID)) {
        // User does not exist! Deleting option
        delete_option(TDOMF_DEFAULT_AUTHOR);
        $message .= "<font color=\"red\">".sprintf(__("<b>Error</b>: Current Default Author does not exist! <a href=\"%s\">Create dummy user for default author automatically &raquo;</a>","tdomf"),$create_user_link)."</font><br/>";
	 	    tdomf_log_message("Current Default Author does not exist! Deleting option.",TDOMF_LOG_BAD);
      }      
 	  	if($def_aut->has_cap("publish_posts")) {
	 	  $message .= "<font color=\"red\">".sprintf(__("<b>Error1</b>: Default author can publish posts. Default author should not be able to publish posts! <a href=\"%s\">Create a dummy user for default author automatically &raquo;</a>","tdomf"),$create_user_link)."</font><br/>";
	 	  tdomf_log_message("Option Default Author is set to an author who can publish posts.",TDOMF_LOG_BAD);
 	  	}
    }
    return $message;
}


?>
