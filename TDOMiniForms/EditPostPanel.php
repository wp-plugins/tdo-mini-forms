<?php
/* Code for the tdomf edit panel on the post page */

// Add the panel
add_action('dbx_post_sidebar', 'tdomf_show_edit_post_panel');
// Do something when the post is saved!
add_action('save_post', 'tdomf_save_post', 1);
add_action('edit_post', 'tdomf_save_post', 1);

// modify the post accordingly
function tdomf_save_post($post_id) {
  /* TODO: When the manage menu is used to "publish" a post, this gets called
     and there is no way to tell if this is from an edit page or from the
     manage menu. Can cause post to become unflagged. Workaround in 
     manage menu. */
  if(!empty($_POST)) {
    if(!isset($_POST['tdomf_flag'])) {
      delete_post_meta($post_id, TDOMF_KEY_FLAG);
    } else {
      add_post_meta($post_id, TDOMF_KEY_FLAG, true, true);
      if(isset($_POST["tdomf_submitter"]) && "tdomf_submitter_is_user" == $_POST["tdomf_submitter"] && isset($_POST["tdomf_submitter_user"])) {
        add_post_meta($post_id, TDOMF_KEY_USER_ID, $_POST["tdomf_submitter_user"], true);
        // TDOMF_KEY_USER_NAME
      } else if(isset($_POST["tdomf_submitter"]) && "tdomf_submitter_not_user" == $_POST["tdomf_submitter"]) {
        // do this so that we *know* that submitter user is not used
        delete_post_meta($post_id, TDOMF_KEY_USER_ID);
        $name = "";
        if(isset($_POST["tdomf_submitter_name"])) { $name = $_POST["tdomf_submitter_name"]; }
        add_post_meta($post_id, TDOMF_KEY_NAME, $name, true);
        $email = "";
        if(isset($_POST["tdomf_submitter_email"])) { $email = $_POST["tdomf_submitter_email"]; }
        add_post_meta($post_id, TDOMF_KEY_EMAIL, $email, true);
        $web = "";
        if(isset($_POST["tdomf_submitter_web"])) { $web = $_POST["tdomf_submitter_web"]; }
        add_post_meta($post_id, TDOMF_KEY_WEB, $web, true);
      }
    }
  }
}

