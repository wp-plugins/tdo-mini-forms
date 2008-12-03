<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

function tdomf_form_hacker_diff($form_id) {
  $mode = $_REQUEST['mode'];
  $form1_type = $_REQUEST['form1'];
  $form2_type = $_REQUEST['form2'];
  $render = 'default';
  if(isset($_REQUEST['render'])) {
      $render = $_REQUEST['render'];
  }
  $type = $_REQUEST['type'];
  
  // @TODO add preview
  
  $form1_name = "";
  $form2_name = "";

  if($type == 'preview') {
      if($form1_type == 'cur') {
        $form1_name = __('Current Unmodified Preview','tdomf');
        $form1 = trim(tdomf_preview_form(array('tdomf_form_id' => $form_id),$mode));
      } else if($form1_type == 'org') {
        $form1_name = __('Original Unmodified Preview','tdomf');
        $form1 = trim(tdomf_get_option_form(TDOMF_OPTION_FORM_PREVIEW_HACK_ORIGINAL,$form_id));
      } else if($form1_type == 'hack') {
        $form1_name = __('Hacked Preview','tdomf');
        $form1 = trim(tdomf_get_option_form(TDOMF_OPTION_FORM_PREVIEW_HACK,$form_id));
      }
      
      if($form2_type == 'cur') {
        $form2_name = __('Current Unmodified Preview','tdomf');
        $form2 = trim(tdomf_preview_form(array('tdomf_form_id' => $form_id),$mode));
      } else if($form2_type == 'org') {
        $form2_name = __('Original Unmodified Preview','tdomf');
        $form2 = trim(tdomf_get_option_form(TDOMF_OPTION_FORM_PREVIEW_HACK_ORIGINAL,$form_id));
      } else if($form2_type == 'hack') {
        $form2_name = __('Hacked Preview','tdomf');
        $form2 = trim(tdomf_get_option_form(TDOMF_OPTION_FORM_PREVIEW_HACK,$form_id));
      }
  } else {
      if($form1_type == 'cur') {
        $form1_name = __('Current Unmodified Form','tdomf');
        $form1 = trim(tdomf_generate_form(array('tdomf_form_id' => $form_id),$mode));
      } else if($form1_type == 'org') {
        $form1_name = __('Original Unmodified Form','tdomf');
        $form1 = trim(tdomf_get_option_form(TDOMF_OPTION_FORM_HACK_ORIGINAL,$form_id));
      } else if($form1_type == 'hack') {
        $form1_name = __('Hacked Form','tdomf');
        $form1 = trim(tdomf_get_option_form(TDOMF_OPTION_FORM_HACK,$form_id));
      }
      
      if($form2_type == 'cur') {
        $form2_name = __('Current Unmodified Form','tdomf');
        $form2 = trim(tdomf_generate_form($form_id,$mode));
      } else if($form2_type == 'org') {
        $form2_name = __('Original Unmodified Form','tdomf');
        $form2 = trim(tdomf_get_option_form(TDOMF_OPTION_FORM_HACK_ORIGINAL,$form_id));
      } else if($form2_type == 'hack') {
        $form2_name = __('Hacked Form','tdomf');
        $form2 = trim(tdomf_get_option_form(TDOMF_OPTION_FORM_HACK,$form_id));
      }
  }
  
  echo "<h3>".sprintf(__('%s versus %s','tdomf'),$form1_name,$form2_name)."</h3>";

  if($form1 == $form2) {
      echo "<p>".sprintf(__('%s is the same as %s!','tdomf'),$form1_name,$form2_name)."</p>";
      return;
  }
  
  
  set_include_path(get_include_path() . PATH_SEPARATOR . ABSPATH.PLUGINDIR.DIRECTORY_SEPARATOR.TDOMF_FOLDER.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'include' );
  include_once "Text/Diff.php";
    
  $form1 = explode("\n",$form1);
  $form2 = explode("\n",$form2);
  
  $diff = &new Text_Diff('auto',array($form1, $form2));
  
  if($diff->isEmpty()) {
      echo "<p>".sprintf(__('%s is the same as %s!','tdomf'),$form1_name,$form2_name)."</p>";
      return;
  }
  
  echo "<form>";
  
  echo "<input type='hidden' id='page' name='page' value='tdomf_show_form_hacker' />";
  echo "<input type='hidden' id='form' name='form' value='$form_id' />";
  echo "<input type='hidden' id='mode' name='mode' value='$mode' />";
  echo "<input type='hidden' id='diff' name='diff' />";
  echo "<input type='hidden' id='form2' name='form2' value='$form2_type' />";
  echo "<input type='hidden' id='form1' name='form1' value='$form1_type' />";
  echo "<input type='hidden' id='type' name='type' value='$type' />";  
  
  echo '<label for="render">'.__('Render Type','tdomf').' </label>';
  echo '<select id="render" name="render">';
  echo '<option value="default" ';
  if($render == 'default') { echo 'selected'; }
  echo ' >'.__('Default','tdomf')."\n<br/>";
  
  echo '<option value="unified" ';
  if($render == 'unified') { echo 'selected'; }
  echo ' >'.__('Unified','tdomf')."\n<br/>";
  
  echo '<option value="inline" ';
  if($render == 'inline') { echo 'selected'; }
  echo ' >'.__('Inline','tdomf')."\n<br/>";
  
  echo '<option value="context" ';
  if($render == 'context') { echo 'selected'; }
  echo ' >'.__('Context','tdomf')."\n<br/>";
  echo '</select>';
  echo '<input type="submit" value="'.__('Go','tdomf').'" /></form>';
  
  if($render == 'unified') {
     include_once "Text/Diff/Renderer/unified.php";
     $renderer = &new Text_Diff_Renderer_unified();
     echo "<pre>".htmlentities($renderer->render($diff),ENT_NOQUOTES,get_bloginfo('charset'))."</pre>";
  } else if($render == 'inline') {
     include_once "Text/Diff/Renderer/inline.php";
     $renderer = &new Text_Diff_Renderer_inline();
     echo "<pre>".$renderer->render($diff)."</pre>";
  } else if($render == 'context') {
     include_once "Text/Diff/Renderer/context.php";
     $renderer = &new Text_Diff_Renderer_context();
     echo "<pre>".htmlentities($renderer->render($diff),ENT_NOQUOTES,get_bloginfo('charset'))."</pre>";
  } else {
     include_once "Text/Diff/Renderer.php";
     $renderer = &new Text_Diff_Renderer();
     echo "<pre>".htmlentities($renderer->render($diff),ENT_NOQUOTES,get_bloginfo('charset'))."</pre>";
  }
}

