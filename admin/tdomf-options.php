<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

///////////////////////////////
// Code for the options menu //
///////////////////////////////

function tdomf_load_options_admin_scripts() {
    /* for tabs */
    wp_enqueue_script( 'jquery-ui-tabs' );
}
add_action("load-".sanitize_title(__('TDO Mini Forms', 'tdomf'))."_page_tdomf_show_options_menu","tdomf_load_options_admin_scripts");

function tdomf_options_admin_head() {
    /* add style options and start tabs for options page */
    if(preg_match('/tdomf_show_options_menu\&form/',$_SERVER[REQUEST_URI])) { ?>
  
           <style>
            .ui-tabs-nav {
                /*resets*/margin: 0; padding: 0; border: 0; outline: 0; line-height: 1.3; text-decoration: none; font-size: 100%; list-style: none;
                float: left;
                position: relative;
                z-index: 1;
                border-right: 1px solid #d3d3d3;
                bottom: -1px;
            }
            .ui-tabs-nav li {
                /*resets*/margin: 0; padding: 0; border: 0; outline: 0; line-height: 1.3; text-decoration: none; font-size: 100%; list-style: none;
                float: left;
                border: 1px solid #d3d3d3;
                border-right: none;
            }
            .ui-tabs-nav li a {
                /*resets*/margin: 0; padding: 0; border: 0; outline: 0; line-height: 1.3; text-decoration: none; font-size: 100%; list-style: none;
                float: left;
                font-weight: bold;
                text-decoration: none;
                padding: .5em 1.7em;
                color: #555555;
                background: #e6e6e6;
            }
            .ui-tabs-nav li a:hover {
                background: #dadada;
                color: #212121;
            }
            .ui-tabs-nav li.ui-tabs-selected {
                border-bottom-color: #ffffff;
            }
            .ui-tabs-nav li.ui-tabs-selected a, .ui-tabs-nav li.ui-tabs-selected a:hover {
                background: #ffffff;
                color: #222222;
            }
            .ui-tabs-panel {
                /*resets*/margin: 0; padding: 0; border: 0; outline: 0; line-height: 1.3; text-decoration: none; font-size: 100%; list-style: none;
                clear:left;
                border: 1px solid #d3d3d3;
                background: #ffffff;
                color: #222222;
                padding: 1.5em 1.7em;	
            }
            .ui-tabs-hide {
                display: none;
            }
            #access_caps_list {
             overflow: scroll;
             height: 200px;
            }
            
            </style>
           
           <script>
           jQuery(document).ready(function(){
                   jQuery("#options_access_tabs > ul").tabs();
           });
           </script>
           
    <?php }
}
add_action( 'admin_head', 'tdomf_options_admin_head' );

  /**
   * get an array with all capabilities
   * copied from role-manager 2 plugin
   */
function tdomf_get_all_caps() {
    global $wp_roles;
    
    // Get Role List
    foreach($wp_roles->role_objects as $key => $role) {
      foreach($role->capabilities as $cap => $grant) {
        $capnames[$cap] = $cap;
        //$this->debug('grant', ($role->capabilities));
      }
    }
    
    $capnames = apply_filters('capabilities_list', $capnames);
    if(!is_array($capnames)) $capnames = array();
    $capnames = array_unique($capnames);
    sort($capnames);

    //Filter out the level_x caps, they're obsolete
    $capnames = array_diff($capnames, array('level_0', 'level_1', 'level_2', 'level_3', 'level_4', 'level_5',
        'level_6', 'level_7', 'level_8', 'level_9', 'level_10'));
    
    //Filter out roles
      foreach ($wp_roles->get_names() as $role) {
        $key = array_search($role, $capnames);
        if ($key !== false && $key !== null) { //array_search() returns null if not found in 4.1
          unset($capnames[$key]);
        }
      }
      
      // this cap is used seperately 
      unset($capnames['publish_post']);
      
      // filter out tdomf caps that were added
      foreach($capnames as $key => $cap) {
          if(substr($cap,TDOMF_CAPABILITY_CAN_SEE_FORM,strlen(TDOMF_CAPABILITY_CAN_SEE_FORM)) == TDOMF_CAPABILITY_CAN_SEE_FORM) {
              unset($capnames[$key]);
          }
      }
      
    return $capnames;
  }