// show the "edit" post panel
function tdomf_show_edit_post_panel() {
  global $post;

  $can_edit = false;
  if(current_user_can('publish_posts')) {
    $can_edit = true;
  }
  
  $is_tdomf = false;
  $tdomf_flag = get_post_meta($post->ID, TDOMF_KEY_FLAG, true);
  if(!empty($tdomf_flag)) {
    $is_tdomf = true;
  }
  
  $submitter_id = get_post_meta($post->ID, TDOMF_KEY_USER_ID, true);
  
  $submitter_ip = get_post_meta($post->ID, TDOMF_KEY_IP, true);
  
?>
        <SCRIPT type="text/javascript">
        <!--
        function tdomf_update_panel() {
          <?php if($can_edit) { ?>
            var flag = document.getElementById("tdomf_flag").checked;
            if(flag) {
              //document.getElementById("tdomf_submitter").disabled = false;
              document.getElementById("tdomf_submitter_is_user").disabled = false;
              document.getElementById("tdomf_submitter_not_user").disabled = false;
              var is_user = document.getElementById("tdomf_submitter_is_user").checked;
              document.getElementById("tdomf_submitter_user").disabled = !is_user;
              document.getElementById("tdomf_submitter_name").disabled = is_user;
              document.getElementById("tdomf_submitter_email").disabled = is_user;
              document.getElementById("tdomf_submitter_web").disabled = is_user;
            } else {
              // disable everything
              //document.getElementById("tdomf_submitter").disabled = true;
              document.getElementById("tdomf_submitter_is_user").disabled = true;
              document.getElementById("tdomf_submitter_user").disabled = true;
              document.getElementById("tdomf_submitter_not_user").disabled = true;
              document.getElementById("tdomf_submitter_name").disabled = true;
              document.getElementById("tdomf_submitter_email").disabled = true;
              document.getElementById("tdomf_submitter_web").disabled = true;
            }
          <?php } else { ?>
            // nothing can be enabled
            //document.getElementById("tdomf_submitter").disabled = true;
            document.getElementById("tdomf_flag").disabled = true;
            document.getElementById("tdomf_submitter_is_user").disabled = true;
            document.getElementById("tdomf_submitter_user").disabled = true;
            document.getElementById("tdomf_submitter_not_user").disabled = true;
            document.getElementById("tdomf_submitter_name").disabled = true;
            document.getElementById("tdomf_submitter_email").disabled = true;
            document.getElementById("tdomf_submitter_web").disabled = true;
          <?php } ?>
        }
        -->
        </SCRIPT>

        <fieldset class="dbx-box">
        <h3 id="posttdomf" class="dbx-handle"><?php _e('TDOMF', "tdomf"); ?></h3>
                <div class="dbx-content">
                <fieldset>
                <legend>
                <input id="tdomf_flag" type="checkbox" name="tdomf_flag" <?php if($tdomf_flag){ ?>checked<?php } ?> <?php if(!$can_edit){ ?> disabled <?php } ?> onClick="tdomf_update_panel();" />
                <label for="tdomf_flag">Managed by TDOMF</label>
                </legend>
                
                <br/>
                
                <?php if(!empty($submitter_id) && $submitter_id == get_option(TDOMF_DEFAULT_AUTHOR)) { ?>
                  <span style="color:red;font-size:larger;">The submitter of this post is set as the "default user"! Please correct!</span>
                  <br/><br/>
                <?php } ?>
                
                <label for="tdomf_submitter_is_user" class="selectit">
                <input id="tdomf_submitter_is_user" type="radio" name="tdomf_submitter" value="tdomf_submitter_is_user" <?php if(!empty($submitter_id)) { ?>checked<?php } ?> <?php if(!$can_edit || !$tdomf_flag){ ?> disabled <?php } ?> onChange="tdomf_update_panel();" />
                Submitter is an existing user</label>
                
                <select id="tdomf_submitter_user" name="tdomf_submitter_user" <?php if(!$can_edit || !$tdomf_flag || empty($submitter_id)){ ?> disabled <?php } ?> onChange="tdomf_update_panel();" >
                <?php $users = tdomf_get_all_users();
                      foreach($users as $user) {
                        $status = get_usermeta($user->ID,TDOMF_KEY_STATUS);
                        if($user->ID == $submitter_id || $user->ID != get_option(TDOMF_DEFAULT_AUTHOR)) { ?>
                          <option value="<?php echo $user->ID; ?>" <?php if($user->ID == $submitter_id) { ?> selected <?php } ?> ><?php echo $user->user_login; ?><?php if($user->ID == get_option(TDOMF_DEFAULT_AUTHOR)) { ?> (Default User) <?php } ?><?php if(!empty($status) && $status == "Banned") { ?> (Banned User) <?php } ?></option>
                      <?php } } ?>
               </select>
                
                <br/><br/>
                
                <label for="tdomf_submitter_not_user" class="selectit">
                <input id="tdomf_submitter_not_user" type="radio" name="tdomf_submitter" value="tdomf_submitter_not_user" <?php if(empty($submitter_id)) { ?>checked<?php } ?> <?php if(!$can_edit || !$tdomf_flag){ ?> disabled <?php } ?> onChange="tdomf_update_panel();" />
                Submitter does not have a user account</label>
                
                <label for="tdomf_submitter_name" class="selectit">Name
                <input type="text" value="<?php echo get_post_meta($post->ID, TDOMF_KEY_NAME, true); ?>" name="tdomf_submitter_name" id="tdomf_submitter_name" onClick="tdomf_update_panel();" <?php if(!$can_edit || !$tdomf_flag || !empty($submitter_id)){ ?> disabled <?php } ?> />
                </label>
                
                <label for="tdomf_submitter_email" class="selectit">Email
                <input type="text" value="<?php echo get_post_meta($post->ID, TDOMF_KEY_EMAIL, true); ?>" name="tdomf_submitter_email" id="tdomf_submitter_email" onClick="tdomf_update_panel();" <?php if(!$can_edit || !$tdomf_flag || !empty($submitter_id)){ ?> disabled <?php } ?> />
                </label>
                
                <label for="tdomf_submitter_web" class="selectit">Webpage 
                <input type="text" value="<?php echo get_post_meta($post->ID, TDOMF_KEY_WEB, true); ?>" name="tdomf_submitter_web" id="tdomf_submitter_web" onClick="tdomf_update_panel();" <?php if(!$can_edit || !$tdomf_flag || !empty($submitter_id)){ ?> disabled <?php } ?> />
                </label>

                <br/><br/>
                
                <?php if(!empty($submitter_ip)) { ?>
                  This post was submitted from IP <?php echo $submitter_ip; ?>.
                <?php } else { ?>
                  No IP was recorded when this post was submitted.
                <?php } ?>
                </fieldset>
                </div>
        </fieldset>
        
        
<?php
}

?>
