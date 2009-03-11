<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

function tdomf_show_options_menu() {
    
  tdomf_handle_options_actions();
    
  ?> 
  <div class="wrap">
    
    <h2><?php _e('General Options', 'tdomf') ?></h2>

    <p><?php _e("Global options for this plugin and applies to all forms.","tdomf"); ?></p>
    
    <form method="post" action="admin.php?page=tdomf_show_options_menu">

    <?php if(function_exists('wp_nonce_field')){ wp_nonce_field('tdomf-options-save'); } ?>

  <h3><?php _e("Default Author","tdomf"); ?></h3>

	<p><?php _e("You <b>must</b> pick a default user to be used as the \"author\" of the post. This user cannot be able to publish or edit posts.","tdomf"); ?>
	  <br/><br/>

    <?php // update created users list (in case a user has been deleted)
      $created_users = get_option(TDOMF_OPTION_CREATEDUSERS);
      if($created_users != false) {
        $updated_created_users = array();
        foreach($created_users as $created_user) {
          if(get_userdata($created_user)){
            $updated_created_users[] = $created_user;
          }
        }
        update_option(TDOMF_OPTION_CREATEDUSERS,$updated_created_users);
      } ?>
    
	  <?php $def_aut = get_option(TDOMF_DEFAULT_AUTHOR);
           $def_aut_bad = false; ?>

	 <b><?php _e("Default Author","tdomf"); ?></b>
     <?php if(tdomf_get_all_users_count() < TDOMF_MAX_USERS_TO_DISPLAY) { ?>
    <select id="tdomf_def_user" name="tdomf_def_user">
    <?php $users = tdomf_get_all_users();
          $cnt_users = 0;
          foreach($users as $user) {
            $status = get_usermeta($user->ID,TDOMF_KEY_STATUS);
            $user_obj = new WP_User($user->ID);
            if($user->ID == $def_aut || (!$user_obj->has_cap("publish_posts"))) {
               $cnt_users++;
               ?>
              <option value="<?php echo $user->ID; ?>" <?php if($user->ID == $def_aut) { ?> selected <?php } ?> ><?php if($user_obj->has_cap("publish_posts")) {?><font color="red"><?php }?><?php echo $user->user_login; ?><?php if(!empty($status) && $status == TDOMF_USER_STATUS_BANNED) { ?> (Banned User) <?php } ?><?php if($user_obj->has_cap("publish_posts")) { $def_aut_bad = true; ?> (Error) </font><?php }?></option>
          <?php } } ?>
    </select>
     <?php } else {
         $def_aut_username = "";
         $cnt_users = 0;
         if($def_aut != false) {
             $user_obj = new WP_User($def_aut);
             $cnt_users = 1; // at least
             if($user_obj->has_cap("publish_posts")) { $def_aut_bad; }
             $def_aut_username = $user_obj->user_login;
         }
         ?>
         <input type="text" name="tdomf_def_user" id="tdomf_def_user" size="20" value="<?php echo htmlentities($def_aut_username,ENT_QUOTES,get_bloginfo('charset')); ?>" />
     <?php } ?>

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
	<?php _e('This only counts for submitters who register with your blog and submit using a user account. You can have the user automatically changed to "trusted" after a configurable number of approved submissions. Setting it the value to 0, means that a registered user is automatically trusted. Setting it to -1, disables the feature. A trusted user can still be banned.',"tdomf"); ?> <?php printf(__('You can change a users status (to/from trusted or banned) using the <a href="%s">Manage</a> menu',"tdomf"),"admin.php?page=tdomf_show_manage_menu"); ?>
	</p>

	<p>
	<b><?php _e("Auto Trust Submitter Count","tdomf"); ?></b>
	<input type="text" name="tdomf_trust_count" id="tdomf_trust_count" size="3" value="<?php echo htmlentities(get_option(TDOMF_OPTION_TRUST_COUNT),ENT_QUOTES,get_bloginfo('charset')); ?>" />
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

  <h3><?php _e('Disable Error Messages','tdomf'); ?></h3>
  
  <p>
  <?php _e('You can disable the display of errors to the user when they use this form. This does not stop errors being reported to the log or enable forms to be submitted with "Bad Data"','tdomf'); ?>
  </p>
  
  <?php $disable_errors = get_option(TDOMF_OPTION_DISABLE_ERROR_MESSAGES); ?>

	</p>
	<b><?php _e("Disable error messages being show to user","tdomf"); ?></b>
	<input type="checkbox" name="tdomf_disable_errors" id="tdomf_disable_errors"  <?php if($disable_errors) echo "checked"; ?> >
	</p>
  
  <h3><?php _e('Extra Debug Messages','tdomf'); ?></h3>
  
  <p>
  <?php _e('You can enable extra debugs messages to aid in debugging problems. If you enable "Error Messages" this will also turn on extra PHP error checking.','tdomf'); ?>
  </p>
  
  <?php $extra_log = get_option(TDOMF_OPTION_EXTRA_LOG_MESSAGES); ?>

	</p>
	<b><?php _e("Enable extra log messages ","tdomf"); ?></b>
	<input type="checkbox" name="tdomf_extra_log" id="tdomf_extra_log"  <?php if($extra_log) echo "checked"; ?> >
	</p>
  
  
  <h3><?php _e('"Your Submissions" Page','tdomf'); ?></h3>
  
  <p>
  <?php _e('When a user logs into Wordpress, they can access a "Your Submissions" page which contains a copy of the form. You can disable this page by disabling this option.','tdomf'); ?>
  </p>
  
  <?php $your_submissions = get_option(TDOMF_OPTION_YOUR_SUBMISSIONS); ?>

	</p>
	<b><?php _e("Enable 'Your Submissions' page ","tdomf"); ?></b>
	<input type="checkbox" name="tdomf_your_submissions" id="tdomf_your_submissions"  <?php if($your_submissions) echo "checked"; ?> >
	</p>

  <?php if(tdomf_wp25()) { ?>
  
  <h3><?php _e('Max Widget Control Size',"tdomf"); ?></h3>

	<p>
	<?php _e('You can limit or increase the max size of the control form of a widget in the Form Widget screen. A value of 0 disables this feature.',"tdomf"); ?>
	</p>

	<p>
	<b><?php _e("Max Widget Width","tdomf"); ?></b>
	<input type="text" name="widget_max_width" id="widget_max_width" size="3" value="<?php echo intval(get_option(TDOMF_OPTION_WIDGET_MAX_WIDTH)); ?>" />
	</p>

  <p>
	<b><?php _e("Max Widget Height","tdomf"); ?></b>
	<input type="text" name="widget_max_height" id="widget_max_height" size="3" value="<?php echo intval(get_option(TDOMF_OPTION_WIDGET_MAX_HEIGHT)); ?>" />
	</p>
      
  <?php } ?>
  
  <h3><?php _e('Form Verification Options',"tdomf"); ?></h3>
    
  <?php $tdomf_verify = get_option(TDOMF_OPTION_VERIFICATION_METHOD); ?>
  
  <p>
	<?php _e('You can use these options to set how a submission is verified as coming from a form created by TDOMF. You shouldn\'t need to modify these settings unless you are having a problem with "Bad Data" or invalid session keys',"tdomf"); ?>
	</p>

  <p>
  <input type="radio" name="tdomf_verify" value="default"<?php if($tdomf_verify == "default" || $tdomf_verify == false){ ?> checked <?php } ?>> 
  <?php _e('Use TDO-Mini-Forms internal Method',"tdomf"); ?>
  <br>

  <?php if(function_exists('wp_nonce_field')){ ?>  
  <input type="radio" name="tdomf_verify" value="wordpress_nonce"<?php if($tdomf_verify == "wordpress_nonce"){ ?> checked <?php } ?>>
  <?php _e("Use Wordpress nonce Method","tdomf"); ?>
  <br>
  <?php } ?>
  
  <input type="radio" name="tdomf_verify" value="none"<?php if($tdomf_verify == "none"){ ?> checked <?php } ?>>
  <?php if($tdomf_verify == "none"){ ?><font color="red"><?php } ?>
  <?php _e("Disable Verification (not recommended)","tdomf"); ?>
  <?php if($tdomf_verify == "none"){ ?></font><?php } ?>
  </p>
  
  <h3><?php _e('Form Session Data',"tdomf"); ?></h3>
    
  <?php $tdomf_form_data = get_option(TDOMF_OPTION_FORM_DATA_METHOD); ?>
  
  <p>
	<?php _e('The original and default method for moving data around for a form in use, uses <code>$_SESSION</code>. However this does not work on every platform, specifically if <code>register_globals</code> is enabled. The alternative method, using a database, should work in all cases as long as the user accepts the cookie. You shouldn\'t need to modify these settings unless you are having a problem with "Bad Data" or register_global.',"tdomf"); ?>
	</p>

  <p>
  <input type="radio" name="tdomf_form_data" value="session"<?php if($tdomf_form_data == "session" || $tdomf_form_data == false){ ?> checked <?php } ?><?php if(ini_get('register_globals')) { ?> disabled <?php } ?>> 
  <?php if(ini_get('register_globals')) { ?><del><?php } ?>
  <?php _e('Use <code>$_SESSION</code> to handle from session data (may not work on all host configurations)',"tdomf"); ?>
  <?php if(ini_get('register_globals')) { ?></del><?php } ?>
  <br>

  <input type="radio" name="tdomf_form_data" value="db"<?php if($tdomf_form_data == "db"){ ?> checked <?php } ?>>
  <?php _e("Use database (and cookie) to store session data (should work in all cases)","tdomf"); ?>
  <br>
  
  </p>
    
    <h3 id="spam"><?php _e('Spam Protection',"tdomf"); ?></h3>
    
    <p>
    <?php printf(__('You can now enable spam protection for new submissions. The online service Akismet is used to identify if a submission is spam or not. Submissions marked as spam cab be deleted automatically after a month. You can moderate spam from the <a href="%s">Moderation</a> screen.',"tdomf"),"admin.php?page=tdomf_show_mod_posts_menu&f=3"); ?>
    </p>
    
    <?php $tdomf_spam = get_option(TDOMF_OPTION_SPAM);
          $tdomf_spam_akismet_key = get_option(TDOMF_OPTION_SPAM_AKISMET_KEY);
          if($tdomf_spam_akismet_key == false) {
            $tdomf_spam_akismet_key = get_option('wordpress_api_key');
          }
          $tdomf_spam_notify = get_option(TDOMF_OPTION_SPAM_NOTIFY);
          $tdomf_spam_auto_delete = get_option(TDOMF_OPTION_SPAM_AUTO_DELETE); ?>
          
          <p>
          <b><?php _e("Enable Spam Protection ","tdomf"); ?></b>
	        <input type="checkbox" name="tdomf_spam" id="tdomf_spam"  <?php if($tdomf_spam) echo "checked"; ?> >
          </p>
          
          <p>
          <b><?php _e("Your Akismet Key","tdomf"); ?></b>
	        <input type="text" name="tdomf_spam_akismet_key" id="tdomf_spam_akismet_key" size="8" value="<?php echo $tdomf_spam_akismet_key; ?>" />
          </p>
          
          <p>
          <input type="radio" name="tdomf_spam_notify" value="live"<?php if($tdomf_spam_notify == "live"){ ?> checked <?php } ?>>
          <?php _e("Recieve normal moderation emails for suspected spam submissions","tdomf"); ?>
          <br/>
          
          <input type="radio" name="tdomf_spam_notify" value="none"<?php if($tdomf_spam_notify == "none" || $tdomf_spam_notify == false){ ?> checked <?php } ?>>
          <?php _e("Recieve no notification of spam submissions","tdomf"); ?>
          <br/>
          </p>

          <p>
          <b><?php _e("Automatically Delete Spam older than a month ","tdomf"); ?></b>
	        <input type="checkbox" name="tdomf_spam_auto_delete" id="tdomf_spam_auto_delete"  <?php if($tdomf_spam_auto_delete) echo "checked"; ?> >
          </p>
          
    
    <h3><?php _e('Max Log Size',"tdomf"); ?></h3>

	<p>
	<?php _e('Limit the number of lines in your tdomf log. A value of 0 disables the stored log.',"tdomf"); ?>
	</p>

	<p>
	<b><?php _e("Max Lines in Log","tdomf"); ?></b>
	<input type="text" name="tdomf_log_max_size" id="tdomf_log_max_size" size="4" value="<?php echo htmlentities(get_option(TDOMF_OPTION_LOG_MAX_SIZE),ENT_QUOTES,get_bloginfo('charset')); ?>" />
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

   $users = get_option(TDOMF_OPTION_CREATEDUSERS);
   if($users == false) {
     $users = array( $user_id );
     add_option(TDOMF_OPTION_CREATEDUSERS,$users);
   } else {
     $users = array_merge( $users, array( $user_id ) );
     update_option(TDOMF_OPTION_CREATEDUSERS,$users);
   }
   
   update_option(TDOMF_DEFAULT_AUTHOR,$user_id);
   tdomf_log_message("Dummy user created for default author, user id = $user_id");
   return $user_id;
}

