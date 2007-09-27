<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

//////////////////////////////
// Code for Form generation //
//////////////////////////////

// TODO: Ajax (probably never)
// TODO: Clear and/or reset button

// Checks if current user/ip has permissions to post!
//
function tdomf_check_permissions_form() {
   global $current_user;

   get_currentuserinfo();

   // User Banned
   //
   if(is_user_logged_in()) {
       $user_status = get_usermeta($current_user->ID,TDOMF_KEY_STATUS);
       if($user_status == TDOMF_USER_STATUS_BANNED) {
          tdomf_log_message("Banned user $current_user->user_name tried to submit a post!",TDOMF_LOG_ERROR);
          return sprintf(__("You (%s) are banned from using this form. If this is an error please contact the <a href=\"mailto:%s\">admins</a>.","tdomf"),$current_user->user_name,get_bloginfo('admin_email'));
       }
   }

  // IP banned
  //
  $ip =  $_SERVER['REMOTE_ADDR'];
  $banned_ips = get_option(TDOMF_BANNED_IPS);
  if($banned_ips != false) {
  	$banned_ips = split(";",$banned_ips);
  	foreach($banned_ips as $banned_ip) {
		if($banned_ip == $ip) {
           tdomf_log_message("Banned ip $ip tried to submit a post!",TDOMF_LOG_ERROR);
			  return sprintf(__("Your IP %s does not currently have permissions to use this form. If this is an error please contact the <a href=\"mailto:%s\">admins</a>.","tdomf"),$ip,get_bloginfo('admin_email'));
		}
	 }
  }

  // Users who can access form
  //
  if(get_option(TDOMF_OPTION_ALLOW_EVERYONE) == false) {
  	if(!current_user_can("publish_posts")  && !current_user_can(TDOMF_CAPABILITY_CAN_SEE_FORM)) {
      tdomf_log_message("User with the incorrect privilages attempted to submit a post!",TDOMF_LOG_ERROR);
	  	return sprintf(__("You (%s) do not currently have permissions to use this form. If this is an error please contact the <a href=\"mailto:%s\">admins</a>.","tdomf"),$current_user->user_name,get_bloginfo('admin_email'));
  	}
  }

  return NULL;
}

// Generate a preview based on form arguments
//
function tdomf_preview_form($args) {
   global $tdomf_form_widgets_preview;
   $message = "";
   $widget_args = array_merge( array( "before_widget"=>"\n<p>\n",
                                      "after_widget"=>"\n</p>\n",
                                      "before_title"=>"<b>",
                                      "after_title"=>"</b><br/>" ),
                                      $args);
   $widget_order = tdomf_get_widget_order();
   foreach($widget_order as $w) {
	  if(isset($tdomf_form_widgets_preview[$w])) {
		#tdomf_log_message("Looking at preview widget $w");
		$message .= $tdomf_form_widgets_preview[$w]['cb']($widget_args);
	  }
   }
   if($message == "") {
      tdomf_log_message("Couldn't generate preview!",TDOMF_LOG_ERROR);
	  return __("Error! Could not generate a preview!","tdomf");
   }
   return "<div class=\"tdomf_form_preview\" id=\"tdomf_form1_preview\" name=\"tdomf_form1_preview\">".sprintf(__("This is a preview of your submission:%s\n","tdomf"),$message)."</div>";
}

// Validate input using widgets
//
function tdomf_validate_form($args) {
   global $tdomf_form_widgets_validate;
   $message = "";
   $widget_args = array_merge( array( "before_widget"=>"",
                                      "after_widget"=>"<br/>\n",
                                      "before_title"=>"<b>",
                                      "after_title"=>"</b><br/>"),
							   $args);
   $widget_order = tdomf_get_widget_order();
   foreach($widget_order as $w) {
	  if(isset($tdomf_form_widgets_validate[$w])) {
		$temp_message = $tdomf_form_widgets_validate[$w]['cb']($widget_args);
		if($temp_message != NULL && trim($temp_message) != ""){
		   $message .= $temp_message;
		}
	   }
   }
   // Oh dear! Something didn't validate!
   if(trim($message) != "") {
	  tdomf_log_message("Their submission didn't validate.");
	  return "<font color='red'>$message</font>\n";
   }
   return NULL;
}


