<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

///////////////////////////////
// Code for the options menu //
///////////////////////////////

function tdomf_show_general_options() {
  ?> 
  <div class="wrap">
    
    <h2><?php _e('General Options for TDOMF', 'tdomf') ?></h2>

    <?php if(tdomf_wp25()) { tdomf_options_form_list(); } ?>
    
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
  
  <h3><?php _e('Extra Log Messages','tdomf'); ?></h3>
  
  <p>
  <?php _e('You can enable extra log messages to aid in debugging problems','tdomf'); ?>
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
    
    <a name="spam" /><h3><?php _e('Spam Protection',"tdomf"); ?></h3>
    
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

function tdomf_show_form_options($form_id) {
  if(!tdomf_form_exists($form_id)) { ?>
    <div class="wrap"><font color="red"><?php printf(__("Form id %d does not exist!","tdomf"),$form_id); ?></font></div>
  <?php } else { ?>
    
    <?php $pages = tdomf_get_option_form(TDOMF_OPTION_CREATEDPAGES,$form_id);
          $updated_pages = false;
          if($pages != false) {
            $updated_pages = array();
            foreach($pages as $page_id) {
              if(get_permalink($page_id) != false) {
                $updated_pages[] = $page_id; 
              }
            }
            if(count($updated_pages) == 0) { $updated_pages = false; }
            tdomf_set_option_form(TDOMF_OPTION_CREATEDPAGES,$updated_pages,$form_id);
          } ?>
    
    <?php if(tdomf_wp23()) { ?>
    <div class="wrap">
    <?php if(function_exists('wp_nonce_url')) { ?>
       <a href="<?php echo wp_nonce_url("admin.php?page=tdomf_show_options_menu&delete=$form_id", 'tdomf-delete-form-'.$form_id); ?>">
          <?php _e("Delete","tdomf"); ?></a> |
       <a href="<?php echo wp_nonce_url("admin.php?page=tdomf_show_options_menu&copy=$form_id&form=$form_id", 'tdomf-copy-form-'.$form_id); ?>">
          <?php _e("Copy","tdomf"); ?></a> | 
    <?php } else { ?>
       <a href="admin.php?page=tdomf_show_options_menu&delete=<?php echo $form_id; ?>"><?php _e("Delete","tdomf"); ?></a> |
       <a href="admin.php?page=tdomf_show_options_menu&copy=<?php echo $form_id; ?>"><?php _e("Copy","tdomf"); ?></a> | 
    <?php } ?>
    <?php if($updated_pages != false) { ?>
      <a href="<?php echo get_permalink($updated_pages[0]); ?>" title="<?php _e("Live on your blog!","tdomf"); ?>" ><?php _e("View &raquo;","tdomf"); ?></a> |
    <?php } ?>
    <?php if(tdomf_get_option_form(TDOMF_OPTION_INCLUDED_YOUR_SUBMISSIONS,$form_id) && get_option(TDOMF_OPTION_YOUR_SUBMISSIONS)) { ?>
        <?php if(current_user_can('edit_users')) { ?>
                <a href="users.php?page=tdomf_your_submissions#tdomf_form<?php echo $form_id; ?>" title="<?php _e("Included on the 'Your Submissions' page!",'tdomf'); ?>" >
        <?php } else { ?>
                <a href="profile.php?page=tdomf_your_submissions#tdomf_form<?php echo $form_id; ?>" title="<?php _e("Included on the 'Your Submissions' page!",'tdomf'); ?>" >
          <?php } ?>
      <?php _e("View &raquo;","tdomf"); ?></a>
    <?php } ?>
    </div>
          <?php } ?>
    
    <div class="wrap">
    
    <h2><?php printf(__("Form %d Options","tdomf"),$form_id); ?></h2>
    
    <?php if(tdomf_wp25()) { ?>
    <?php tdomf_options_form_list($form_id); ?>
    <ul class="subsubsub">
    <?php if(function_exists('wp_nonce_url')) { ?>
       <li><a href="<?php echo wp_nonce_url("admin.php?page=tdomf_show_options_menu&delete=$form_id", 'tdomf-delete-form-'.$form_id); ?>">
          <?php _e("Delete","tdomf"); ?></a> |</li>
       <li><a href="<?php echo wp_nonce_url("admin.php?page=tdomf_show_options_menu&copy=$form_id&form=$form_id", 'tdomf-copy-form-'.$form_id); ?>">
          <?php _e("Copy","tdomf"); ?></a> |</li> 
    <?php } else { ?>
       <li><a href="admin.php?page=tdomf_show_options_menu&delete=<?php echo $form_id; ?>"><?php _e("Delete","tdomf"); ?></a> |</li>
       <li><a href="admin.php?page=tdomf_show_options_menu&copy=<?php echo $form_id; ?>"><?php _e("Copy","tdomf"); ?></a> |</li> 
    <?php } ?>
    <?php if($updated_pages != false) { ?>
      <li><a href="<?php echo get_permalink($updated_pages[0]); ?>" title="<?php _e("Live on your blog!","tdomf"); ?>" ><?php _e("View Page &raquo;","tdomf"); ?></a> |</li>
    <?php } ?>
    <?php if(tdomf_get_option_form(TDOMF_OPTION_INCLUDED_YOUR_SUBMISSIONS,$form_id) && get_option(TDOMF_OPTION_YOUR_SUBMISSIONS)) { ?>
      <li><a href="users.php?page=tdomf_your_submissions#tdomf_form<?php echo $form_id; ?>" title="<?php _e("Included on the 'Your Submissions' page!",'tdomf'); ?>" >
      <?php _e("View on 'Your Submissions' &raquo;","tdomf"); ?></a> |</li>
    <?php } ?>
     <li><a href="admin.php?page=tdomf_show_form_menu&form=<?php echo $form_id; ?>"><?php printf(__("Widgets &raquo;","tdomf"),$form_id); ?><a></li>  
    </ul>
          <?php } ?>

          <?php if($updated_pages == false) { ?>
          
             <?php $create_form_link = "admin.php?page=tdomf_show_options_menu&action=create_form_page&form=$form_id";
          if(function_exists('wp_nonce_url')){
          	$create_form_link = wp_nonce_url($create_form_link, 'tdomf-create-form-page');
          } ?>
    <p><a href="<?php echo $create_form_link; ?>"><?php _e("Create a page with this form automatically &raquo;","tdomf"); ?></a></p>
          <?php } ?>
          
    <?php if(tdomf_wp23()) { ?>
          <p><a href="admin.php?page=tdomf_show_form_menu&form=<?php echo $form_id; ?>"><?php printf(__("Widgets for Form %d &raquo;","tdomf"),$form_id); ?></a></p>
    <?php } ?>
    
    <form method="post" action="admin.php?page=tdomf_show_options_menu&form=<?php echo $form_id; ?>">

    <h3><?php _e('Form Name',"tdomf"); ?> </h3>
    
    <p>
    <?php _e('You can give this form a name to make it easier to identify. The name will also be used on the "Your Submissions" page if the form is included. HTML tags will be stripped.','tdomf'); ?>
    </p>
    
     <?php $form_name = tdomf_get_option_form(TDOMF_OPTION_NAME,$form_id); ?>
	</p>
	<b><?php _e("Form Name","tdomf"); ?></b>
	<input type="text" name="tdomf_form_name" id="tdomf_form_name" value="<?php if($form_name) { echo htmlentities(stripslashes($form_name),ENT_QUOTES,get_bloginfo('charset')); } ?>" />
	</p>
  
  <h3><?php _e('Form Description',"tdomf"); ?> </h3>

  <p>
    <?php _e('You can give a description of this form. The description will also be used on the "Your Submissions" page if the form is included. HTML can be used.','tdomf'); ?>
    </p>
  
     <?php $form_descp = tdomf_get_option_form(TDOMF_OPTION_DESCRIPTION,$form_id); ?>
	</p>
  <textarea cols="80" rows="3" name="tdomf_form_descp" id="tdomf_form_descp"><?php if($form_descp) { echo htmlentities(stripslashes($form_descp),ENT_NOQUOTES,get_bloginfo('charset')); } ?></textarea>
	</p>
  
     <h3><?php _e('Include this form in the "Your Submissions" Page',"tdomf"); ?> </h3>

	<p>
	<?php _e('You can optionally include the form in the "Your Submission" page will registered users can access',"tdomf"); ?>
    </p>

    <?php $inc_sub = tdomf_get_option_form(TDOMF_OPTION_INCLUDED_YOUR_SUBMISSIONS,$form_id); ?>

	</p>
	<b><?php _e("Include on 'Your Submissions' page","tdomf"); ?></b>
	<input type="checkbox" name="tdomf_include_sub" id="tdomf_include_sub" <?php if($inc_sub) echo "checked"; ?> >
	</p>
    
     <input type="hidden" id="tdomf_form_id" name="tdomf_form_id" value="<?php echo $form_id; ?>" />
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
   <input value="tdomf_special_access_anyone" type="checkbox" name="tdomf_special_access_anyone" id="tdomf_special_access_anyone" <?php if(tdomf_get_option_form(TDOMF_OPTION_ALLOW_EVERYONE,$form_id) != false) { ?>checked<?php } ?> onClick="tdomf_unreg_user();" />
   <?php _e("Unregistered Users"); ?>
           </label><br/>

   <?php if(isset($def_role)) { ?>
             <label for="tdomf_access_<?php echo ($def_role); ?>">
             <input value="tdomf_access_<?php echo ($def_role); ?>" type="checkbox" name="tdomf_access_<?php echo ($def_role); ?>" id="tdomf_access_<?php echo ($def_role); ?>"  <?php if(isset($wp_roles->role_objects[$def_role]->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id])) { ?> checked <?php } ?> onClick="tdomf_def_role()" <?php if(tdomf_get_option_form(TDOMF_OPTION_ALLOW_EVERYONE,$form_id) != false) { ?> disabled <?php } ?> />
             <?php if(function_exists('translate_with_context')) {
                   $role_name = translate_with_context($wp_roles->role_names[$def_role]);
                   } else { $role_name = $wp_roles->role_names[$def_role]; } ?>
             <?php echo $role_name." ".__("(default role for new users)"); ?>
             </label><br/>
          <?php } ?>

          <?php foreach($access_roles as $role) { ?>
             <label for="tdomf_access_<?php echo ($role); ?>">
             <input value="tdomf_access_<?php echo ($role); ?>" type="checkbox" name="tdomf_access_<?php echo ($role); ?>" id="tdomf_access_<?php echo ($role); ?>" <?php if(isset($wp_roles->role_objects[$role]->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id])) { ?> checked <?php } ?> <?php if(tdomf_get_option_form(TDOMF_OPTION_ALLOW_EVERYONE,$form_id) != false) { ?> disabled <?php } ?> />
             <?php if(function_exists('translate_with_context')) {
                   echo translate_with_context($wp_roles->role_names[$role]);
                   } else { echo $wp_roles->role_names[$role]; } ?>
             </label><br/>
          <?php } ?>
	 </p>

        <h3><?php _e("Who gets notified?","tdomf"); ?></h3>

	<p><?php _e("When a form is submitted by someone who can't automatically publish their entry, someone who can approve or publish the posts will be notified by email. You can chose which roles will be notified. If you select no role, no-one will be notified.","tdomf"); ?>
     <br/><br/>

	 <?php $notify_roles = tdomf_get_option_form(TDOMF_NOTIFY_ROLES,$form_id);
	       if($notify_roles != false) { $notify_roles = explode(';', $notify_roles); }  ?>

	 <?php foreach($roles as $role) {
           if(isset($role->capabilities['edit_others_posts'])
	           && isset($role->capabilities['publish_posts'])) { ?>
		     <label for="tdomf_notify_<?php echo ($role->name); ?>">
		     <input value="tdomf_notify_<?php echo ($role->name); ?>" type="checkbox" name="tdomf_notify_<?php echo ($role->name); ?>" id="tdomf_notify_<?php echo ($role->name); ?>" <?php if($notify_roles != false && in_array($role->name,$notify_roles)) { ?>checked<?php } ?> />
          <?php if(function_exists('translate_with_context')) {
                   echo translate_with_context($wp_roles->role_names[$role->name]);
                   } else { echo $wp_roles->role_names[$role->name]; } ?>
          <br/>
		     </label>
		     <?php
		  }
	       } ?>
         <br/>

	 </p>

	<h3><?php _e("Default Category","tdomf"); ?></h3>

       <p><?php _e("You can select a default category that the entry will be added to by default. You can change always edit the entry before publishing.","tdomf"); ?>
	   <br/><br/>

	         <?php $def_cat = tdomf_get_option_form(TDOMF_DEFAULT_CATEGORY,$form_id); ?>

	   <b><?php _e("Default Category","tdomf"); ?></b>

	   <SELECT NAME="tdomf_def_cat" id="tdomf_def_cat">
	   <?php $cats = get_categories("get=all");
        if(!empty($cats)) {
           foreach($cats as $c) {
             if($c->term_id == $def_cat ) {
               echo "<OPTION VALUE=\"$c->term_id\" selected>$c->category_nicename\n";
             } else {
               echo "<OPTION VALUE=\"$c->term_id\">$c->category_nicename\n";
             }
          }
        }?>
	</select>
	</p>
  
  <h3><?php _e('Turn On/Off Moderation',"tdomf"); ?> </h3>

	<p>
	<?php _e('<b>It is not recommended to turn off moderation.</b> Someone should always approve submissions from anonoymous users otherwise your webpage becomes a source for spammers and bots. However this feature has been requested too many times to not include. I recommend you use the "Auto Trust Submitter Count" instead if you want to enable automatic posting from users. Turning off moderation does not prevent you from banning specific users and IP address or deleting or setting to draft submitted posts.',"tdomf"); ?>
    </p>

    <?php $on_mod = tdomf_get_option_form(TDOMF_OPTION_MODERATION,$form_id); ?>

	</p>
	<b><?php _e("Enable Moderation","tdomf"); ?></b>
	<input type="checkbox" name="tdomf_moderation" id="tdomf_moderation"  	<?php if($on_mod) echo "checked"; ?> >
	</p>

    <h3><?php _e('Preview',"tdomf"); ?> </h3>

	<p>
	<?php _e('If your chosen widgets support preview, you can allow users to preview their post before submission',"tdomf"); ?>
    </p>

    <?php $on_preview = tdomf_get_option_form(TDOMF_OPTION_PREVIEW,$form_id); ?>

	</p>
	<b><?php _e("Enable Preview","tdomf"); ?></b>
	<input type="checkbox" name="tdomf_preview" id="tdomf_preview"  <?php if($on_preview) echo "checked"; ?> >
	</p>

  	<h3><?php _e('From Email Address for Notifications',"tdomf"); ?> </h3>

	<p>
	<?php _e('You can set a different email address for notifications here. If you leave this field blank, the default for your blog will be used.',"tdomf"); ?>
    </p>

    <?php $from_email = tdomf_get_option_form(TDOMF_OPTION_FROM_EMAIL,$form_id); ?>

	</p>
	<b><?php _e("From Email Address","tdomf"); ?></b>
	<input type="text" name="tdomf_from_email" id="tdomf_from_email" value="<?php if($from_email) { echo htmlentities($from_email,ENT_QUOTES,get_bloginfo('charset')); } ?>" >
	</p>
  
  <h3><?php _e('Maximum number of Widget instances',"tdomf"); ?></h3>

	<p>
	<?php _e('You can increase or decrease the number of instances of Widgets that support multiple copies. The minimum is at least 1.','tdomf'); ?>
	</p>

	<p>
	<b><?php _e("Widget Instances","tdomf"); ?></b>
  <?php $widget_count = tdomf_get_option_form(TDOMF_OPTION_WIDGET_INSTANCES,$form_id);
  if($widget_count == false) { $widget_count = 9; } ?>
	<input type="text" name="tdomf_widget_count" id="tdomf_widget_count" size="3" value="<?php echo htmlentities(strval($widget_count),ENT_QUOTES,get_bloginfo('charset')); ?>" />
	</p>
  

  <h3><?php _e('Submit Page instead of Post',"tdomf"); ?> </h3>

	<p>
	<?php _e('You can make this form submit Pages instead of posts. All widgets will technically work, however they their submitted information may not be used by Wordpress.',"tdomf"); ?>
    </p>

    <?php $use_page = tdomf_get_option_form(TDOMF_OPTION_SUBMIT_PAGE,$form_id); ?>

	<p>
	<b><?php _e("Submit Page","tdomf"); ?></b>
	<input type="checkbox" name="tdomf_use_page" id="tdomf_use_page"  <?php if($use_page) echo "checked"; ?> >
	</p>
  
    <a name="queue" />
    
    <h3><?php _e('Queue Published Submissions',"tdomf"); ?></h3>

	<p>
	<?php _e('You can set submissions from this form that are published/approved to be queued before appearing on the site. Just set the period of time between each post and TDOMF will schedule approved submissions from this form. A value of 0 or -1 disables this option.',"tdomf"); ?>
	</p>
    
    <?php $tdomf_queue_period = intval(tdomf_get_option_form(TDOMF_OPTION_QUEUE_PERIOD,$form_id)); ?>

	<p>
	<input type="text" name="tdomf_queue_period" id="tdomf_queue_period" size="5" value="<?php echo htmlentities($tdomf_queue_period,ENT_QUOTES,get_bloginfo('charset')); ?>" />
    <?php _e("Seconds (1 day = 86400 seconds)","tdomf"); ?>
	</p>

    <a name="throttle" />
    
    <h3><?php _e('Throttling Rules',"tdomf"); ?></h3>

	<p>
	<?php _e('You can add rules to throttle input based on registered user accounts and/or IP addresses.',"tdomf"); ?>
	</p>
   
    <?php printf(__("<table border=\"0\">
                     <tr><td>Only %s submissions per</td>
                     <td>%s</td>
                     <td>%s(optionally) per %s Seconds (1 hour = 3600 seconds)</td>
                     <td>%s</td>
                     </tr>
                     </table>","tdomf"),
                     '<input type="text" name="tdomf_throttle_rule_count" id="tdomf_throttle_rule_count" size="3" value="10" /> 
                      <select id="tdomf_throttle_rule_sub_type" name="tdomf_throttle_rule_sub_type" >
                      <option value="unapproved" selected />'.__("unapproved","tdomf").'
                      <option value="any" />'.__("any","tdomf").'
                      </select>',
                     '<input type="radio" name="tdomf_throttle_rule_user_type" id="tdomf_throttle_rule_user_type" value="user" />'.__("registered user","tdomf").'<br/>
                      <input type="radio" name="tdomf_throttle_rule_user_type" id="tdomf_throttle_rule_user_type" value="ip" checked />'.__("IP","tdomf"),
                     '<input type="checkbox" name="tdomf_throttle_rule_opt1" id="tdomf_throttle_rule_opt1" checked >',
                     '<input type="text" name="tdomf_throttle_rule_time" id="tdomf_throttle_rule_time" size="3" value="3600" />',
                     '<input type="submit" name="tdomf_add_throttle_rule" id="tdomf_add_throttle_rule" value="'.__("Add","tdomf").' &raquo;">'); ?>

    <?php $throttle_rules = tdomf_get_option_form(TDOMF_OPTION_THROTTLE_RULES,$form_id); 
          if(is_array($throttle_rules) && !empty($throttle_rules)) { ?>
    
    <p><b><?php _e("Current Throttle Rules","tdomf"); ?></b>
    <ul>
    <?php  foreach($throttle_rules as $id => $throttle_rule) {
             $option_string = "";
             if($throttle_rule['opt1']) {
                 $option_string = sprintf(__("per %s Seconds","tdomf"),$throttle_rule['time']);
             }
        ?>
        <li>
        <?php printf(__("(%d) Only %d %s submissions per %s %s","tdomf"),$id,$throttle_rule['count'],$throttle_rule['sub_type'],$throttle_rule['type'],$option_string); ?>
        <input type="submit" name="tdomf_remove_throttle_rule_<?php echo $id; ?>" id="tdomf_remove_throttle_rule_<?php echo $id; ?>" value="<?php _e("Remove","tdomf"); ?> &raquo;">
        </li>
    <?php } ?>
    </ul>
    </p>
    
          <?php } else { ?>
              <p><b><?php _e("No Throttling Rules currently set.","tdomf"); ?></b></p>
          <?php } ?>
    
     <a name="import" />
          
     <h3><?php _e('Export/Import Form Settings',"tdomf"); ?></h3>
     
     <p>
	<?php _e('The textbox below contains the export of data for this form including widgets. If you wish to import a form, paste its settings here and click Import.',"tdomf"); ?>
	</p>
     
     <?php $form_data['options'] = tdomf_get_options_form($form_id);
           $form_data['options'][TDOMF_OPTION_FORM_NAME] = tdomf_get_option_form(TDOMF_OPTION_FORM_NAME,$form_id);
           $form_data['widgets'] = tdomf_get_widgets_form($form_id); 
           $form_data['caps'] = array();
           if(!isset($wp_roles)) {
              $wp_roles = new WP_Roles();
           }
           $roles = $wp_roles->role_objects;
           foreach($roles as $role) {
              if(isset($role->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id])){
                  $form_data['caps'][] = $role->name;
              }
           }
           $form_export = maybe_serialize($form_data); ?>
     <p>
     <textarea cols="100" rows="10" name="tdomf_import" id="tdomf_import"><?php echo $form_export; ?></textarea>
     <br/>
     <input type="submit" name="tdomf_import_button" id="tdomf_import_button" value="<?php _e("Import","tdomf"); ?> &raquo;">
     </p>
     
          
  <table border="0"><tr>

    <td>
    <input type="hidden" name="save_settings" value="0" />
    <input type="submit" name="tdomf_save_button" id="tdomf_save_button" value="<?php _e("Save","tdomf"); ?> &raquo;" />
	</form>
    </td>

    <td>
    <form method="post" action="admin.php?page=tdomf_show_options_menu&form=<?php echo $form_id; ?>">
    <input type="submit" name="refresh" value="Refresh" />
    </form>
    </td>

    </tr></table>
  
  </div>
  
  <?php }
}