// Create a random string!
// Taken from http://www.tutorialized.com/view/tutorial/PHP-Random-String-Generator/13903
//
function tdomf_random_string($length)
{
    // Error check input
    //
    if($length > 32) { $length = 32; }
    if($length <= 0) { $length = 1; }
  
    // Generate random 32 charecter string
    $string = md5(time());

    // Position Limiting
    $highest_startpoint = 32-$length;

    // Take a random starting point in the randomly
    // Generated String, not going any higher then $highest_startpoint
    $tdomf_random_string = substr($string,rand(0,$highest_startpoint),$length);

    return $tdomf_random_string;

}

// Handle actions for this form
//
function tdomf_handle_options_actions() {
   global $wpdb, $wp_roles;

   $message = "";
   $retValue = false;
   
  if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'create_dummy_user') {
     check_admin_referer('tdomf-create-dummy-user');
     tdomf_create_dummy_user();
     $message = "Dummy user created for Default Author!<br/>";
  } else if(isset($_REQUEST['save_settings']) && !isset($_REQUEST['tdomf_form_id'])) {

      check_admin_referer('tdomf-options-save');

      // Default Author

      $def_aut = $_POST['tdomf_def_user'];
      if(!empty($def_aut) && !is_numeric($def_aut)) {
          if(($userdata = get_userdatabylogin($def_aut)) != false) {
              $def_aut = $userdata->ID;
          } else { 
              $message .= "<font color='red'>".sprintf(__("The user %s is not a valid user and cannot be used for Default Author","tdomf"),$def_aut)."</font><br/>";
              $def_aut = false;              
          }
      }
      update_option(TDOMF_DEFAULT_AUTHOR,$def_aut);

      // Author and Submitter fix

      $fix_aut = false;
      if(isset($_POST['tdomf_autocorrect_author'])) { $fix_aut = true; }
      update_option(TDOMF_AUTO_FIX_AUTHOR,$fix_aut);

      //Auto Trust Submitter Count

      $cnt = -1;
      if(isset($_POST['tdomf_trust_count']) 
       && !empty($_POST['tdomf_trust_count']) 
       && is_numeric($_POST['tdomf_trust_count'])){ 
         $cnt = intval($_POST['tdomf_trust_count']);
      }
      update_option(TDOMF_OPTION_TRUST_COUNT,$cnt);

      //Author theme hack

      $author_theme_hack = false;
      if(isset($_POST['tdomf_author_theme_hack'])) { $author_theme_hack = true; }
      update_option(TDOMF_OPTION_AUTHOR_THEME_HACK,$author_theme_hack);

      //Add submitter info

      $add_submitter = false;
      if(isset($_POST['tdomf_add_submitter'])) { $add_submitter = true; }
      update_option(TDOMF_OPTION_ADD_SUBMITTER,$add_submitter);

      //disable errors
      
      $disable_errors = false;
      if(isset($_POST['tdomf_disable_errors'])) { $disable_errors = true; }
      update_option(TDOMF_OPTION_DISABLE_ERROR_MESSAGES,$disable_errors);
      
      // extra log messages
      
      $extra_log = false;
      if(isset($_POST['tdomf_extra_log'])) { $extra_log = true; }
      update_option(TDOMF_OPTION_EXTRA_LOG_MESSAGES,$extra_log);
      
      // your submissions
      
      $your_submissions = false;
      if(isset($_POST['tdomf_your_submissions'])) { $your_submissions = true; }
      update_option(TDOMF_OPTION_YOUR_SUBMISSIONS,$your_submissions);

      // default widget max sizes
      
      if(tdomf_wp25()) {
        
        $widget_max_width = intval($_POST['widget_max_width']);
        update_option(TDOMF_OPTION_WIDGET_MAX_WIDTH,$widget_max_width);
        
        $widget_max_height = intval($_POST['widget_max_height']);
        update_option(TDOMF_OPTION_WIDGET_MAX_HEIGHT,$widget_max_height);
        
      }
      
      // verification method
      
      $tdomf_verify = $_POST['tdomf_verify'];
      update_option(TDOMF_OPTION_VERIFICATION_METHOD,$tdomf_verify);
      
      $tdomf_form_data = $_POST['tdomf_form_data'];
      update_option(TDOMF_OPTION_FORM_DATA_METHOD,$tdomf_form_data);
      
      // spam options
      
      $tdomf_spam = isset($_POST['tdomf_spam']);
      update_option(TDOMF_OPTION_SPAM,$tdomf_spam);
      
      if($tdomf_spam) {
        $tdomf_spam_akismet_key = $_POST['tdomf_spam_akismet_key'];
        $tdomf_spam_akismet_key_prev = get_option(TDOMF_OPTION_SPAM_AKISMET_KEY);
        if(get_option(TDOMF_OPTION_SPAM_AKISMET_KEY_PREV) == false || $tdomf_spam_akismet_key_prev != $tdomf_spam_akismet_key) {
            if(!empty($tdomf_spam_akismet_key) && tdomf_akismet_key_verify($tdomf_spam_akismet_key)){
               update_option(TDOMF_OPTION_SPAM_AKISMET_KEY,$tdomf_spam_akismet_key);
               update_option(TDOMF_OPTION_SPAM_AKISMET_KEY_PREV,$tdomf_spam_akismet_key_prev);
            } else {
              $message .= "<font color='red'>".sprintf(__("The key: %s has not been recognised by akismet. Spam protection has been disabled.","tdomf"),$tdomf_spam_akismet_key)."</font><br/>";
              update_option(TDOMF_OPTION_SPAM,false);
            }
        }
      }
      
      $tdomf_spam_notify = $_POST['tdomf_spam_notify'];
      update_option(TDOMF_OPTION_SPAM_NOTIFY,$tdomf_spam_notify);
      
      $tdomf_spam_auto_delete = $_POST['tdomf_spam_auto_delete'];
      update_option(TDOMF_OPTION_SPAM_AUTO_DELETE,$tdomf_spam_auto_delete);
      
      $tdomf_log_max_size = intval($_POST['tdomf_log_max_size']);
      update_option(TDOMF_OPTION_LOG_MAX_SIZE,$tdomf_log_max_size);
      
      $message .= "Options Saved!<br/>";
      tdomf_log_message("Options Saved");
      
  } 
  
   // Warnings

   $message .= tdomf_get_error_messages(false);

   if(!empty($message)) { ?>
   <div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
   <?php }
   
   return $retValue;
}