// Creates a post using args
//
function tdomf_create_post($args) {
   global $wp_rewrite, $tdomf_form_widgets_post, $current_user;

   tdomf_log_message("Attempting to create a post based on submission");

   // Default submitter
   $user_id = get_option(TDOMF_DEFAULT_AUTHOR);
   if(is_user_logged_in()) {
      $user_id = $current_user->ID;
   }

   // Default category
   //
   $post_cats = array(get_option(TDOMF_DEFAULT_CATEGORY));

   // Default title (should this be an option?)
   //
   $def_title = tdomf_get_log_timestamp();

   // Build post and post it as draft
   //
   $post = array (
	   "post_content"   => "",
	   "post_excerpt"   => "",
	   "post_title"     => $def_title,
	   "post_category"  => $post_cats,
	   "post_author"    => $user_id,
	   "post_status"    => 'draft',
	   "post_name"      => "",
	   "post_date"      => "",
	   "comment_status" => "",
	   "ping_status"    => ""
   );
   $post_ID = wp_insert_post($post);

   tdomf_log_message("Post with id $post_ID (and default title $def_title) created as draft.");

   // Flag this post as TDOMF!
   add_post_meta($post_ID, TDOMF_KEY_FLAG, true, true);

   // Submitter info
   if($user_id != get_option(TDOMF_DEFAULT_AUTHOR)){
     tdomf_log_message("Logging default submitter info (user $user_id) for this post $post_ID");
     add_post_meta($post_ID, TDOMF_KEY_USER_ID, $user_id, true);
     add_post_meta($post_ID, TDOMF_KEY_USER_NAME, $user->user_login, true);
     update_usermeta($user_id, TDOMF_KEY_FLAG, true);
   }

   // IP info
   if(isset($args['ip'])){
        $ip = $args['ip'];
        tdomf_log_message("Logging default ip $ip for this post $post_ID");
        add_post_meta($post_ID, TDOMF_KEY_IP, $ip, true);
   }

   tdomf_log_message("Let the widgets do their work on newly created $post_ID");

   // Disable kses protection! It seems to get over-protective of non-registered
   // posts! If the post is going to be moderated, then we don't have an issue
   // as an admin will verify it... I think. Hope to god this is not a
   // security risk!
   if(get_option(TDOMF_OPTION_MODERATION)){
     kses_remove_filters();
   }
   
   // Widgets:post
   //
   $widget_args = array_merge( array( "post_ID"=>$post_ID ),
                               $args);
   $widget_order = tdomf_get_widget_order();
   foreach($widget_order as $w) {
	  if(isset($tdomf_form_widgets_post[$w])) {
		$tdomf_form_widgets_post[$w]['cb']($widget_args);
	  }
   }

   // Submitted post count!
   //
   $submitted_count = get_option(TDOMF_STAT_SUBMITTED);
   if($submitted_count == false) {
      $submitted_count = 0;
   }
   $submitted_count++;
   update_option(TDOMF_STAT_SUBMITTED,$submitted_count);
   tdomf_log_message("post $post_ID is number $submitted_count submission!");

   // publish (maybe)
   //
   $send_moderator_email = true;
   if(!get_option(TDOMF_OPTION_MODERATION)){
      tdomf_log_message("Moderation is disabled. Publishing $post_ID!");
      wp_publish_post($post_ID);
      $send_moderator_email = false;
   } else if($user_id != get_option(TDOMF_DEFAULT_AUTHOR)) {
        $testuser = new WP_User($user_id,$user->user_login);
        $user_status = get_usermeta($user_id,TDOMF_KEY_STATUS);
        if(current_user_can('publish_posts') || $user_status == TDOMF_USER_STATUS_TRUSTED) {
           tdomf_log_message("Publishing post $post_ID!");
           wp_publish_post($post_ID);
           $send_moderator_email = false;
        }
   }

   // Notify admins
   //
   if($send_moderator_email){
      tdomf_notify_admins($post_ID);
   }

   // Renable filters so we dont' break anything else!
   //
   if(get_option(TDOMF_OPTION_MODERATION) && current_user_can('unfiltered_html') == false){
     kses_init_filters();
   }
   
   return $post_ID;
}