function tdomf_form_hacker_actions($form_id) {

  if(tdomf_form_exists($form_id)) {
    if(tdomf_get_option_form(TDOMF_OPTION_SUBMIT_PAGE,$form_id)) {
       $mode = "new-page-hack";
    } else {
       $mode = "new-post-hack";
    }
    #@session_start();
    $message = "";
    if(isset($_REQUEST['tdomf_form_hack_save'])) {
       check_admin_referer('tdomf-form-hacker');
       if(isset($_REQUEST['tdomf_form_hack'])) {
          $form_new = $_REQUEST['tdomf_form_hack'];
          $preview_new = $_REQUEST['tdomf_form_preview_hack'];
          
          #if (get_magic_quotes_gpc()) {
             $form_new = stripslashes($form_new);
             $preview_new = stripslashes($preview_new);
          #}
          if(strpos($form_new,TDOMF_MACRO_FORMKEY) !== false) {
            $form_cur = trim(tdomf_generate_form($form_id,$mode));
            $preview_cur = trim(tdomf_preview_form(array('tdomf_form_id' => $form_id),$mode));
            tdomf_set_option_form(TDOMF_OPTION_FORM_HACK,trim($form_new),$form_id);
            tdomf_set_option_form(TDOMF_OPTION_FORM_PREVIEW_HACK,trim($preview_new),$form_id);
            tdomf_set_option_form(TDOMF_OPTION_FORM_HACK_ORIGINAL,$form_cur,$form_id);
            tdomf_set_option_form(TDOMF_OPTION_FORM_PREVIEW_HACK_ORIGINAL,$preview_cur,$form_id);
          } else {
            $message = sprintf(__("No <code>%s</code> is included in one of your forms! Hacked form not saved.","tdomf"),TDOMF_MACRO_FORMKEY);
          }
       }
       if(empty($message)) {
         $message = __("Hacked Form Saved.","tdomf");
       }
     } else if(isset($_REQUEST['tdomf_form_hack_reset'])){
       check_admin_referer('tdomf-form-hacker');
       tdomf_set_option_form(TDOMF_OPTION_FORM_HACK_ORIGINAL,false,$form_id);
       tdomf_set_option_form(TDOMF_OPTION_FORM_PREVIEW_HACK_ORIGINAL,false,$form_id);
       tdomf_set_option_form(TDOMF_OPTION_FORM_HACK,false,$form_id);
       tdomf_set_option_form(TDOMF_OPTION_FORM_PREVIEW_HACK,false,$form_id);
       $message = __("Reset Hacked Forms.","tdomf");
     } else if(isset($_REQUEST['tdomf_hack_messages_save'])) {
         check_admin_referer('tdomf-form-hacker');
         
         if(!function_exists('tdomf_set_form_message')) {
             function tdomf_set_form_message($form_id,$name,$opt) {
                 if(isset($_REQUEST[$name])) {
                     $msg = $_REQUEST[$name];
                     #if (get_magic_quotes_gpc()) {
                         $msg = stripslashes($_REQUEST[$name]);
                     #}
                 }
                 tdomf_set_option_form($opt,$msg,$form_id);
             }
         }
         
         tdomf_set_form_message($form_id, 'tdomf_msg_sub_publish', TDOMF_OPTION_MSG_SUB_PUBLISH); 
         tdomf_set_form_message($form_id, 'tdomf_msg_sub_future', TDOMF_OPTION_MSG_SUB_FUTURE); 
         tdomf_set_form_message($form_id, 'tdomf_msg_sub_spam', TDOMF_OPTION_MSG_SUB_SPAM);
         tdomf_set_form_message($form_id, 'tdomf_msg_sub_mod', TDOMF_OPTION_MSG_SUB_MOD);
         tdomf_set_form_message($form_id, 'tdomf_msg_sub_error', TDOMF_OPTION_MSG_SUB_ERROR);
         tdomf_set_form_message($form_id, 'tdomf_msg_perm_banned_user', TDOMF_OPTION_MSG_PERM_BANNED_USER);
         tdomf_set_form_message($form_id, 'tdomf_msg_perm_banned_ip', TDOMF_OPTION_MSG_PERM_BANNED_IP);
         tdomf_set_form_message($form_id, 'tdomf_msg_perm_throttle', TDOMF_OPTION_MSG_PERM_THROTTLE);
         tdomf_set_form_message($form_id, 'tdomf_msg_perm_invalid_user', TDOMF_OPTION_MSG_PERM_INVALID_USER);
         tdomf_set_form_message($form_id, 'tdomf_msg_perm_invalid_nouser', TDOMF_OPTION_MSG_PERM_INVALID_NOUSER);
         $message = __("Messages Updated.","tdomf");
     } else if(isset($_REQUEST['tdomf_hack_messages_reset'])) {
         check_admin_referer('tdomf-form-hacker');
         tdomf_set_option_form(TDOMF_OPTION_MSG_SUB_PUBLISH,false,$form_id);
         tdomf_set_option_form(TDOMF_OPTION_MSG_SUB_FUTURE,false,$form_id);
         tdomf_set_option_form(TDOMF_OPTION_MSG_SUB_SPAM,false,$form_id);
         tdomf_set_option_form(TDOMF_OPTION_MSG_SUB_MOD,false,$form_id);
         tdomf_set_option_form(TDOMF_OPTION_MSG_SUB_ERROR,false,$form_id);
         tdomf_set_option_form(TDOMF_OPTION_MSG_PERM_BANNED_USER,false,$form_id);
         tdomf_set_option_form(TDOMF_OPTION_MSG_PERM_BANNED_IP,false,$form_id);
         tdomf_set_option_form(TDOMF_OPTION_MSG_PERM_THROTTLE,false,$form_id);
         tdomf_set_option_form(TDOMF_OPTION_MSG_PERM_INVALID_USER,false,$form_id);
         tdomf_set_option_form(TDOMF_OPTION_MSG_PERM_INVALID_NOUSER,false,$form_id);
         $message = __("Messages Reset.","tdomf");         
    } else if(isset($_REQUEST['dismiss'])) {
         check_admin_referer('tdomf-form-hacker');
         $mode = "new-post-hack";
         if(tdomf_get_option_form(TDOMF_OPTION_SUBMIT_PAGE,$form_id)) {
            $mode = "new-page-hack";
         }
         if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'preview')
         {
             $curr = tdomf_preview_form(array('tdomf_form_id' => $form_id),$mode);
             tdomf_set_option_form(TDOMF_OPTION_FORM_PREVIEW_HACK_ORIGINAL,$curr,$form_id);
         } 
         else
         {
             $curr = tdomf_generate_form($form_id,$mode);
             tdomf_set_option_form(TDOMF_OPTION_FORM_HACK_ORIGINAL,$curr,$form_id);
         }
         $message = __("Error Dismissed.","tdomf");        
    }
    if(!empty($message)) {
    ?> <div id="message" class="updated fade"><p><?php echo $message ?></p></div> <?php
    }
  }
}