// Check for error messages with options and return a message
//
function tdomf_get_error_messages($show_links=true, $form_id=0) {
  global $wpdb, $wp_roles;
  if(!isset($wp_roles)) {
  	$wp_roles = new WP_Roles();
  }
  $roles = $wp_roles->role_objects;
  $message = "";
  
  #if(ini_get('register_globals') && !TDOMF_HIDE_REGISTER_GLOBAL_ERROR){
  #  $message .= "<font color=\"red\"><strong>".__("ERROR: <em>register_globals</em> is enabled. This is a security risk and also prevents TDO Mini Forms from working.")."</strong></font>";
  #}
  
  if(version_compare("5.0.0",phpversion(),">"))
  {
    $message .= sprintf(__("Warning: You are currently using PHP version %s. It is strongly recommended to use PHP5 with TDO Mini Forms.","tdomf"),phpversion());
    $message .= "<br/>";
  }
  
  if(get_option(TDOMF_OPTION_VERIFICATION_METHOD) == 'none') {
    $message .= __("Warning: Form input verification is disabled. This is a potential security risk.","tdomf");
    $message .= "<br/>";
  }
  
    if(isset($_REQUEST['form']) || $form_id != 0) {
        if($form_id == 0)
        {
            $form_id = intval($_REQUEST['form']);
        }
        
        // permissions error
        
        if(tdomf_get_option_form(TDOMF_OPTION_ALLOW_EVERYONE,$form_id) == false) {
            
            $caps = tdomf_get_option_form(TDOMF_OPTION_ALLOW_CAPS,$form_id);
            if(is_array($caps) && empty($caps)) { $caps = false; } 
            $users = tdomf_get_option_form(TDOMF_OPTION_ALLOW_USERS,$form_id);
            if(is_array($users) && empty($users)) { $users = false; }
            $publish = tdomf_get_option_form(TDOMF_OPTION_ALLOW_PUBLISH,$form_id);
            
            $role_count = 0;
            $role_publish_count = 0;
            foreach($roles as $role) {
                if(isset($role->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id])){
                    $role_count++;
                    if(isset($role->capabilities['publish_posts'])) {
                        $role_publish_count++;
                    }
                }
            }
            
            // if nothing set
            
            if($role_count == 0 && $caps == false && $users == false && $publish == false) {
                if($show_links) {
                    $message .= "<font color=\"red\">".sprintf(__("<b>Warning</b>: No-one has been configured to be able to access the form! <a href=\"%s\">Configure on Options Page &raquo;</a>","tdomf"),get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_form_options_menu&form=$form_id")."</font><br/>";
                } else {
                    $message .= "<font color=\"red\">".__("<b>Warning</b>: No-one has been configured to be able to access the form!", "tdomf")."</font><br/>";
                }
                tdomf_log_message("No-one has been configured to access this form ($form_id)",TDOMF_LOG_BAD);
            } 
            
            // if only publish set

            else if($caps == false && $users == false && $role_count == $role_publish_count && $publish == false ) {
    
                if($show_links) {
                    $message .= "<font color=\"red\">".sprintf(__("<b>Warning</b>: Only users who can <i>already publish posts</i>, can see the form! <a href=\"%s\">Configure on Options Page &raquo;</a>","tdomf"),get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_form_options_menu&form=$form_id")."</font><br/>";
                } else {
                    $message .= "<font color=\"red\">".__("<b>Warning</b>: Only users who can <i>already publish posts</i>, can see this form!", "tdomf")."</font><br/>";
                }
                tdomf_log_message("Only users who can already publish can access the form ($form_id)",TDOMF_LOG_BAD);
            }
        }
   
        // form hacker modified
        
        $mode = "new-post-hack";
        if(tdomf_get_option_form(TDOMF_OPTION_SUBMIT_PAGE,$form_id)) {
            $mode = "new-page-hack";
        }
        $curr_unmod_prev = trim(tdomf_preview_form(array('tdomf_form_id' => $form_id),$mode));
        $org_unmod_prev = trim(tdomf_get_option_form(TDOMF_OPTION_FORM_PREVIEW_HACK_ORIGINAL,$form_id));
        $hacked_prev = trim(tdomf_get_option_form(TDOMF_OPTION_FORM_PREVIEW_HACK,$form_id));
        if($hacked_prev != false && $curr_unmod_prev != $org_unmod_prev) {
            $message .= "<font color=\"red\">";
            $diffs = "admin.php?page=tdomf_show_form_hacker&form=$form_id&mode=$mode&diff&form2=cur&form1=org&type=preview";
            $form_hacker = "admin.php?page=tdomf_show_form_hacker&form=$form_id";
            $dismiss = wp_nonce_url("admin.php?page=tdomf_show_form_hacker&form=$form_id&dismiss&type=preview",'tdomf-form-hacker');
            $message .= sprintf(__("<b>Warning</b>: Form configuration has been changed that affect the preview output but Form Hacker has not been updated! <a href='%s'>Diff &raquo;</a> | <a href='%s'>Hack Form &raquo;</a> | <a href='%s'>Dismiss</a>","tdomf"),$diffs,$form_hacker,$dismiss);
            $message .= "</font><br/>";
        }
        
        $curr_unmod_form = trim(tdomf_generate_form($form_id,$mode));
        $org_unmod_form = trim(tdomf_get_option_form(TDOMF_OPTION_FORM_HACK_ORIGINAL,$form_id));
        $hacked_form = trim(tdomf_get_option_form(TDOMF_OPTION_FORM_HACK,$form_id));
        if($hacked_form != false && $curr_unmod_form != $org_unmod_form) {
            $message .= "<font color=\"red\">";
            $diffs = "admin.php?page=tdomf_show_form_hacker&form=$form_id&mode=$mode&diff&form2=cur&form1=org";
            $form_hacker = "admin.php?page=tdomf_show_form_hacker&form=$form_id";
            $dismiss = wp_nonce_url("admin.php?page=tdomf_show_form_hacker&form=$form_id&dismiss",'tdomf-form-hacker');
            $message .= sprintf(__("<b>Warning</b>: Form configuration has been changed that affect the generated form but Form Hacker has not been updated! <a href='%s'>Diff &raquo;</a> | <a href='%s'>Hack Form &raquo;</a> | <a href='%s'>Dismiss</a>","tdomf"),$diffs,$form_hacker,$dismiss);
            $message .= "</font><br/>";
        }
        
        // widget errors
        
        global $tdomf_form_widgets_admin_errors;
        $mode = "new-post";        
        if(tdomf_get_option_form(TDOMF_OPTION_SUBMIT_PAGE,$form_id)) {
            $mode = "new-page";
        }
        $uri = "admin.php?page=tdomf_show_form_menu&form=".$form_id;
        do_action('tdomf_control_form_start',$form_id,$mode);
        $widget_order = tdomf_get_widget_order($form_id);
        $widgets = tdomf_filter_widgets($mode, $tdomf_form_widgets_admin_errors);
        foreach($widget_order as $w) {
              if(isset($widgets[$w])) {
                  $widget_message = call_user_func($widgets[$w]['cb'],$form_id,$widgets[$w]['params']);
                  if(!empty($widget_message)) {
                      $message .= "<font color=\"red\">" . $widget_message . sprintf(__(" <a href='%s'>Fix &raquo;</a>","tdomf"),$uri)."</font><br/>";
                  }
              }
          }
          
         // @todo check that key is unique in custom fields
    }
        
    if(get_option(TDOMF_OPTION_EXTRA_LOG_MESSAGES) && !get_option(TDOMF_OPTION_DISABLE_ERROR_MESSAGES)) {
         $message .= "<font color=\"red\">";
         if($show_links) {
             $message .= sprintf(__("<b>Warning:</b> You have enabled 'Extra Debug Messages' and disabled 'Disable Error Messages'. This invokes a special mode where all PHP errors are turned on. This can lead to unexpected problems and could be considered a security leak! <a href=\"%s\">Change on the Options Page &raquo;</a>", "tdomf"),get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_options_menu");
         } else {
             $message .= __("<b>Warning:</b> You have enabled 'Extra Debug Messages' and disabled 'Disable Error Messages'. This invokes a special mode where all PHP errors are turned on. This can lead to unexpected problems and could be considered a security leak! This should only be used for debugging purposes.","tdomf");
         }
         $message .= "</font><br/>";
    }
    
       $create_user_link = get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_options_menu&action=create_dummy_user";
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
	 	  $message .= "<font color=\"red\">".sprintf(__("<b>Error</b>: Default author can publish posts. Default author should not be able to publish posts! <a href=\"%s\">Create a dummy user for default author automatically &raquo;</a>","tdomf"),$create_user_link)."</font><br/>";
	 	  tdomf_log_message("Option Default Author is set to an author who can publish posts.",TDOMF_LOG_BAD);
 	  	}
    }
    
    if(function_exists('wp_get_http'))
    {
        $post_uri = TDOMF_URLPATH.'tdomf-form-post.php';
        $headers = wp_get_http($post_uri,false,1);
        if($headers != false && $headers["response"] != '200')
        {
             $message .= "<font color=\"red\">";
             $message .= sprintf(__("<b>Error</b>: Got a %d error when checking <a href=\"%s\">%s</a>! This will prevent posts from being submitted. The permissions may be wrong on the tdo-mini-forms folder.","tdomf"),$headers["response"], $post_uri, $post_uri);
             $message .= "</font><br/>";
             tdomf_log_message("Did not receive a 200 response when checking $post_uri:<pre>".var_export($headers,true)."</pre>",TDOMF_LOG_ERROR);
        }

        $ajax_uri = TDOMF_URLPATH.'tdomf-form-ajax.php';
        $headers = wp_get_http($ajax_uri,false,1);
        if($headers != false && $headers["response"] != '200')
        {
             $message .= "<font color=\"red\">";
             $message .= sprintf(__("<b>Error</b>: Got a %d error when checking <a href=\"%s\">%s</a>! This will prevent forms that use AJAX from submitting posts. The permissions may be wrong on the tdo-mini-forms folder.","tdomf"),$headers["response"], $ajax_uri, $ajax_uri);
             $message .= "</font><br/>";
             tdomf_log_message("Did not receive a 200 response when checking $ajax_uri:<pre>".var_export($headers,true)."</pre>",TDOMF_LOG_ERROR);
        }
        
        $css_uri = TDOMF_URLPATH.'tdomf-style-form.css';
        $headers = wp_get_http($css_uri,false,1);
        if($headers != false && $headers["response"] != '200')
        {
             $message .= "<font color=\"red\">";
             $message .= sprintf(__("<b>Error</b>: Got a %d error when checking <a href=\"%s\">%s</a>! This will make your forms, by default, look very ugly. The permissions may be wrong on the tdo-mini-forms folder.","tdomf"),$headers["response"], $css_uri, $css_uri);
             $message .= "</font><br/>";
             tdomf_log_message("Did not receive a 200 response when checking $css_uri:<pre>".var_export($headers,true)."</pre>",TDOMF_LOG_ERROR);
        }
    }
    
    return $message;
}

?>
