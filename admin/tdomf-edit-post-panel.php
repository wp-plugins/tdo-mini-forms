<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

////////////////////////////////////////////////////
// Code for the tdomf edit panel on the post page //
////////////////////////////////////////////////////

# TODO: nonce support

// Grab a list of user ids of all users, to use in the drop-down menu
//
function tdomf_get_all_users() {
    global $wpdb;
    $query = "SELECT * ";
    $query .= "FROM $wpdb->users ";
    $query .= "ORDER BY ID DESC";
    return $wpdb->get_results( $query );
}

// Add the sidebar panel
//
add_action('dbx_post_sidebar', 'tdomf_show_edit_post_panel');
//
// Show the Edit Post Panel
//
function tdomf_show_edit_post_panel() {
  global $post;

  // don't show on new post
  if($post->ID > 0) {

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

  // use JavaScript SACK library for AJAX
  wp_print_scripts( array( 'sack' ));

  // I could stick this AJAX call into the Admin header, however, I don't want
  // it hanging around on every admin page and potentially being called
  // accidentially from some other TDOMF page
?>
         <script type="text/javascript">
         //<![CDATA[
         function tdomf_ajax_edit_post( flag, is_user, user, name, email, web )
         {
           var mysack = new sack( "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );
           mysack.execute = 1;
           mysack.method = 'POST';
           mysack.setVar( "action", "tdomf_edit_post" );
           mysack.setVar( "post_ID", "<?php echo $post->ID; ?>" );
           mysack.setVar( "tdomf_flag", flag.checked );
           if(is_user.checked) {
              mysack.setVar( "tdomf_user", user.value);
           } else {
              mysack.setVar( "tdomf_name", name.value );
              mysack.setVar( "tdomf_email", email.value );
              mysack.setVar( "tdomf_web", web.value );
           }
           mysack.encVar( "cookie", document.cookie, false );
           mysack.onError = function() { alert('AJAX error in looking up tdomf' )};
           mysack.runAJAX();

           return true;
         }

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
        //]]>
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
                          <option value="<?php echo $user->ID; ?>" <?php if($user->ID == $submitter_id) { ?> selected <?php } ?> ><?php echo $user->user_login; ?><?php if($user->ID == get_option(TDOMF_DEFAULT_AUTHOR)) { ?> (Default User) <?php } ?><?php if(!empty($status) && $status == TDOMF_USER_STATUS_BANNED) { ?> (Banned User) <?php } ?></option>
                      <?php } } ?>
               </select>

                <br/><br/>

                <label for="tdomf_submitter_not_user" class="selectit">
                <input id="tdomf_submitter_not_user" type="radio" name="tdomf_submitter" value="tdomf_submitter_not_user" <?php if(empty($submitter_id)) { ?>checked<?php } ?> <?php if(!$can_edit || !$tdomf_flag){ ?> disabled <?php } ?> onChange="tdomf_update_panel();" />
                Submitter does not have a user account</label>

                <label for="tdomf_submitter_name" class="selectit">Name
                <input type="textfield" value="<?php echo htmlentities(get_post_meta($post->ID, TDOMF_KEY_NAME, true),ENT_QUOTES); ?>" name="tdomf_submitter_name" id="tdomf_submitter_name" onClick="tdomf_update_panel();" <?php if(!$can_edit || !$tdomf_flag || !empty($submitter_id)){ ?> disabled <?php } ?> />
                </label>

                <label for="tdomf_submitter_email" class="selectit">Email
                <input type="textfield" value="<?php echo htmlentities(get_post_meta($post->ID, TDOMF_KEY_EMAIL, true),ENT_QUOTES); ?>" name="tdomf_submitter_email" id="tdomf_submitter_email" onClick="tdomf_update_panel();" <?php if(!$can_edit || !$tdomf_flag || !empty($submitter_id)){ ?> disabled <?php } ?> />
                </label>

                <label for="tdomf_submitter_web" class="selectit">Webpage
                <input type="textfield" value="<?php echo htmlentities(get_post_meta($post->ID, TDOMF_KEY_WEB, true),ENT_QUOTES); ?>" name="tdomf_submitter_web" id="tdomf_submitter_web" onClick="tdomf_update_panel();" <?php if(!$can_edit || !$tdomf_flag || !empty($submitter_id)){ ?> disabled <?php } ?> />
                </label>

                <br/><br/>

                <?php if(!empty($submitter_ip)) { ?>
                  This post was submitted from IP <?php echo $submitter_ip; ?>.
                <?php } else { ?>
                  No IP was recorded when this post was submitted.
                <?php } ?>
                </fieldset>

                 <p><input type="button" value="Update &raquo;" onclick="tdomf_ajax_edit_post(this.form.tdomf_flag, tdomf_submitter_is_user, tdomf_submitter_user, tdomf_submitter_name, tdomf_submitter_email, tdomf_submitter_web);" />

                </div>
        </fieldset>

<?php
}
}

// Add a handler for the AJAX
//
add_action('wp_ajax_tdomf_edit_post', 'tdomf_save_post');
//
// Handler for AJAX
//
function tdomf_save_post() {
    $post_id = (int) $_POST['post_ID'];
    if($_POST['tdomf_flag'] == "false") {
      delete_post_meta($post_id, TDOMF_KEY_FLAG);
      tdomf_log_message("Removed post $post_id from TDOMF");
      die("alert('TDOMF: Post $post_id is no longer managed by TDOMF!')");
    } else {
      add_post_meta($post_id, TDOMF_KEY_FLAG, true, true);
      if(isset($_POST["tdomf_user"])) {
        $user_id = $_POST["tdomf_user"];
        delete_post_meta($post_id, TDOMF_KEY_USER_ID);
        add_post_meta($post_id, TDOMF_KEY_USER_ID, $user_id, true);
        tdomf_log_message("Submitter info for post $post_id added");
        die("alert('TDOMF: Submitter info for post $post_id updated')");
      } else {
        // do this so that we *know* that submitter user is not used
        delete_post_meta($post_id, TDOMF_KEY_USER_ID);
        $name = $_POST["tdomf_name"];
        delete_post_meta($post_id, TDOMF_KEY_NAME);
        add_post_meta($post_id, TDOMF_KEY_NAME, $name, true);
        $email = $_POST["tdomf_email"];
        delete_post_meta($post_id, TDOMF_KEY_EMAIL);
        add_post_meta($post_id, TDOMF_KEY_EMAIL, $email, true);
        $web = $_POST["tdomf_web"];
        delete_post_meta($post_id, TDOMF_KEY_WEB);
        add_post_meta($post_id, TDOMF_KEY_WEB, $web, true);
        tdomf_log_message("Submitter info for post $post_id added");
        die("alert('TDOMF: Submitter info for post $post_id updated')");
      }
  }
  tdomf_log_message("Error captured in EditPostPanel:tdomf_save_post");
  die("alert('TDOMF: Error! Incomplete information provided!')");
}

?>