function tdomf_show_form_hacker() {
  
  $form_id = false;
  if(isset($_REQUEST['form'])) {
    $form_id = $_REQUEST['form'];
  } else {
    $form_id = tdomf_get_first_form_id();
  }

  if($form_id == false || !tdomf_form_exists($form_id) ) { ?>
    <div class="wrap">
       <h2><?php _e('Form Hacker', 'tdomf') ?></h2>
       <p><?php 
       if(is_numeric($form_id)) { 
         printf(__('Invalid Form ID %s specified!'),$form_id); 
       } else { 
         _e('No Form ID specified!');
       } ?></p>
    </div>
  <?php } else if(isset($_REQUEST['diff'])) { ?>
    <div class="wrap">
          <h2><?php _e('Form Diff', 'tdomf') ?></h2>
          <?php tdomf_form_hacker_diff($form_id); ?>
    </div>
  <?php } else {

    if(tdomf_get_option_form(TDOMF_OPTION_SUBMIT_PAGE,$form_id)) {
      $mode = "new-page-hack";
    } else {
       $mode = "new-post-hack";
    }
    tdomf_form_hacker_actions($form_id);
    
    $message = tdomf_get_error_messages(true,$form_id);
    if(!empty($message)) { ?>
        <div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
    <?php }
    
    $form_ids = tdomf_get_form_ids(); ?>
        
        <div class="wrap">
        <?php if(!isset($_REQUEST['text'])) { ?>
          <h2><?php _e('Form Hacker', 'tdomf') ?></h2>
        <?php } else { ?>
          <h2><?php _e('Message Hacker', 'tdomf') ?></h2>
        <?php } ?>

          <script type="text/javascript">
            function tdomfHideHelp() {
                jQuery('#tdomf_help').attr('class','hidden');
                jQuery('#tdomf_show_help').attr('class','');
                jQuery('#tdomf_hide_help').attr('class','hidden');
            }
            function tdomfShowHelp() {
                jQuery('#tdomf_help').attr('class','');
                jQuery('#tdomf_show_help').attr('class','hidden');
                jQuery('#tdomf_hide_help').attr('class','');
            }
          </script>
          
          <?php if(count($form_ids) > 1) { ?>
                <ul class="subsubsub">
                <?php foreach($form_ids as $single_form_id) { ?>
                    <li><a href="admin.php?page=tdomf_show_form_hacker&form=<?php echo $single_form_id->form_id; ?>"<?php if($single_form_id->form_id == $form_id) { ?> class="current" <?php } ?>>
                    <?php printf(__("Form %d","tdomf"),$single_form_id->form_id); ?></a> | </li>
                <?php } ?>
                </ul>
                <?php if(tdomf_wp27()) { ?><br/><br/><?php } ?>
          <?php } ?>

           <ul class="subsubsub">
               <li><a href="admin.php?page=tdomf_show_form_hacker&form=<?php echo $form_id; ?>"<?php if(!isset($_REQUEST['text'])) { ?> class="current" <?php } ?>><?php _e("Form") ?></a> | </li>
               <li><a href="admin.php?page=tdomf_show_form_hacker&text&form=<?php echo $form_id; ?>"<?php if(isset($_REQUEST['text'])) { ?> class="current" <?php } ?>><?php _e("Messages") ?></a> | </li>
               <li><a id='tdomf_show_help' href="javascript:tdomfShowHelp()" ><?php _e("Show Help","tdomf"); ?></a></li>
               <li><a id='tdomf_hide_help' href="javascript:tdomfHideHelp()" class='hidden'><?php _e("Hide Help","tdomf"); ?></a></li>
           </ul>
          
           <?php if(tdomf_wp27()) { ?><br/><br/><?php } ?>
           
           <ul class="subsubsub">
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
    <?php if($updated_pages != false) { ?>
      <li><a href="<?php echo get_permalink($updated_pages[0]); ?>" title="<?php _e("Live on your blog!","tdomf"); ?>" ><?php _e("View Page &raquo;","tdomf"); ?></a> |</li>
    <?php } ?>
    <?php if(tdomf_get_option_form(TDOMF_OPTION_INCLUDED_YOUR_SUBMISSIONS,$form_id) && get_option(TDOMF_OPTION_YOUR_SUBMISSIONS)) { ?>
      <li><a href="users.php?page=tdomf_your_submissions#tdomf_form<?php echo $form_id; ?>" title="<?php _e("Included on the 'Your Submissions' page!",'tdomf'); ?>" >
      <?php _e("View on 'Your Submissions' &raquo;","tdomf"); ?></a> |</li>
    <?php } ?>
     <li><a href="admin.php?page=tdomf_show_options_menu&form=<?php echo $form_id; ?>"><?php printf(__("Options &raquo;","tdomf"),$form_id); ?></a> |</li>
     <li><a href="admin.php?page=tdomf_show_form_menu&form=<?php echo $form_id; ?>"><?php printf(__("Widgets &raquo;","tdomf"),$form_id); ?></a></li>
    </ul>
           
    <?php if(tdomf_wp27()) { ?><br/><br/><?php } ?>
    
          <?php if(isset($_REQUEST['text'])) { ?>
           
           <div id="tdomf_help" class='hidden'>
          
          <p><?php _e("You can use this page to modify any messages outputed from TDOMF for your form. From here you can change the post published messages, post held in moderation, etc. etc.","tdomf"); ?></p>
            
          <p><?php _e("PHP code can be included in the hacked messages. Also TDOMF will automatically expand these macro strings:","tdomf"); ?>
             <ul>
             <li><?php printf(__("<code>%s</code> - User name of the currently logged in user","tdomf"),TDOMF_MACRO_USERNAME); ?>
             <li><?php printf(__("<code>%s</code> - IP of the current visitor","tdomf"),TDOMF_MACRO_IP); ?>
             <li><?php printf(__("<code>%s</code> - The ID of the current form (which is currently %d)","tdomf"),TDOMF_MACRO_FORMID,$form_id); ?>
             <li><?php printf(__("<code>%s</code> - Name of the Form (set in options)","tdomf"),TDOMF_MACRO_FORMNAME); ?>
             <li><?php printf(__("<code>%s</code> - Form Description (set in options)","tdomf"),TDOMF_MACRO_FORMDESCRIPTION); ?>
             <li><?php printf(__("<code>%s</code> - Submission Errors","tdomf"),TDOMF_MACRO_SUBMISSIONERRORS); ?>
             <li><?php printf(__("<code>%s</code> - URL of Submission","tdomf"),TDOMF_MACRO_SUBMISSIONURL); ?>
             <li><?php printf(__("<code>%s</code> - Date of Submission","tdomf"),TDOMF_MACRO_SUBMISSIONDATE); ?>             
             <li><?php printf(__("<code>%s</code> - Time of Submission","tdomf"),TDOMF_MACRO_SUBMISSIONTIME); ?>
             <li><?php printf(__("<code>%s</code> - Title of Submission","tdomf"),TDOMF_MACRO_SUBMISSIONTITLE); ?>
             </ul>
          </p>
          
          </div>
          
          <form method="post">
          <?php if(function_exists('wp_nonce_field')){ wp_nonce_field('tdomf-form-hacker'); } ?>
          
          <p class="submit">
          <input type="submit" value="<?php _e('Save &raquo;','tdomf'); ?>" id="tdomf_hack_messages_save" name="tdomf_hack_messages_save" />
          <input type="submit" value="<?php _e('Reset &raquo;','tdomf'); ?>" id="tdomf_hack_messages_reset" name="tdomf_hack_messages_reset" />
          </p>
          
          <?php if(!tdomf_get_option_form(TDOMF_OPTION_MODERATION,$form_id) && !tdomf_get_option_form(TDOMF_OPTION_REDIRECT,$form_id)){ ?>
              <h3><?php _e('Submission Published','tdomf'); ?></h3>
              <textarea title="true" rows="5" cols="70" name="tdomf_msg_sub_publish" id="tdomf_msg_sub_publish" ><?php echo htmlentities(tdomf_get_message(TDOMF_OPTION_MSG_SUB_PUBLISH,$form_id),ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
              <br/><br/>
          <?php } ?>
                    
          <?php if(intval(tdomf_get_option_form(TDOMF_OPTION_QUEUE_PERIOD,$form_id)) > 0 && !tdomf_get_option_form(TDOMF_OPTION_MODERATION,$form_id)) { ?>
              <h3><?php _e('Submission Queued','tdomf'); ?></h3>
              <textarea title="true" rows="5" cols="70" name="tdomf_msg_sub_future" id="tdomf_msg_sub_future" ><?php echo htmlentities(tdomf_get_message(TDOMF_OPTION_MSG_SUB_FUTURE,$form_id),ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
              <br/><br/>
          <?php } ?>
          
          <?php if(get_option(TDOMF_OPTION_SPAM)) { ?>
              <h3><?php _e('Submission is Spam','tdomf'); ?></h3>
              <textarea title="true" rows="5" cols="70" name="tdomf_msg_sub_spam" id="tdomf_msg_sub_spam" ><?php echo htmlentities(tdomf_get_message(TDOMF_OPTION_MSG_SUB_SPAM,$form_id),ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
              <br/><br/>
          <?php } ?>
          
          <?php if(tdomf_get_option_form(TDOMF_OPTION_MODERATION,$form_id)){ ?>
              <h3><?php _e('Submission awaiting Moderation','tdomf'); ?></h3>
              <textarea title="true" rows="5" cols="70" name="tdomf_msg_sub_mod" id="tdomf_msg_sub_mod" ><?php echo htmlentities(tdomf_get_message(TDOMF_OPTION_MSG_SUB_MOD,$form_id),ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
              <br/><br/>
          <?php } ?>
          
          <h3><?php _e('Submission contains Errors','tdomf'); ?></h3>
          <textarea title="true" rows="5" cols="70" name="tdomf_msg_sub_error" id="tdomf_msg_sub_error" ><?php echo htmlentities(tdomf_get_message(TDOMF_OPTION_MSG_SUB_ERROR,$form_id),ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
          <br/><br/>
          
          <h3><?php _e('Banned User','tdomf'); ?></h3>
          <textarea title="true" rows="5" cols="70" name="tdomf_msg_perm_banned_user" id="tdomf_msg_perm_banned_user" ><?php echo htmlentities(tdomf_get_message(TDOMF_OPTION_MSG_PERM_BANNED_USER,$form_id),ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
          <br/><br/>

          <h3><?php _e('Banned IP','tdomf'); ?></h3>          
          <textarea title="true" rows="5" cols="70" name="tdomf_msg_perm_banned_ip" id="tdomf_msg_perm_banned_ip" ><?php echo htmlentities(tdomf_get_message(TDOMF_OPTION_MSG_PERM_BANNED_IP,$form_id),ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
          <br/><br/>
          
          <?php $throttle_rules = tdomf_get_option_form(TDOMF_OPTION_THROTTLE_RULES,$form_id); 
          if(is_array($throttle_rules) && !empty($throttle_rules)) { ?>
              <h3><?php _e('Throttled Submission','tdomf'); ?></h3>
              <textarea title="true" rows="5" cols="70" name="tdomf_msg_perm_throttle" id="tdomf_msg_perm_throttle" ><?php echo htmlentities(tdomf_get_message(TDOMF_OPTION_MSG_PERM_THROTTLE,$form_id),ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
              <br/><br/>
          <?php } ?>
          
          <?php if(!tdomf_get_option_form(TDOMF_OPTION_ALLOW_EVERYONE,$form_id)) { ?>
              <h3><?php _e('Denied User','tdomf'); ?></h3>
              <textarea title="true" rows="5" cols="70" name="tdomf_msg_perm_invalid_user" id="tdomf_msg_perm_invalid_user" ><?php echo htmlentities(tdomf_get_message(TDOMF_OPTION_MSG_PERM_INVALID_USER,$form_id),ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
              <br/><br/>
          <?php } ?>
          
          <?php if(!tdomf_get_option_form(TDOMF_OPTION_ALLOW_EVERYONE,$form_id)) { ?>
              <h3><?php _e('Banned Unregistered User','tdomf'); ?></h3>
              <textarea title="true" rows="5" cols="70" name="tdomf_msg_perm_invalid_nouser" id="tdomf_msg_perm_invalid_nouser" ><?php echo htmlentities(tdomf_get_message(TDOMF_OPTION_MSG_PERM_INVALID_NOUSER,$form_id),ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
              <br/><br/>
          <?php } ?>
          
          <?php do_action('tdomf_form_hacker_messages_bottom',$form_id,$mode); ?>
                    
          <span class="submit">
          <input type="submit" value="<?php _e('Save &raquo;','tdomf'); ?>" id="tdomf_hack_messages_save" name="tdomf_hack_messages_save" />
          <input type="submit" value="<?php _e('Reset &raquo;','tdomf'); ?>" id="tdomf_hack_messages_reset" name="tdomf_hack_messages_reset" />
          </span>
          
          </form>
          
          <?php } else { ?>
          
          <div id="tdomf_help" class='hidden'>
          
          <p><?php _e("You can use this page to hack the generated HTML code for your form without modifing the code of TDOMF. Please only do this if you know what you are doing. From here you can modify titles, default values, re-arrange fields, etc. etc.","tdomf"); ?></p>
             
          <p><?php _e('Do not modify or remove the "name" and "id" attributes of fields as this is what the widgets and TDOMF use to get input values for processing','tdomf'); ?></p>
             
          <p><?php printf(__("Every time a form is generated, it creates a unique key. If you hack the form, make sure you keep <code>%s</code> (and also <code>%s</code>) within the form. TDOMF will replace this string with the unique key.","tdomf"),TDOMF_MACRO_FORMKEY,TDOMF_MACRO_FORMURL); ?></p>
          
          <p><?php _e("PHP code can be included in the hacked form. Also TDOMF will automatically expand these macro strings:","tdomf"); ?>
             <ul>
             <li><?php printf(__("<code>%s</code> - User name of the currently logged in user","tdomf"),TDOMF_MACRO_USERNAME); ?>
             <li><?php printf(__("<code>%s</code> - IP of the current visitor","tdomf"),TDOMF_MACRO_IP); ?>
             <li><?php printf(__("<code>%s</code> - The form's unique key","tdomf"),TDOMF_MACRO_FORMKEY); ?>
             <li><?php printf(__("<code>%s</code> - The current URL of the form","tdomf"),TDOMF_MACRO_FORMURL); ?>
             <li><?php printf(__("<code>%s</code> - The ID of the current form (which is currently %d)","tdomf"),TDOMF_MACRO_FORMID,$form_id); ?>
             <li><?php printf(__("<code>%s</code> - Name of the Form (set in options)","tdomf"),TDOMF_MACRO_FORMNAME); ?>
             <li><?php printf(__("<code>%s</code> - Form Description (set in options)","tdomf"),TDOMF_MACRO_FORMDESCRIPTION); ?>
             <li><?php printf(__("<code>%s</code> - Form Output (such as preview, errors, etc.). This is automatically encapsulated in a div called tdomf_form_message (and tdomf_form_preview for preview)","tdomf"),TDOMF_MACRO_FORMMESSAGE); ?>
             <li><?php printf(__("<code>%swidget-name%s</code> - Original, unmodified output from 'widget-name'","tdomf"),TDOMF_MACRO_WIDGET_START,TDOMF_MACRO_END); ?>
             </ul>
          </p>
          
          </div>
 
          <form method="post">
          <?php if(function_exists('wp_nonce_field')){ wp_nonce_field('tdomf-form-hacker'); } ?>
      
          <p class="submit">
          <input type="submit" value="<?php _e('Save &raquo;','tdomf'); ?>" id="tdomf_form_hack_save" name="tdomf_form_hack_save" />
          <input type="submit" value="<?php _e('Reset &raquo;','tdomf'); ?>" id="tdomf_form_hack_reset" name="tdomf_form_hack_reset" />
          </p>
          
          <?php if(tdomf_widget_is_preview_avaliable($form_id)) { ?>
          
              <h3><?php _e('Core Form', 'tdomf') ?></h3>
              
          <?php } ?>
          
            <?php $cur_form = tdomf_generate_form($form_id,$mode);
                  $form = $cur_form;
                  $hacked_form = tdomf_get_option_form(TDOMF_OPTION_FORM_HACK,$form_id);
                  if($hacked_form != false) { $form = $hacked_form; } ?>
                  
            <?php if($hacked_form != false) { ?>
              <?php _e("You can diff the hacked form to see what you have changed","tdomf"); ?>
              <ul>
              <li><a href="admin.php?page=tdomf_show_form_hacker&form=<?php echo $form_id; ?>&mode=<?php echo $mode; ?>&diff&form1=hack&form2=cur"><?php _e("Diff Hacked Form with Current Form","tdomf"); ?></a></li>
              <?php $org_form = tdomf_get_option_form(TDOMF_OPTION_FORM_HACK_ORIGINAL,$form_id);  
                    if(trim($cur_form) != trim($org_form)) { ?>
              <li><a href="admin.php?page=tdomf_show_form_hacker&form=<?php echo $form_id; ?>&mode=<?php echo $mode; ?>&diff&form2=hack&form1=org"><?php _e("Diff Hacked Form with Previous Form","tdomf"); ?></a></li>
              <li><a href="admin.php?page=tdomf_show_form_hacker&form=<?php echo $form_id; ?>&mode=<?php echo $mode; ?>&diff&form2=cur&form1=org"><?php _e("Diff Current Form with Previous Form","tdomf"); ?></a></li>
                    <?php } ?>
              </ul>
            <?php }?>
                  
            <textarea title="true" rows="30" cols="100" name="tdomf_form_hack" id="tdomf_form_hack" ><?php echo htmlentities($form,ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
            
          <br/><br/>
          
          <?php if(tdomf_widget_is_preview_avaliable($form_id)) { ?>
          
              <h3><?php _e('Form Preview', 'tdomf') ?></h3>
              
              <?php $cur_preview = tdomf_preview_form(array('tdomf_form_id' => $form_id),$mode);
                    $preview = $cur_preview;
                    $hacked_preview = tdomf_get_option_form(TDOMF_OPTION_FORM_PREVIEW_HACK,$form_id);
                    if($hacked_preview != false) { $preview = $hacked_preview; } ?>
              
              <?php if($hacked_preview != false) { ?>
              <?php _e("You can diff the hacked preview to see what you have changed","tdomf"); ?>
              <ul>
              <li><a href="admin.php?page=tdomf_show_form_hacker&form=<?php echo $form_id; ?>&mode=<?php echo $mode; ?>&diff&form1=hack&form2=cur&type=preview"><?php _e("Diff Hacked Preview with Current Preview","tdomf"); ?></a></li>
              <?php $org_preview = tdomf_get_option_form(TDOMF_OPTION_FORM_PREVIEW_HACK_ORIGINAL,$form_id);  
                    if(trim($cur_preview) != trim($org_preview)) { ?>
              <li><a href="admin.php?page=tdomf_show_form_hacker&form=<?php echo $form_id; ?>&mode=<?php echo $mode; ?>&diff&form2=hack&form1=org&type=preview"><?php _e("Diff Hacked Preview with Previous Preview","tdomf"); ?></a></li>
              <li><a href="admin.php?page=tdomf_show_form_hacker&form=<?php echo $form_id; ?>&mode=<?php echo $mode; ?>&diff&form2=cur&form1=org&type=preview"><?php _e("Diff Current Preview with Previous Preview","tdomf"); ?></a></li>
                    <?php } ?>
              </ul>
            <?php }?>                    
                    
              <textarea title="true" rows="15" cols="100" name="tdomf_form_preview_hack" id="tdomf_form_preview_hack" ><?php echo htmlentities($preview,ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
                
              <br/><br/>
                
          <?php } ?>

          <!-- @TODO Validation Message Hacker -->
          <!-- @TODO Upload Form Hacker -->     
          <?php do_action('tdomf_form_hacker_bottom',$form_id,$mode); ?>
          
          <span class="submit">
          <input type="submit" value="<?php _e('Save &raquo;','tdomf'); ?>" id="tdomf_form_hack_save" name="tdomf_form_hack_save" />
          <input type="submit" value="<?php _e('Reset &raquo;','tdomf'); ?>" id="tdomf_form_hack_reset" name="tdomf_form_hack_reset" />
          </span>
          
          </form>
          
          <!-- @TODO: warning about updated form (with dismiss link) -->
          
          <?php } ?>
          
        </div>
    <?php
  }
}
?>
