<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

/////////////////////////////
// Widgets for your Theme! //
/////////////////////////////

function tdomf_theme_widget_admin_init() {
  if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
    return;
  
  function tdomf_theme_widget_admin($args) {
    if(current_user_can('manage_options') || current_user_can('edit_others_posts')) {
      extract($args);
      $options = get_option('tdomf_theme_widget_admin');
      $log = empty($options['log']) ? 5 : $options['log'];
      $mod = empty($options['mod']) ? 5 : $options['mod'];

      if($log > 0) {
        echo $before_widget;
        echo $before_title;
        _e('TDOMF Log', 'tdomf'); 
        if(current_user_can('manage_options')) { 
          echo "<a href=\"".get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_log_menu\" title=\"Full Log...\">&raquo;</a>";
        }
        echo $after_title;
        echo '<p>'.tdomf_get_log($log).'</p>';
        echo $after_widget;
      }
      
      if($mod > 0) {
        $posts = tdomf_get_unmoderated_posts(0,$mod);
        if(!empty($posts)) {
          echo $before_widget;
          echo $before_title;
          printf(__('TDOMF Moderation Queue (%d)', 'tdomf'),tdomf_get_unmoderated_posts_count());
          if(current_user_can('edit_others_posts')) { 
            echo "<a href=\"".get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_mod_posts_menu&f=0\" title=\"Moderate Submissions...\">&raquo;</a>";
          }
          echo $after_title;
          echo '<ul>';
          foreach($posts as $p) { 
            echo "<li>".$p->post_title." from ".get_post_meta($p->ID, TDOMF_KEY_NAME, true)." </li>";
          }
          echo '</ul>';
          echo $after_widget;
        }
      }
      
      echo $before_widget;
      echo $before_title;
      _e('TDOMF Admin Links', 'tdomf'); 
      echo $after_title;
      echo "<ul>";
      if($mod <= 0 && get_option(TDOMF_OPTION_MODERATION)) {
        echo "<li>";
        printf(__("<a href=\"%s\">Moderate (%d)</a>","tdomf"),get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_mod_posts_menu&f=0",tdomf_get_unmoderated_posts_count());
        echo "</li>";
      }
      echo "<li>";
      printf(__("<a href=\"%s\">Configure</a>","tdomf"),get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_options_menu");
      echo "</li>";
      echo "<li>";
      printf(__("<a href=\"%s\">Manage</a>","tdomf"),get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_manage_menu");
      echo "</li>";
      echo "<li>";
      printf(__("<a href=\"%s\">Create Form</a>","tdomf"),get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_form_menu");
      echo "</li>";
      if($log <= 0) {
        echo "<li>";
        printf(__("<a href=\"%s\">Log</a>","tdomf"),get_bloginfo('wpurl')."/wp-admin/admin.php?page=tdomf_show_log_menu");
        echo "</li>";
      }
      echo "</ul>";
      echo $after_widget;
    }
  }

  function tdomf_theme_widget($args) {
    extract($args);
    $options = get_option('tdomf_theme_widget_admin');
    $title = empty($options['title']) ? 'Recent Submissions' : $options['title'];
    $mod = empty($options['mod']) ? 5 : $options['mod'];
    
    $posts = tdomf_get_published_posts(0,$mod);
    if(!empty($posts)) {
      echo $before_widget;
      if($title) {
        echo $before_title;
        echo $title;
        echo $after_title;
      }
      echo "<ul>";
      foreach($posts as $p) { 
         echo "<li><a href=\"".get_permalink($p->ID)."\">".$p->post_title."</a> from ".get_post_meta($p->ID, TDOMF_KEY_NAME, true)."</li>";
      }
      echo "</ul>";
      echo $after_widget;
    }
  }
  
	/*function tdomf_theme_widget_admin_control() {

		// Collect our widget's options.
		$options = get_option('tdomf_theme_widget_admin');

		// This is for handing the control form submission.
		if ( $_POST['mywidget-submit'] ) {
			// Clean up control form submission options
			$newoptions['title'] = strip_tags(stripslashes($_POST['mywidget-title']));
			$newoptions['text'] = strip_tags(stripslashes($_POST['mywidget-text']));
		}

		// If original widget options do not match control form
		// submission options, update them.
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('tdomf_theme_widget_admin', $options);
		}

		// Format options as valid HTML. Hey, why not.
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$text = htmlspecialchars($options['text'], ENT_QUOTES);

// The HTML below is the control form for editing options.
?>
		<div>
		<label for="mywidget-title" style="line-height:35px;display:block;">Widget title: <input type="text" id="mywidget-title" name="mywidget-title" value="<?php echo $title; ?>" /></label>
		<label for="mywidget-text" style="line-height:35px;display:block;">Widget text: <input type="text" id="mywidget-text" name="mywidget-text" value="<?php echo $text; ?>" /></label>
		<input type="hidden" name="mywidget-submit" id="mywidget-submit" value="1" />
		</div>
	<?php
	// end of tdomf_theme_widget_admin_control()
	}*/

  register_sidebar_widget('TDOMF Admin Info', 'tdomf_theme_widget_admin');
  register_sidebar_widget('TDOMF Recent Submissions', 'tdomf_theme_widget');
  #register_widget_control('TDOMF Admin Widget', 'tdomf_theme_widget_admin_control');
}
add_action('plugins_loaded', 'tdomf_theme_widget_admin_init');
?>