// Show the list of forms
//
function tdomf_options_form_list($form_id_in=false) {
  if(tdomf_wp23()) {
  ?>
  <div class="wrap">
  <?php if($form_id_in != false) { ?>
    <a href="admin.php?page=tdomf_show_options_menu"><?php _e("General Options"); ?></a> |
  <?php } else { ?> 
    <b><?php _e("General Options"); ?></b> | 
  <?php } ?>
    
    <?php $form_ids = tdomf_get_form_ids();
          if(!empty($form_ids)) {
            foreach($form_ids as $form_id) { ?>
              <?php if($form_id->form_id == $form_id_in) { ?>
                <b>
              <?php } else { ?>
                <a href="admin.php?page=tdomf_show_options_menu&form=<?php echo $form_id->form_id; ?>">
              <?php } ?>
              <?php printf(__("Form %d","tdomf"),$form_id->form_id); ?><?php if($form_id->form_id == $form_id_in) { ?></b><?php } else {?></a><?php } ?>
                 |
            <?php }
          }
    ?>
    <?php if(function_exists('wp_nonce_url')) { ?>
   <a href="<?php echo wp_nonce_url("admin.php?page=tdomf_show_options_menu&new", 'tdomf-new-form'); ?>">
          <?php _e("New Form &raquo;","tdomf"); ?></a>
    <?php } else { ?>
      <a href="admin.php?page=tdomf_show_options_menu&new"><?php _e("New Form &raquo;","tdomf"); ?></a>
    <?php } ?>
  </div>
  <?php } else { ?>
    <ul class="subsubsub">
       <li><a href="admin.php?page=tdomf_show_options_menu" <?php if($form_id_in == false) { ?> class="current" <?php } ?>><?php _e("General Options"); ?></a> |</li>
       <?php $form_ids = tdomf_get_form_ids();
          if(!empty($form_ids)) {
            foreach($form_ids as $form_id) { ?>
                <li><a href="admin.php?page=tdomf_show_options_menu&form=<?php echo $form_id->form_id; ?>"<?php if($form_id->form_id == $form_id_in) { ?> class="current" <?php } ?>">
                <?php printf(__("Form %d","tdomf"),$form_id->form_id); ?></a> |</li>
            <?php }
          } ?>
        <?php if(function_exists('wp_nonce_url')) { ?>
        <li><a href="<?php echo wp_nonce_url("admin.php?page=tdomf_show_options_menu&new", 'tdomf-new-form'); ?>">
          <?php _e("New Form &raquo;","tdomf"); ?></a></li>
    <?php } else { ?>
      <li><a href="admin.php?page=tdomf_show_options_menu&new"><?php _e("New Form &raquo;","tdomf"); ?></a></li>
    <?php } ?>
   </ul>
  <?php } 
}