// Create the form!
//
function tdomf_generate_form() {
  global $tdomf_form_widgets;

  // AJAX is currently not supported
  //
  $use_ajax = tdomf_widget_is_ajax_avaliable();

  $form = tdomf_check_permissions_form();
  if($form != NULL) {
    return $form;
  }
  
  // Okay, all checks pass! Now create form
  $form = "";
  
  if(!$use_ajax) {
     $post_args = array();
     if(isset($_SESSION['tdomf_form_post'])) {
    	$post_args = $_SESSION['tdomf_form_post'];
    	unset($_SESSION['tdomf_form_post']);
    	if(isset($post_args['tdomf_post_message'])) {
    	   $form = $post_args['tdomf_post_message'];
    	}
    	if(isset($post_args['tdomf_no_form'])) {
    	   return $form;
    	}
     }
  } else {
     wp_print_scripts( array( 'sack' ));
     $ajax_script = TDOMF_URLPATH.'tdomf-form-ajax.php';
  	 $form .= <<<EOT

  <script type="text/javascript">
  //<![CDATA[
  function tdomf_submit_post()
  {
      var mysack = new sack("$ajax_script" );

	    mysack.execute = 1;
	    mysack.method = 'POST';
        mysack.setVar( "action", "post" );

	    // How do I get values from dynamically chosen widgets into a value I can pass to the backend?

	    mysack.onError = function() { alert('AJAX Error' )};
	    mysack.runAJAX();

  	return true;
  }
  function tdomf_preview_post()
  {
     var mysack = new sack("$ajax_script" );
     mysack.execute = 1;
	 mysack.method = 'POST';

	 mysack.setVar( "action", "preview" );

	 // How do I get values from dynamically chosen widgets into a value I can pass to the backend?

     mysack.onError = function() { alert('AJAX Error' )};
     mysack.runAJAX();

     return true;
  }
  //]]>
  </script>
EOT;
  }

  // Ajax or POST setup
  //
  if($use_ajax) {
  	$form .= "<div id='tdomf_form1_msg_div'></div>\n<form>";
  } else {
    $form .= "<form method=\"post\" action=\"".TDOMF_URLPATH.'tdomf-form-post.php" id="tdomf_form1" name="tdomf_form1" class="tdomf_form">';
    $form .= "<input type='hidden' id='redirect' name='redirect' value='$_SERVER[REQUEST_URI]' />";
    $random_string = tdomf_random_string(100);
    $_SESSION["tdomf_key"] = $random_string;
    $form .= "<input type='hidden' id='tdomf_key' name='tdomf_key' value='$random_string' />";
  }

  // Process widgets
  //
  if(!$use_ajax) {
  	$widget_args = array_merge( array( "before_widget"=>"<fieldset>\n",
                                       "after_widget"=>"\n</fieldset>\n",
                                       "before_title"=>"<legend>",
                                       "after_title"=>"</legend>" ),
                                $post_args);
  } else {
  	$widget_args = array( "before_widget"=>"<fieldset>\n",
                          "after_widget"=>"\n</fieldset>\n",
                          "before_title"=>"<legend>",
                          "after_title"=>"</legend>" );
  }
  $widget_order = tdomf_get_widget_order();
  try {
	  foreach($widget_order as $w) {
		if(isset($tdomf_form_widgets[$w])) {
			$form .= $tdomf_form_widgets[$w]['cb']($widget_args);
			#$form .= "<br/>";
		}
	  }
  } catch (Exception $e) {
    tdomf_log_message("Error during form widgets $e->getMessage()",TDOMF_LOG_ERROR);
    $form .= "\n<font color='red'>".sprintf(__("Error during processing of widgets: %s","tdomf"),$e->getMessage())."</font><br/><br/>\n";
  }

  // Form buttons
  //
  $form .= '<table border="0" align="left"><tr>';
  //
  //TODO: Clear and/or Reset buttons
  //
  if($use_ajax) {
    if(tdomf_widget_is_preview_avaliable()) {
	    	$form .= '<td width="10px"><input type="button" value="'.__("Preview","tdomf").'" name="tdomf_form1_preview" id="tdomf_form1_preview" onclick="tdomf_preview_post(); return false;" /></td>';
    }
    $form .= '<td width="10px"><input type="button" value="'.__("Send","tdomf").'" name="tdomf_form1_send" id="tdomf_form1_send" onclick="tdomf_submit_post(); return false;" /></td>';
  } else {
  	if(tdomf_widget_is_preview_avaliable()) {
    	$form .= '<td width="10px"><input type="submit" value="'.__("Preview","tdomf").'" name="tdomf_form1_preview" id="tdomf_form1_preview" /></td>';
    }
  	$form .= '<td width="10px"><input type="submit" value="'.__("Post","tdomf").'" name="tdomf_form1_send" id="tdomf_form1_send" /></td>';
  }


  $form .= '</tr></table>';
  $form .= "\n</form>\n";
  
  return $form;
}

// Replaces <!--tdomf_form1--> or [tdomf_form1] with actual form
//
function tdomf_form_filter($content=''){
   if ('' == $content ||
       (preg_match('|<!--tdomf_form1-->|', $content) <= 0 && preg_match('|\[tdomf_form1\]|', $content) <= 0)) {
   	return $content;
   }
   #$the_form = tdomf_generate_form();
   $content = preg_replace('|<!--tdomf_form1-->|', '[tdomf_form1]', $content);
   // make sure to swallow paragraph markers as well so the form is valid xhtml
   $content = preg_replace('|(<p>)*(\n)*\[tdomf_form1\](\n)*(</p>)*|', tdomf_generate_form(), $content);
   return $content;
}
add_filter('the_content', 'tdomf_form_filter');



?>
