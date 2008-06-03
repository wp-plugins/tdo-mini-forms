<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

function tdomf_form_hacker_diff($form_id) {
  $mode = $_REQUEST['mode'];
  $form1 = $_REQUEST['form1'];
  $form2 = $_REQUEST['form2'];
  
  if($form1 == 'cur') {
    $form1 = tdomf_generate_form($form_id,$mode);
  } else if($form1 == 'org') {
    $form1 = tdomf_get_option_form(TDOMF_OPTION_FORM_HACK_ORIGINAL,$form_id);
  } else if($form1 == 'hack') {
    $form1 = tdomf_get_option_form(TDOMF_OPTION_FORM_HACK,$form_id);
  }
  
  if($form2 == 'cur') {
    $form2 = tdomf_generate_form($form_id,$mode);
  } else if($form2 == 'org') {
    $form2 = tdomf_get_option_form(TDOMF_OPTION_FORM_HACK_ORIGINAL,$form_id);
  } else if($form2 == 'hack') {
    $form2 = tdomf_get_option_form(TDOMF_OPTION_FORM_HACK,$form_id);
  }

  echo "<pre>".htmlentities(PHPDiff($form1,trim($form2)),ENT_NOQUOTES,get_bloginfo('charset'))."</pre>";
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
          #$form_new = str_replace("\t","   ",$form_new);
          if (get_magic_quotes_gpc()) {
             $form_new = stripslashes($form_new);
          }
          if(strpos($form_new,TDOMF_MACRO_FORMKEY) !== false) {
            $form_cur = trim(tdomf_generate_form($form_id,$mode));
            #$form_cur = str_replace("\t","   ",$form_cur);
            tdomf_set_option_form(TDOMF_OPTION_FORM_HACK,trim($form_new),$form_id);
            tdomf_set_option_form(TDOMF_OPTION_FORM_HACK_ORIGINAL,$form_cur,$form_id);
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
       tdomf_set_option_form(TDOMF_OPTION_FORM_HACK_ORIGINAL,false,$form_id);
       tdomf_set_option_form(TDOMF_OPTION_FORM_HACK,false,$form_id);
       tdomf_set_option_form(TDOMF_OPTION_FORM_HACK,false,$form_id);
       $message = __("Reset Hacked Forms.","tdomf");
     } else if(isset($_REQUEST['tdomf_hack_messages_save'])) {
         check_admin_referer('tdomf-form-hacker');
         
         if(!function_exists('tdomf_set_form_message')) {
             function tdomf_set_form_message($form_id,$name,$opt) {
                 if(isset($_REQUEST[$name])) {
                     $msg = $_REQUEST[$name];
                     if (get_magic_quotes_gpc()) {
                         $msg = stripslashes($_REQUEST[$name]);
                     }
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
    tdomf_form_hacker_actions($form_id);
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
          <?php } ?>

           <ul class="subsubsub">
               <li><a href="admin.php?page=tdomf_show_form_hacker&form=<?php echo $form_id; ?>"<?php if(!isset($_REQUEST['text'])) { ?> class="current" <?php } ?>><?php _e("Form") ?></a> | </li>
               <li><a href="admin.php?page=tdomf_show_form_hacker&text&form=<?php echo $form_id; ?>"<?php if(isset($_REQUEST['text'])) { ?> class="current" <?php } ?>><?php _e("Messages") ?></a> | </li>
               <li><a id='tdomf_show_help' href="javascript:tdomfShowHelp()" ><?php _e("Show Help","tdomf"); ?></a></li>
               <li><a id='tdomf_hide_help' href="javascript:tdomfHideHelp()" class='hidden'><?php _e("Hide Help","tdomf"); ?></a></li>
           </ul>
          
          <?php if(isset($_REQUEST['text'])) { ?>
           
           <div id="tdomf_help" class='hidden'>
          
          <p><?php _e("You can use this page to modify any messages outputed from TDOMF for your form. From here you can change the post published messages, post held in moderation, etc. etc.","tdomf"); ?></p>
            
          <p><?php _e("PHP code can be included in the hacked messages. Also TDOMF will automatically expand these macro strings:","tdomf"); ?>
             <ul>
             <li><?php printf(__("<code>%s</code> - User name of the currently logged in user","tdomf"),TDOMF_MACRO_USERNAME); ?>
             <li><?php printf(__("<code>%s</code> - IP of the current visitor","tdomf"),TDOMF_MACRO_IP); ?>
             <li><?php printf(__("<code>%s</code> - The ID of the current form (which is current %d)","tdomf"),TDOMF_MACRO_FORMID,$form_id); ?>
             <li><?php printf(__("<code>%s</code> - Name of the Form (set in options)","tdomf"),TDOMF_MACRO_FORMNAME); ?>
             <li><?php printf(__("<code>%s</code> - Form Description (set in options)","tdomf"),TDOMF_MACRO_FORMDESCRIPTION); ?>
             <li><?php printf(__("<code>%s</code> - Submission Errors","tdomf"),TDOMF_MACRO_SUBMISSIONERRORS); ?>
             <li><?php printf(__("<code>%s</code> - URL of Submission","tdomf"),TDOMF_MACRO_SUBMISSIONURL); ?>
             <li><?php printf(__("<code>%s</code> - Date of Submission","tdomf"),TDOMF_MACRO_SUBMISSIONDATE); ?>             
             <li><?php printf(__("<code>%s</code> - Time of Submission","tdomf"),TDOMF_MACRO_SUBMISSIONTIME); ?>
             <li><?php printf(__("<code>%s</code> - Title of Submission","tdomf"),TDOMF_MACRO_SUBMISSIONTITLE); ?>
          </p>
          
          </div>
          
          <form method="post">
          <?php if(function_exists('wp_nonce_field')){ wp_nonce_field('tdomf-form-hacker'); } ?>
          
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
          
          <!-- @TODO: notification messages -->
                    
          <span class="submit">
          <input type="submit" value="<?php _e('Save','tdomf'); ?>" id="tdomf_hack_messages_save" name="tdomf_hack_messages_save" />
          <input type="submit" value="<?php _e('Reset','tdomf'); ?>" id="tdomf_hack_messages_reset" name="tdomf_hack_messages_reset" />
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
             <li><?php printf(__("<code>%s</code> - The ID of the current form (which is current %d)","tdomf"),TDOMF_MACRO_FORMID,$form_id); ?>
             <li><?php printf(__("<code>%s</code> - Name of the Form (set in options)","tdomf"),TDOMF_MACRO_FORMNAME); ?>
             <li><?php printf(__("<code>%s</code> - Form Description (set in options)","tdomf"),TDOMF_MACRO_FORMDESCRIPTION); ?>
             <li><?php printf(__("<code>%s</code> - Form Output (such as preview, errors, etc.). This is automatically encapsulated in a div called tdomf_form_message (and tdomf_form_preview for preview)","tdomf"),TDOMF_MACRO_FORMMESSAGE); ?>
             <li><?php printf(__("<code>%swidget-name%s</code> - Original, unmodified output from 'widget-name'","tdomf"),TDOMF_MACRO_WIDGET_START,TDOMF_MACRO_END); ?>
          </p>
          
          </div>
 
          <?php if(tdomf_get_option_form(TDOMF_OPTION_SUBMIT_PAGE,$form_id)) {
                   $mode = "new-page-hack";
                } else {
                   $mode = "new-post-hack";
                } ?>
          
          <form method="post">
          <?php if(function_exists('wp_nonce_field')){ wp_nonce_field('tdomf-form-hacker'); } ?>
          
            <?php $cur_form = tdomf_generate_form($form_id,$mode);
                  $form = $cur_form;
                  $hacked_form = tdomf_get_option_form(TDOMF_OPTION_FORM_HACK,$form_id);
                  if($hacked_form != false) { $form = $hacked_form; } ?>
                  
            <?php if($hacked_form != false) { ?>
              <?php _e("You can diff the hacked form to see what you have changed","tdomf"); ?>
              <ul>
              <li><a href="admin.php?page=tdomf_show_form_hacker&form=<?php echo $form_id; ?>&mode=<?php echo $mode; ?>&diff&form1=hack&form2=cur"><?php _e("Diff Hacked Form with Current Form","tdomf"); ?></a></li>
              <?php $org_form = tdomf_get_option_form(TDOMF_OPTION_FORM_HACK_ORIGINAL,$form_id);  
                    if($cur_form != $org_form) { ?>
              <li><a href="admin.php?page=tdomf_show_form_hacker&form=<?php echo $form_id; ?>&mode=<?php echo $mode; ?>&diff&form1=hack&form2=org"><?php _e("Diff Hacked Form with Previous Form","tdomf"); ?></a></li>
              <li><a href="admin.php?page=tdomf_show_form_hacker&form=<?php echo $form_id; ?>&mode=<?php echo $mode; ?>&diff&form1=cur&form2=org"><?php _e("Diff Current Form with Previous Form","tdomf"); ?></a></li>
                    <?php } ?>
              </ul>
            <?php }?>
                  
            <textarea title="true" rows="30" cols="100" name="tdomf_form_hack" id="tdomf_form_hack" ><?php echo htmlentities($form,ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
            
          <br/><br/>
          
          <span class="submit">
          <input type="submit" value="<?php _e('Save','tdomf'); ?>" id="tdomf_form_hack_save" name="tdomf_form_hack_save" />
          <input type="submit" value="<?php _e('Reset','tdomf'); ?>" id="tdomf_form_hack_reset" name="tdomf_form_hack_reset" />
          </span>
          
          </form>
          
          <!-- @TODO: warning about updated form (with dismiss link) -->
          <!-- @TODO: upload form -->
          
          <?php } ?>
          
        </div>
    <?php
  }
}

    /** 
        Diff implemented in pure php, written from scratch. 
        Copyright (C) 2003  Daniel Unterberger <diff.phpnet@holomind.de> 
        Copyright (C) 2005  Nils Knappmeier next version  
         
        This program is free software; you can redistribute it and/or 
        modify it under the terms of the GNU General Public License 
        as published by the Free Software Foundation; either version 2 
        of the License, or (at your option) any later version. 
         
        This program is distributed in the hope that it will be useful, 
        but WITHOUT ANY WARRANTY; without even the implied warranty of 
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
        GNU General Public License for more details. 
         
        You should have received a copy of the GNU General Public License 
        along with this program; if not, write to the Free Software 
        Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. 
         
        http://www.gnu.org/licenses/gpl.html 

        About: 
        I searched a function to compare arrays and the array_diff() 
        was not specific enough. It ignores the order of the array-values. 
        So I reimplemented the diff-function which is found on unix-systems 
        but this you can use directly in your code and adopt for your needs. 
        Simply adopt the formatline-function. with the third-parameter of arr_diff() 
        you can hide matching lines. Hope someone has use for this. 

        Contact: d.u.diff@holomind.de <daniel unterberger> 
    **/ 

## PHPDiff returns the differences between $old and $new, formatted 
## in the standard diff(1) output format. 
function PHPDiff($old,$new)  
{ 
   # split the source text into arrays of lines 
   $t1 = explode("\n",$old); 
   $x=array_pop($t1);  
   if ($x>'') $t1[]="$x\n\\ No newline at end of file"; 
   $t2 = explode("\n",$new); 
   $x=array_pop($t2);  
   if ($x>'') $t2[]="$x\n\\ No newline at end of file"; 

   # build a reverse-index array using the line as key and line number as value 
   # don't store blank lines, so they won't be targets of the shortest distance 
   # search 
   foreach($t1 as $i=>$x) if ($x>'') $r1[$x][]=$i; 
   foreach($t2 as $i=>$x) if ($x>'') $r2[$x][]=$i; 

   $a1=0; $a2=0;   # start at beginning of each list 
   $actions=array(); 

   # walk this loop until we reach the end of one of the lists 
   while ($a1<count($t1) && $a2<count($t2)) { 
     # if we have a common element, save it and go to the next 
     if ($t1[$a1]==$t2[$a2]) { $actions[]=4; $a1++; $a2++; continue; }  

     # otherwise, find the shortest move (Manhattan-distance) from the 
     # current location 
     $best1=count($t1); $best2=count($t2); 
     $s1=$a1; $s2=$a2; 
     while(($s1+$s2-$a1-$a2) < ($best1+$best2-$a1-$a2)) { 
       $d=-1; 
       foreach((array)@$r1[$t2[$s2]] as $n)  
         if ($n>=$s1) { $d=$n; break; } 
       if ($d>=$s1 && ($d+$s2-$a1-$a2)<($best1+$best2-$a1-$a2)) 
         { $best1=$d; $best2=$s2; } 
       $d=-1; 
       foreach((array)@$r2[$t1[$s1]] as $n)  
         if ($n>=$s2) { $d=$n; break; } 
       if ($d>=$s2 && ($s1+$d-$a1-$a2)<($best1+$best2-$a1-$a2)) 
         { $best1=$s1; $best2=$d; } 
       $s1++; $s2++; 
     } 
     while ($a1<$best1) { $actions[]=1; $a1++; }  # deleted elements 
     while ($a2<$best2) { $actions[]=2; $a2++; }  # added elements 
  } 

  # we've reached the end of one list, now walk to the end of the other 
  while($a1<count($t1)) { $actions[]=1; $a1++; }  # deleted elements 
  while($a2<count($t2)) { $actions[]=2; $a2++; }  # added elements 

  # and this marks our ending point 
  $actions[]=8; 

  # now, let's follow the path we just took and report the added/deleted 
  # elements into $out. 
  $op = 0; 
  $x0=$x1=0; $y0=$y1=0; 
  $out = array(); 
  foreach($actions as $act) { 
    if ($act==1) { $op|=$act; $x1++; continue; } 
    if ($act==2) { $op|=$act; $y1++; continue; } 
    if ($op>0) { 
      $xstr = ($x1==($x0+1)) ? $x1 : ($x0+1).",$x1"; 
      $ystr = ($y1==($y0+1)) ? $y1 : ($y0+1).",$y1"; 
      if ($op==1) $out[] = "{$xstr}d{$y1}"; 
      elseif ($op==3) $out[] = "{$xstr}c{$ystr}"; 
      while ($x0<$x1) { $out[] = '< '.$t1[$x0]; $x0++; }   # deleted elems 
      if ($op==2) $out[] = "{$x1}a{$ystr}"; 
      elseif ($op==3) $out[] = '---'; 
      while ($y0<$y1) { $out[] = '> '.$t2[$y0]; $y0++; }   # added elems 
    } 
    $x1++; $x0=$x1; 
    $y1++; $y0=$y1; 
    $op=0; 
  } 
  $out[] = ''; 
  return join("\n",$out); 
} 
?>