// Display the menu to configure options for this plugin
//
function tdomf_show_options_menu() {
  global $wpdb, $wp_roles;

  $new_form_id = tdomf_handle_options_actions();
  $selected_form_id = intval($_REQUEST['form']);

 if($new_form_id!= false) {
    if(tdomf_wp23()) { tdomf_options_form_list(intval($new_form_id)); }
    tdomf_show_form_options(intval($new_form_id));
  } else if(isset($_REQUEST['form'])) {
    if(tdomf_wp23()) { tdomf_options_form_list($selected_form_id); }
    tdomf_show_form_options($selected_form_id);
  } else {
    if(tdomf_wp23()) { tdomf_options_form_list(); }
    tdomf_show_general_options();
  }
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

// Create a page with the form embedded
//
function tdomf_create_form_page($form_id = 1) {
   global $current_user;

   if(tdomf_form_exists($form_id)){
     
     $form_name = tdomf_get_option_form(TDOMF_OPTION_NAME,$form_id);
     if($form_name == false || empty($form_name)) {
       $form_name = __("Submit A Post","tdomf");
     }
     
     $post = array (
       "post_content"   => "[tdomf_form$form_id]",
       "post_title"     => $form_name,
       "post_author"    => $current_user->ID,
       "post_status"    => 'publish',
       "post_type"      => "page"
     );
     $post_ID = wp_insert_post($post);
  
     $pages = tdomf_get_option_form(TDOMF_OPTION_CREATEDPAGES,$form_id);
     if($pages == false) {
       $pages = array( $post_ID );
     } else {
       $pages = array_merge( $pages, array( $post_ID ) );
     }
     tdomf_set_option_form(TDOMF_OPTION_CREATEDPAGES,$pages,$form_id);
     
     return $post_ID;
   }
   
   return false;
}

// Handle actions for this form
//
function tdomf_handle_options_actions() {
   global $wpdb, $wp_roles;

   $message = "";
   $retValue = false;
   
  if(!isset($wp_roles)) {
  	$wp_roles = new WP_Roles();
  }
  $roles = $wp_roles->role_objects;
  
  $remove_throttle_rule = false;
  $rule_id = 0;
  if(isset($_REQUEST['tdomf_form_id'])) {
      $form_id = intval($_REQUEST['tdomf_form_id']);
      $rules = tdomf_get_option_form(TDOMF_OPTION_THROTTLE_RULES,$form_id);
      if(is_array($rules)) {
          foreach($rules as $id => $r) {
              if(isset($_REQUEST["tdomf_remove_throttle_rule_$id"])) {
                  $remove_throttle_rule = true;
                  $rule_id = $id;
                  break;
              }
          }
      }
  }
  
  if($remove_throttle_rule) {
      check_admin_referer('tdomf-options-save');
      
      unset($rules[$rule_id]);
      tdomf_set_option_form(TDOMF_OPTION_THROTTLE_RULES,$rules,$form_id);
      
      $message .= "Throttle rule removed!<br/>";
      tdomf_log_message("Removed throttle rule");
      
  } else if(isset($_REQUEST['tdomf_add_throttle_rule'])) {
     
     check_admin_referer('tdomf-options-save');

     $form_id = intval($_REQUEST['tdomf_form_id']);
     
     $rule = array();
     $rule['sub_type'] = $_REQUEST['tdomf_throttle_rule_sub_type'];
     $rule['count'] = $_REQUEST['tdomf_throttle_rule_count'];
     $rule['type'] = $_REQUEST['tdomf_throttle_rule_user_type'];
     $rule['opt1'] = isset($_REQUEST['tdomf_throttle_rule_opt1']);
     $rule['time'] = intval($_REQUEST['tdomf_throttle_rule_time']);
                            
     $rules = tdomf_get_option_form(TDOMF_OPTION_THROTTLE_RULES,$form_id);
     if(!is_array($rules)) { $rules = array(); }
     $rules[] = $rule;
     tdomf_set_option_form(TDOMF_OPTION_THROTTLE_RULES,$rules,$form_id);
     
     $message .= "Throttle rule added!<br/>";
     tdomf_log_message("Added a new throttle rule: " . var_export($rule,true));
  
  } else if(isset($_REQUEST['tdomf_import_button'])) {
     
     check_admin_referer('tdomf-options-save');

     $form_id = intval($_REQUEST['tdomf_form_id']);
     
     $form_import = $_REQUEST['tdomf_import'];
     if(get_magic_quotes_gpc()) {
         $form_import = stripslashes($form_import);
     }
     
     $form_data = maybe_unserialize($form_import);
     
     if(is_array($form_data)) {
         tdomf_import_form($form_id,$form_data['options'],$form_data['widgets'],$form_data['caps']);
         tdomf_log_message("Form import succeeded",TDOMF_LOG_GOOD);
         $message = __("Form import successful<br/>","tdomf");
     } else {
         tdomf_log_message("Form import failed " . var_export($form_data,true),TDOMF_LOG_ERROR);
         $message = __("Form import failed<br/>","tdomf");
     }

  } else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'create_dummy_user') {
     check_admin_referer('tdomf-create-dummy-user');
     tdomf_create_dummy_user();
     $message = "Dummy user created for Default Author!<br/>";
  } else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'create_form_page') {
     check_admin_referer('tdomf-create-form-page');
     $form_id = intval($_REQUEST['form']);
     $page_id = tdomf_create_form_page($form_id);
     $message = sprintf(__("A page with the form has been created. <a href='%s'>View page &raquo;</a><br/>","tdomf"),get_permalink($page_id));
  } else if(isset($_REQUEST['save_settings']) && !isset($_REQUEST['tdomf_form_id'])) {

      check_admin_referer('tdomf-options-save');

      // Default Author

      $def_aut = $_POST['tdomf_def_user'];
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
      
      $message .= "Options Saved!<br/>";
      tdomf_log_message("Options Saved");
      
  } else if(isset($_REQUEST['save_settings']) && isset($_REQUEST['tdomf_form_id'])) {
    
      check_admin_referer('tdomf-options-save');
    
      $form_id = intval($_REQUEST['tdomf_form_id']);
     
      // Who can access the form?

      if(isset($_REQUEST['tdomf_special_access_anyone']) && tdomf_get_option_form(TDOMF_OPTION_ALLOW_EVERYONE,$form_id) == false) {
         tdomf_set_option_form(TDOMF_OPTION_ALLOW_EVERYONE,true,$form_id);
     	foreach($roles as $role) {
     	    // remove cap as it's not needed
		    if(isset($role->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id])){
   				$role->remove_cap(TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id);
		    }
 	  	}
      } else if(!isset($_REQUEST['tdomf_special_access_anyone'])){
         tdomf_set_option_form(TDOMF_OPTION_ALLOW_EVERYONE,false,$form_id);
         // add cap to right roles
         foreach($roles as $role) {
		    if(isset($_REQUEST["tdomf_access_".$role->name])){
				$role->add_cap(TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id);
		    } else if(isset($role->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id])){
   				$role->remove_cap(TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id);
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
        tdomf_set_option_form(TDOMF_NOTIFY_ROLES,$notify_roles,$form_id);
      } else {
        tdomf_set_option_form(TDOMF_NOTIFY_ROLES,false,$form_id);
      }

      // Default Category

      $def_cat = $_POST['tdomf_def_cat'];
      tdomf_set_option_form(TDOMF_DEFAULT_CATEGORY,$def_cat,$form_id);

       //Turn On/Off Moderation

      $mod = false;
      if(isset($_POST['tdomf_moderation'])) { $mod = true; }
      tdomf_set_option_form(TDOMF_OPTION_MODERATION,$mod,$form_id);

      //Preview

      $preview = false;
      if(isset($_POST['tdomf_preview'])) { $preview = true; }
      tdomf_set_option_form(TDOMF_OPTION_PREVIEW,$preview,$form_id);

            //From email

      if(trim($_POST['tdomf_from_email']) == "") {
       	tdomf_set_option_form(TDOMF_OPTION_FROM_EMAIL,false,$form_id);
       } else {
        tdomf_set_option_form(TDOMF_OPTION_FROM_EMAIL,$_POST['tdomf_from_email'],$form_id);
       }

       // Form name
       
       if(trim($_POST['tdomf_form_name']) == "") {
        tdomf_set_option_form(TDOMF_OPTION_NAME,"",$form_id);
       } else {
        tdomf_set_option_form(TDOMF_OPTION_NAME,strip_tags($_POST['tdomf_form_name']),$form_id);
       }
       
       // Form description
       
       if(trim($_POST['tdomf_form_descp']) == "") {
       	tdomf_set_option_form(TDOMF_OPTION_DESCRIPTION,false,$form_id);
       } else {
        tdomf_set_option_form(TDOMF_OPTION_DESCRIPTION,$_POST['tdomf_form_descp'],$form_id);
       }
       
       // Include on "your submissions" page
       //
       $include = false;
      if(isset($_POST['tdomf_include_sub'])) { $include = true; }
      tdomf_set_option_form(TDOMF_OPTION_INCLUDED_YOUR_SUBMISSIONS,$include,$form_id);
       
      if(get_option(TDOMF_OPTION_YOUR_SUBMISSIONS) && $include) {
        $message .= sprintf(__("Saved Options for Form %d. <a href='%s'>See your form &raquo</a>","tdomf"),$form_id,"users.php?page=tdomf_your_submissions#tdomf_form%d")."<br/>";
      } else {
        $message .= sprintf(__("Saved Options for Form %d.","tdomf"),$form_id)."<br/>";
      }
      
      // widget count
      //
      $widget_count = 10;
      if(isset($_POST['tdomf_widget_count'])) { $widget_count = intval($_POST['tdomf_widget_count']); }
      if($widget_count < 1){ $widget_count = 1; }
      tdomf_set_option_form(TDOMF_OPTION_WIDGET_INSTANCES,$widget_count,$form_id);
      
      //Submit page instead of post
      //
      $use_page = false;
      if(isset($_POST['tdomf_use_page'])) { $use_page = true; }
      tdomf_set_option_form(TDOMF_OPTION_SUBMIT_PAGE,$use_page,$form_id);

      // Queue period
      //
      $tdomf_queue_period = intval($_POST['tdomf_queue_period']);
      tdomf_set_option_form(TDOMF_OPTION_QUEUE_PERIOD,$tdomf_queue_period,$form_id);
      
      tdomf_log_message("Options Saved for Form ID $form_id");
       
  } else if(isset($_REQUEST['delete'])) {
      
    $form_id = intval($_REQUEST['delete']);
    
    check_admin_referer('tdomf-delete-form-'.$form_id);
    
    if(tdomf_form_exists($form_id)) {
      $count_forms = count(tdomf_get_form_ids());
      if($count_forms > 1) {
        if(tdomf_delete_form($form_id)) {
           $message .= sprintf(__("Form %d deleted.<br/>","tdomf"),$form_id);
        } else {
          $message .= sprintf(__("Could not delete Form %d!<br/>","tdomf"),$form_id);
        }
      } else {
        $message .= sprintf(__("You cannot delete the last form! There must be at least one form in the system.<br/>","tdomf"),$form_id);
      }
    } else {
      $message .= sprintf(__("Form %d is not valid!<br/>","tdomf"),$form_id);
    }
  } else if(isset($_REQUEST['copy'])) {
    
    $form_id = intval($_REQUEST['copy']);
    
    check_admin_referer('tdomf-copy-form-'.$form_id);
    
    $copy_form_id = tdomf_copy_form($form_id);
   
    if($copy_form_id != 0) {
      $message .= sprintf(__("Form %d copied with id %d.<br/>","tdomf"),$form_id,$copy_form_id);
      $retValue = $copy_form_id;
    } else {
      $message .= sprintf(__("Failed to copy Form %d!<br/>","tdomf"),$form_id);
    }
        
  } else if(isset($_REQUEST['new'])) {
    
    check_admin_referer('tdomf-new-form');
    
    $form_id = tdomf_create_form(__('New Form','tdomf'),array());
   
    if($form_id != 0) {
      $message .= sprintf(__("New form created with %d.<br/>","tdomf"),$form_id);
      $retValue = $form_id;
    } else {
      $message .= __("Failed to create new Form!<br/>","tdomf");
    }
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
function tdomf_get_error_messages($show_links=true) {
  global $wpdb, $wp_roles;
  if(!isset($wp_roles)) {
  	$wp_roles = new WP_Roles();
  }
  $roles = $wp_roles->role_objects;
  $message = "";
  
  #if(ini_get('register_globals') && !TDOMF_HIDE_REGISTER_GLOBAL_ERROR){
  #  $message .= "<font color=\"red\"><strong>".__("ERROR: <em>register_globals</em> is enabled. This is a security risk and also prevents TDO Mini Forms from working.")."</strong></font>";
  #}
  
  if(get_option(TDOMF_OPTION_VERIFICATION_METHOD) == 'none') {
    $message .= __("Warning: Form input verification is disabled. This is a potential security risk.");
  }
  
  if(isset($_REQUEST['form'])) {
  
    $form_id = intval($_REQUEST['form']);
    
  if(tdomf_get_option_form(TDOMF_OPTION_ALLOW_EVERYONE,$form_id) == false) {
          $test_see_form = false;
          foreach($roles as $role) {
          if(!isset($role->capabilities['publish_posts']) && isset($role->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id])){
            $test_see_form = true;
          }
          }
          if($test_see_form == false) {
            if($show_links) {
              $message .= "<font color=\"red\">".sprintf(__("<b>Warning</b>: Only users who can <i>already publish posts</i>, can see the form! <a href=\"%s\">Configure on Options Page &raquo;</a>"),get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_options_menu")."</font><br/>";
            } else {
              $message .= "<font color=\"red\">".__("<b>Warning</b>: Only users who can <i>already publish posts</i>, can seet this form!")."</font><br/>";
            }
            tdomf_log_message("Option Allow Everyone not set and no roles set to see the form",TDOMF_LOG_BAD);
          }
        }
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
    return $message;
}

?>