function tdomf_prep_str_seralize($input) {
    $ko = 4096; /*$ko = 100;*/
    
    $match = array( chr(13).chr(10), "\t", /*"\"",*/ "\n", "\r" );
    /*$replace = array( "\\n", "\\t", "\\\"", "\\n", "\\r" );*/
    $replace = array( '\n', '\t', /*'\"',*/ '\n', '\r' );
    
    if(is_array($input)) {
        $output = array();
        foreach($input as $key => $elem) {
            $output[$key] = tdomf_prep_str_seralize($elem);
        }
        return $output;
    } else if(is_string($input) && strlen($input) > $ko) {
        #echo "strlen = " . strlen($input) . "<br/>";
        $split_data['split'] = true;
        $split_data['ko'] = $ko;
        $split_data['data'] = array();
        #$split_data = array();
        for($i=0;$i<ceil(strlen($input) / $ko);$i++) {
            #echo "<br/><b>$i</b> (".($i * $ko).") and (".(($i+1) * $ko).")<br/>";
            $data = substr($input,($i * $ko),(($i+1) * $ko));
            /*$data = str_replace("\t","\\t",$data);
            $data = str_replace("\"","\\\"",$data);
            $data = str_replace("\n","\\n",$data);
            $data = str_replace("\r","\\r",$data);*/
            $data = str_replace($match,$replace,$data);
            $split_data['data'][] = $data;
            #echo htmlentities($split_data['data'][$i]);
            #$split_data = substr($input,($i * $ko),(($i+1) * $ko));
            #echo "\$output[$i] = " . strlen($output[$i]) . "<br/>";
        }
        return $split_data;
    } else if(is_string($input)){
        /*$output = str_replace(chr(13).chr(10),"\\n",$input);
        $output = str_replace("\t","\\t",$output);
        $output = str_replace("\"","\\\"",$output);
        $output = str_replace("\n","\\n",$output);
        $output = str_replace("\r","\\r",$output);*/
        $output = str_replace($match,$replace,$input);
        #$output = "$output";
        #echo htmlentities($output)."<br/><br/>";
        return $output;
    } else if(is_object($input)) {
        # string is already seralized so this just "corrects" mistakes  
        @$input->widget_value = tdomf_prep_str_seralize($input->widget_value);
    }
    return $input;
}

function tdomf_fix_str_unseralize($input) {
    /*$str_literal = '\n';
    $str_literal = sprintf("%s",$str_literal);
    $str_test = "<pre>test${str_literal}test</pre>";
    echo $str_test;*/

    if(is_array($input)) {
        if(isset($input['split']) && isset($input['ko']) && isset($input['data'])) {
            $data = $input['data'];
            $ko = intval($input['ko']);
            $output = "";
            for($i=0;$i<count($data);$i++) {
                $output .= $data[$i];
            }
            return $output;
        } else {
            $output = array();
            foreach($input as $key => $elem) {
                $output[$key] = tdomf_fix_str_unseralize($elem);
            }
            return $output;
        } 
    /*} else if(is_object($input)) {
        $vars = get_object_vars($input);
        #echo "<pre>".var_export($vars,true)."</pre>";
        if(array_key_exists('widget_value',$vars)) {
            echo "<br/>before: <pre>".var_export($input->widget_value,true)."</pre>";
            $widget_value = tdomf_fix_str_unseralize(maybe_unserialize($input->widget_value));
            echo "<br/>after: <pre>".var_export($widget_value,true)."</pre><br/>";
        }*/
    } else if(is_string($input)) {
         $replace = array( "\n", "\t", "\r" );
         $match = array( '\n', '\t', '\r' );
         $output = str_replace($match,$replace,$input);
         return $output;
    }
    return $input;
}

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
     <li><a href="admin.php?page=tdomf_show_form_menu&form=<?php echo $form_id; ?>"><?php printf(__("Widgets &raquo;","tdomf"),$form_id); ?></a> |</li>
     <li><a href="admin.php?page=tdomf_show_form_hacker&form=<?php echo $form_id; ?>"><?php printf(__("Hack Form &raquo;","tdomf"),$form_id); ?></a></li>
    </ul>
    <?php if(tdomf_wp27()) { ?><br/><br/><?php } ?>
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

    <h3><?php _e('Form Name',"tdomf"); ?></h3>
    
    <p>
    <?php _e('You can give this form a name to make it easier to identify. The name will also be used on the "Your Submissions" page if the form is included. HTML tags will be stripped.','tdomf'); ?>
    </p>
    
     <?php $form_name = tdomf_get_option_form(TDOMF_OPTION_NAME,$form_id); ?>
	<p>
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

	<p><?php _e("You can control access to the form based on user roles, capabilities or by specific users. You can chose \"Unregistered Users\" if you want anyone to be able to access the form, including visitors to your site that do not have user accounts. The old behaviour of TDO Mini Forms allowed any user with the ability to publish posts automatic access to the form. This behaviour can now be turned off or on as required.","tdomf"); ?></p>
   
	<?php if (!isset($wp_roles)) { $wp_roles = new WP_Roles(); }
	       $roles = $wp_roles->role_objects;
          $access_roles = array();
          $publish_roles = array();
          foreach($roles as $role) {
             if(!isset($role->capabilities['publish_posts'])) {
                if($role->name != get_option('default_role')) {
                   array_push($access_roles,$role->name);
                } else {
                   $def_role = $role->name;
                }
             } else {
                 array_push($publish_roles,$role->name);
             }
          }
          rsort($access_roles);
          rsort($publish_roles);
          
          $caps = tdomf_get_all_caps();

          $can_reg = get_option('users_can_register');
          
          ?>


          
          <script type="text/javascript">
         //<![CDATA[
          function tdomf_unreg_user() {
            var flag = document.getElementById("tdomf_special_access_anyone").checked;
            var flag2 = document.getElementById("tdomf_user_publish_override").checked;
            <?php if(isset($def_role)) {?>
            document.getElementById("tdomf_access_<?php echo $def_role; ?>").disabled = flag;
            document.getElementById("tdomf_access_<?php echo $def_role; ?>").checked = flag;
            <?php } ?>
            <?php foreach($access_roles as $role) { ?>
            document.getElementById("tdomf_access_<?php echo $role; ?>").disabled = flag;
            document.getElementById("tdomf_access_<?php echo $role; ?>").checked = flag;
            <?php } ?>
            <?php foreach($caps as $cap) { ?>
            document.getElementById("tdomf_access_caps_<?php echo $cap; ?>").disabled = flag;
            document.getElementById("tdomf_access_caps_<?php echo $cap; ?>").checked = flag;
            <?php } ?>
            document.getElementById("tdomf_access_users_list").disabled = flag;
            if(flag) {
               document.getElementById("tdomf_access_users_list").value = "";
            }
            if(!flag2) {
            <?php foreach($publish_roles as $role) { ?>
            document.getElementById("tdomf_access_<?php echo $role; ?>").disabled = flag;
            document.getElementById("tdomf_access_<?php echo $role; ?>").checked = flag;
            <?php } ?>
            }
           }
           <?php if(isset($def_role) && $can_reg) { ?>
           function tdomf_def_role() {
              var flag = document.getElementById("tdomf_access_<?php echo $def_role; ?>").checked;
              var flag2 = document.getElementById("tdomf_user_publish_override").checked;
              <?php foreach($access_roles as $role) { ?>
               document.getElementById("tdomf_access_<?php echo $role; ?>").checked = flag;
              <?php } ?>
                 if(!flag2) {
                 <?php foreach($publish_roles as $role) { ?>
                    document.getElementById("tdomf_access_<?php echo $role; ?>").checked = flag;
                 <?php } ?>
                 }
              <?php foreach($caps as $cap) { ?>
             document.getElementById("tdomf_access_caps_<?php echo $cap; ?>").disabled = flag;
             document.getElementById("tdomf_access_caps_<?php echo $cap; ?>").checked = flag;
             <?php } ?>
              <?php foreach($access_roles as $role) { ?>
              document.getElementById("tdomf_access_<?php echo $role; ?>").disabled = flag;
              <?php } ?>
              if(!flag2) {
              <?php foreach($publish_roles as $role) { ?>
                 document.getElementById("tdomf_access_<?php echo $role; ?>").disabled = flag;
              <?php } ?>
              }
             document.getElementById("tdomf_access_users_list").disabled = flag;
            if(flag) {
               document.getElementById("tdomf_access_users_list").value = "";
            }
           }
           <?php } ?>
           
           function tdomf_publish_user() {
            var flag = document.getElementById("tdomf_user_publish_override").checked;
            <?php if(isset($def_role) && $can_reg) { ?>
            var flag2 = document.getElementById("tdomf_access_<?php echo $def_role; ?>").checked;
            if(!flag2) {
            <?php } ?>
                <?php foreach($publish_roles as $role) { ?>
                document.getElementById("tdomf_access_<?php echo $role; ?>").checked = flag;
                document.getElementById("tdomf_access_<?php echo $role; ?>").disabled = flag;
                <?php } ?>

            <?php if(isset($def_role) && $can_reg) { ?>
            }
            <?php } ?>
           }
           //-->
           </script>

           <p>
           
          <label for="tdomf_special_access_anyone">
   <input value="tdomf_special_access_anyone" type="checkbox" name="tdomf_special_access_anyone" id="tdomf_special_access_anyone" <?php if(tdomf_get_option_form(TDOMF_OPTION_ALLOW_EVERYONE,$form_id) != false) { ?>checked<?php } ?> onClick="tdomf_unreg_user();" />
   <?php _e("Unregistered Users (i.e. everyone)","tdomf"); ?>
           </label>
           
           <br/>

           <input type="checkbox" name="tdomf_author_edit" id="tdomf_author_edit" disabled />
          <label for="tdomf_author_edit">
          <?php _e("Author of post (registered users only)","tdomf"); ?>
          </label>   
           
          <br/>
          
          <?php $can_publish = tdomf_get_option_form(TDOMF_OPTION_ALLOW_PUBLISH,$form_id); ?>
          
          <input type="checkbox" 
                 name="tdomf_user_publish_override" id="tdomf_user_publish_override"
                 <?php if($can_publish) { ?> checked <?php } ?>
                 onClick="tdomf_publish_user();" />
          <label for="tdomf_user_publish_override">
          <?php _e("Users with rights to publish posts.","tdomf"); ?>
          </label>   

           </p>
          
           <div id="options_access_tabs" class="tabs">
              <ul>
                <li><a href="#access_roles"><span><?php _e('Roles','tdomf'); ?></span></a></li>
                <li><a href="#access_caps"><span><?php _e('Capabilities','tdomf'); ?></span></a></li>
                <li><a href="#access_users"><span><?php _e('Specific Users','tdomf'); ?></span></a></li>
              </ul>
           
           <div id="access_roles">
           <p><?php _e('Select roles that can access the form. If you allow free user registration and pick the default role, this means that a user must just be logged in to access the form.','tdomf'); ?></p>
           
           <p>
          <?php if(isset($def_role)) { ?>
             <label for="tdomf_access_<?php echo ($def_role); ?>">
             <input value="tdomf_access_<?php echo ($def_role); ?>" type="checkbox"
                    name="tdomf_access_<?php echo ($def_role); ?>" id="tdomf_access_<?php echo ($def_role); ?>"  
                    <?php if(isset($wp_roles->role_objects[$def_role]->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id])) { ?> checked <?php } ?> 
                    onClick="tdomf_def_role()" 
                    <?php if(tdomf_get_option_form(TDOMF_OPTION_ALLOW_EVERYONE,$form_id) != false) { ?> checked disabled <?php } ?> />
             <?php if(function_exists('translate_with_context')) {
                   $role_name = translate_with_context($wp_roles->role_names[$def_role]);
                   } else { $role_name = $wp_roles->role_names[$def_role]; } ?>
             <?php echo $role_name." ".__("(newly registered users)"); ?>
             </label><br/>
          <?php } ?>

          <?php foreach($access_roles as $role) { ?>
             <label for="tdomf_access_<?php echo ($role); ?>">
             <input value="tdomf_access_<?php echo ($role); ?>" type="checkbox" 
                    name="tdomf_access_<?php echo ($role); ?>" id="tdomf_access_<?php echo ($role); ?>" 
                    <?php if(isset($wp_roles->role_objects[$role]->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id])) { ?> checked <?php } ?>
                    <?php if(isset($def_role) && isset($wp_roles->role_objects[$def_role]->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id]) && $can_reg) { ?> checked disabled <?php } ?>
                    <?php if(tdomf_get_option_form(TDOMF_OPTION_ALLOW_EVERYONE,$form_id) != false) { ?> checked disabled <?php } ?> />
             <?php if(function_exists('translate_with_context')) {
                   echo translate_with_context($wp_roles->role_names[$role]);
                   } else { echo $wp_roles->role_names[$role]; } ?>
             </label><br/>
          <?php } ?>
          
          <?php foreach($publish_roles as $role) { ?>
             <label for="tdomf_access_<?php echo ($role); ?>">
             <input value="tdomf_access_<?php echo ($role); ?>" type="checkbox" 
                    name="tdomf_access_<?php echo ($role); ?>" id="tdomf_access_<?php echo ($role); ?>"
                    <?php if($can_publish) { ?> checked disabled <?php } ?>
                    <?php if(isset($wp_roles->role_objects[$role]->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id])) { ?> checked <?php } ?>
                    <?php if(isset($def_role) && isset($wp_roles->role_objects[$def_role]->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id]) && $can_reg) { ?> checked disabled <?php } ?>
                    <?php if(tdomf_get_option_form(TDOMF_OPTION_ALLOW_EVERYONE,$form_id) != false) { ?> checked disabled <?php } ?> />
             <?php if(function_exists('translate_with_context')) {
                   printf(__('%s (can publish posts)','tdomf'), translate_with_context($wp_roles->role_names[$role]));
                   } else { printf(__('%s (can publish posts)','tdomf'),$wp_roles->role_names[$role]); } ?>
             </label><br/>
          <?php } ?>
          </p>
          </div> <!-- access_roles -->
           
          <div id="access_caps">
          <p><?php _e('Capabilities are specific access rights. Roles are groupings of capabilities. Individual users can be given individual capabilities outside their assigned Role using external plugins. You can optionally select additional capabilities that give access to the form.','tdomf'); ?></p>
          
          <?php $access_caps = tdomf_get_option_form(TDOMF_OPTION_ALLOW_CAPS,$form_id);
                if($access_caps == false) { $access_caps = array(); } ?>
          
          <div id="access_caps_list"><p>
          <?php foreach($caps as $cap) { ?>
             <input value="tdomf_access_caps_<?php echo ($cap); ?>" type="checkbox" 
                    name="tdomf_access_caps_<?php echo ($cap); ?>" id="tdomf_access_caps_<?php echo ($cap); ?>"
                    <?php if(isset($def_role) && isset($wp_roles->role_objects[$def_role]->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id]) && $can_reg) { ?> checked disabled <?php } ?>
                    <?php if(in_array($cap,$access_caps)) { ?> checked <?php } ?>
                    <?php if(tdomf_get_option_form(TDOMF_OPTION_ALLOW_EVERYONE,$form_id) != false) { ?> checked disabled <?php } ?> />
             <label for="tdomf_access_caps_<?php echo ($cap); ?>">
             <?php if(function_exists('translate_with_context')) {
                   echo translate_with_context($cap);
                   } else { echo $cap; } ?>
             </label><br/>
          <?php } ?>
          </p></div> <!-- access_caps_list -->
          
          </div> <!-- access_caps -->
          
          <div id="access_users">
          
          <?php $allow_users = tdomf_get_option_form(TDOMF_OPTION_ALLOW_USERS,$form_id); 
                $tdomf_access_users_list = "";
                if(is_array($allow_users)) {
                    $tdomf_access_users_list = array();
                    foreach( $allow_users as $allow_user ) {
                        $allow_user = get_userdata($allow_user);
                        $tdomf_access_users_list[] = $allow_user->user_login;
                    }
                    sort($tdomf_access_users_list);
                    $tdomf_access_users_list = join(' ', $tdomf_access_users_list);
                } ?>
          
          <p><?php _e('You can specify additional specific users who can access the form. Just list their login names seperated by spaces in the box provide','tdomf'); ?></p>
          
          <textarea cols="80" rows="3" 
                    name="tdomf_access_users_list" id="tdomf_access_users_list"
                    <?php if(isset($def_role) && isset($wp_roles->role_objects[$def_role]->capabilities[TDOMF_CAPABILITY_CAN_SEE_FORM.'_'.$form_id]) && $can_reg) { ?> checked disabled /></textarea>
                    <?php } else if(tdomf_get_option_form(TDOMF_OPTION_ALLOW_EVERYONE,$form_id) != false) { ?> checked disabled /></textarea> 
                    <?php } else { ?> /><?php echo $tdomf_access_users_list; ?></textarea><?php } ?>
          
          </div> <!-- access_users -->
          
       </div> <!-- options_access_tabs -->
           
        <h3><?php _e("Who gets notified?","tdomf"); ?></h3>

	<p><?php _e("When a form is submitted by someone who can't automatically publish their entry, someone who can approve or publish the posts will be notified by email. You can chose which roles will be notified or set a list of specific email addresses (seperate multiple email addresses with a comma). If you select no role or leave the email field empty, no-one will be notified.","tdomf"); ?>
     <br/><br/>

	 <?php $notify_roles = tdomf_get_option_form(TDOMF_NOTIFY_ROLES,$form_id);
	       if($notify_roles != false) { $notify_roles = explode(';', $notify_roles); }  
           $admin_emails = tdomf_get_option_form(TDOMF_OPTION_ADMIN_EMAILS,$form_id); ?>

	 <?php foreach($roles as $role) {
           if(isset($role->capabilities['edit_others_posts'])
	           && isset($role->capabilities['publish_posts'])) { ?>
		     <input value="tdomf_notify_<?php echo ($role->name); ?>" type="checkbox" name="tdomf_notify_<?php echo ($role->name); ?>" id="tdomf_notify_<?php echo ($role->name); ?>" <?php if($notify_roles != false && in_array($role->name,$notify_roles)) { ?>checked<?php } ?> />
             <label for="tdomf_notify_<?php echo ($role->name); ?>">
          <?php if(function_exists('translate_with_context')) {
                   echo translate_with_context($wp_roles->role_names[$role->name]);
                   } else { echo $wp_roles->role_names[$role->name]; } ?>
          <br/>
		     </label>
		     <?php
		  }
	       } ?>
         <br/>

     <b><?php _e("Specific Emails","tdomf"); ?></b><br/>
	<input type="text" name="tdomf_admin_emails" id="tdomf_admin_emails" size="80" value="<?php if($admin_emails) { echo htmlentities(stripslashes($admin_emails),ENT_QUOTES,get_bloginfo('charset')); } ?>" />
	</p>
         
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

	<p>
	<input type="checkbox" name="tdomf_moderation" id="tdomf_moderation"  	<?php if($on_mod) echo "checked"; ?> >
    <b><?php _e("Enable Moderation","tdomf"); ?></b><br/>

    <?php $redirect = tdomf_get_option_form(TDOMF_OPTION_REDIRECT,$form_id); ?>
    

	<input type="checkbox" name="tdomf_redirect" id="tdomf_redirect" <?php if($redirect) echo "checked"; ?> >
    <?php _e("Redirect to Published Post","tdomf"); ?><br/>
    
    <?php $mod_email_on_pub = tdomf_get_option_form(TDOMF_OPTION_MOD_EMAIL_ON_PUB,$form_id); ?>
    
	<input type="checkbox" name="tdomf_mod_email_on_pub" id="tdomf_mod_email_on_pub" <?php if($mod_email_on_pub) echo "checked"; ?> >
    <?php _e("Send Moderation Email even for automatically Published Post","tdomf"); ?><br/>

    <?php $user_publish_auto = tdomf_get_option_form(TDOMF_OPTION_PUBLISH_NO_MOD,$form_id); ?>

    <input type="checkbox" name="tdomf_user_publish_auto" id="tdomf_user_publish_auto" <?php if($user_publish_auto) { ?> checked <?php } ?> />
    <label for="tdomf_user_publish_auto">
    <?php _e("Users with publish rights will have their posts automatically published","tdomf"); ?>
    </label>
    
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
  
    <h3 id="queue"><?php _e('Queue Published Submissions',"tdomf"); ?></h3>

	<p>
	<?php _e('You can set submissions from this form that are published/approved to be queued before appearing on the site. Just set the period of time between each post and TDOMF will schedule approved submissions from this form. A value of 0 or -1 disables this option.',"tdomf"); ?>
	</p>
    
    <?php $tdomf_queue_period = intval(tdomf_get_option_form(TDOMF_OPTION_QUEUE_PERIOD,$form_id)); ?>

	<p>
	<input type="text" name="tdomf_queue_period" id="tdomf_queue_period" size="5" value="<?php echo htmlentities($tdomf_queue_period,ENT_QUOTES,get_bloginfo('charset')); ?>" />
    <?php _e("Seconds (1 day = 86400 seconds)","tdomf"); ?>
	</p>

    <h3 id="throttle"><?php _e('Throttling Rules',"tdomf"); ?></h3>

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


    <h3 id="ajax"><?php _e('AJAX',"tdomf"); ?> </h3>          
          
	<p>
	<?php _e('You can now enable your form to use AJAX to submit posts. The form handles graceful fallback to the non-ajax version for no-javascript browsers and accessibilty.',"tdomf"); ?>
    </p>

    <?php $ajax = tdomf_get_option_form(TDOMF_OPTION_AJAX,$form_id); ?>
    
	<p>
	<b><?php _e("Use AJAX","tdomf"); ?></b>
	<input type="checkbox" name="tdomf_ajax" id="tdomf_ajax"  <?php if($ajax) echo "checked"; ?> >
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
    
    <div class="wrap">
    
    <h3 id="import"><?php _e('Export and Import Form Configuration',"tdomf"); ?></h3>

     <?php $export_url = get_bloginfo('wpurl')."?tdomf_export=$form_id";
           $export_url = wp_nonce_url($export_url,'tdomf-export-'.$form_id);?>
    
     <p>
        <?php printf(__('To export the configuration of this file, just <a href="%s">save this link</a>. To import, just use the form below to select a previousily exported file and click "Import"',"tdomf"),$export_url); ?>     </p>
     </p>
    
     <form enctype="multipart/form-data" method="post" action="admin.php?page=tdomf_show_options_menu&form=<?php echo $form_id; ?>">
        <label for="import_file"><b><?php _e("Form saved configuration to import: "); ?></b></label>
        <!-- <input type="hidden" name="MAX_FILE_SIZE" value="3000000" /> -->
        <input type="hidden" name='form_id' id='form_id' value='<?php echo $form_id; ?>'>
        <input type='file' name='import_file' id='import_file' size='30' />
        <input type="submit" name="tdomf_import" id="tdomf_import" value="<?php _e("Import","tdomf"); ?>" />
        <?php wp_nonce_field('tdomf-import-'.$form_id); ?>
     </form>
     
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
                <li><a href="admin.php?page=tdomf_show_options_menu&form=<?php echo $form_id->form_id; ?>"<?php if($form_id->form_id == $form_id_in) { ?> class="current" <?php } ?>>
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
   <?php if(tdomf_wp27()) { ?><br/><br/><?php } ?>
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
  $caps = tdomf_get_all_caps();
  
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
  
  } else if(isset($_REQUEST['tdomf_import'])) {
     
     $import_message = tdomf_import_form_from_file();
     if($import_message != false) { $message .= $import_message . '<br/>'; }

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
        
        tdomf_set_option_form(TDOMF_OPTION_ALLOW_CAPS,array(),$form_id);
        
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
        
        // list caps that can access form
        $allow_caps = array();
        foreach($caps as $cap) {
            if(isset($_REQUEST['tdomf_access_caps_'.$cap])){
                $allow_caps[] = $cap; 
            }
        }
        tdomf_set_option_form(TDOMF_OPTION_ALLOW_CAPS,$allow_caps,$form_id);
        
        // convert user names to ids
        $allow_users = array();
        if(isset($_REQUEST['tdomf_access_users_list'])) {
           $user_names = trim($_REQUEST['tdomf_access_users_list']);
           if(!empty($user_names)) {
               $user_names = split(' ',$user_names);
               foreach($user_names as $user_name) {
                   if(!empty($user_name)) {
                       if(($userdata = get_userdatabylogin($user_name)) != false) {
                           $allow_users[] = $userdata->ID;
                       } else {
                           $message .= "<font color='red'>".sprintf(__("$user_name is not a valid user name. Ignoring.<br/>","tdomf"),$form_id)."</font>";
                           tdomf_log_message("User login $user_name is not recognised by wordpress. Ignoring.",TDOMF_LOG_BAD);
                       }
                   }
               }
           }
        }
        tdomf_set_option_form(TDOMF_OPTION_ALLOW_USERS,$allow_users,$form_id);
      }
 
      tdomf_set_option_form(TDOMF_OPTION_ALLOW_PUBLISH,isset($_REQUEST['tdomf_user_publish_override']),$form_id);
      
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
      
      $save = true;
      $tdomf_admin_emails = $_POST['tdomf_admin_emails'];
      $emails = split(',',$tdomf_admin_emails);
      foreach($emails as $email) {
          if(!empty($email)) {
              if(!tdomf_check_email_address($email)) {
                  $message .= "<font color='red'>".sprintf(__("The email %s is not valid! Please update 'Who Gets Notified' with valid email addresses.","tdomf"),$email)."</font><br/>";
                  $save = false;
                  break;
              }
          }
      }
      if($save) { tdomf_set_option_form(TDOMF_OPTION_ADMIN_EMAILS,$tdomf_admin_emails,$form_id); }
      
      // Default Category

      $def_cat = $_POST['tdomf_def_cat'];
      tdomf_set_option_form(TDOMF_DEFAULT_CATEGORY,$def_cat,$form_id);

       //Turn On/Off Moderation

      $mod = false;
      if(isset($_POST['tdomf_moderation'])) { $mod = true; }
      tdomf_set_option_form(TDOMF_OPTION_MODERATION,$mod,$form_id);

      $tdomf_redirect = isset($_POST['tdomf_redirect']);
      tdomf_set_option_form(TDOMF_OPTION_REDIRECT,$tdomf_redirect,$form_id);
      
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
      
      // ajax
      //
      $tdomf_ajax = isset($_POST['tdomf_ajax']);
      tdomf_set_option_form(TDOMF_OPTION_AJAX,$tdomf_ajax,$form_id);
      
      // Send moderation email even for published posts
      //
      $tdomf_mod_email_on_pub = isset($_POST['tdomf_mod_email_on_pub']);
      tdomf_set_option_form(TDOMF_OPTION_MOD_EMAIL_ON_PUB,$tdomf_mod_email_on_pub,$form_id);
      
      // Admin users auto-publish?
      //
      $tdomf_publish_no_mod = isset($_POST['tdomf_user_publish_auto']);
      tdomf_set_option_form(TDOMF_OPTION_PUBLISH_NO_MOD,$tdomf_publish_no_mod,$form_id);
      
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
                    $message .= "<font color=\"red\">".sprintf(__("<b>Warning</b>: No-one has been configured to be able to access the form! <a href=\"%s\">Configure on Options Page &raquo;</a>","tdomf"),get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_options_menu")."</font><br/>";
                } else {
                    $message .= "<font color=\"red\">".__("<b>Warning</b>: No-one has been configured to be able to access the form!", "tdomf")."</font><br/>";
                }
                tdomf_log_message("No-one has been configured to access this form ($form_id)",TDOMF_LOG_BAD);
            } 
            
            // if only publish set

            else if($caps == false && $users == false && $role_count == $role_publish_count && $publish == false ) {
    
                if($show_links) {
                    $message .= "<font color=\"red\">".sprintf(__("<b>Warning</b>: Only users who can <i>already publish posts</i>, can see the form! <a href=\"%s\">Configure on Options Page &raquo;</a>","tdomf"),get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_options_menu")."</font><br/>";
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
