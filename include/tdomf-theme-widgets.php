<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

/////////////////////////////
// Widgets for your Theme! //
/////////////////////////////

function tdomf_get_latest_submissions_post_list_line($p) {
  $submitter = get_post_meta($p->ID, TDOMF_KEY_NAME, true);
    if($submitter == false || empty($submitter)) {
      return "<li>".sprintf(__("<a href=\"%s\">\"%s\"</a>","tdomf"),get_permalink($p->ID),$p->post_title)."</li>";
    } 
    return "<li>".sprintf(__("<a href=\"%s\">\"%s\"</a> submitted by %s","tdomf"),get_permalink($p->ID),$p->post_title,$submitter)."</li>";
}

function tdomf_theme_widgets_init() {
  if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
    return;
  
  function tdomf_theme_widget_form($args,$params) {
    extract($args);
    $form_id = $params;
    if(!tdomf_form_exists($form_id)) {
       $form_id = tdomf_get_first_form_id();
    }
    echo $before_widget;
    echo $before_title;
    echo tdomf_get_option_form(TDOMF_OPTION_NAME,$form_id);
    echo $after_title;
    tdomf_the_form($form_id);
    echo "<br/><br/>\n";
    echo $after_widget;
  }
  $form_ids = tdomf_get_form_ids();
  foreach($form_ids as $form_id) {
    register_sidebar_widget("TDOMF Form " . $form_id->form_id, 'tdomf_theme_widget_form', $form_id->form_id);
  }
  
  function tdomf_theme_widget_admin($args) {
    if(current_user_can('manage_options') || current_user_can('edit_others_posts')) {
      extract($args);

      $errors = tdomf_get_error_messages();
      if(trim($errors) != "") {
        echo $before_widget;
        echo $before_title.__("TDOMF Errors","tdomf").$after_title;
        echo "<p>$errors</p>";
        echo $after_widget;
      }

      $options = get_option('tdomf_theme_widget_admin');
      if($options == false) {
        $log = 5;
        $mod = 5;
      } else {
        $log = $options['log'];
        $mod = $options['mod'];
      }

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
            echo tdomf_get_post_list_line($p);
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
      if($mod <= 0 && tdomf_is_moderation_in_use()) {
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
    $options = get_option('tdomf_theme_widget');
    if($options == false) {
      $title = 'Recent Submissions';
      $mod = 5;
    } else {
      $title = $options['title'];
      $mod = $options['mod'];
    }
    
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
         #echo "<li><a href=\"".get_permalink($p->ID)."\">".$p->post_title."</a> from ".get_post_meta($p->ID, TDOMF_KEY_NAME, true)."</li>";
         echo tdomf_get_latest_submissions_post_list_line($p);
      }
      echo "</ul>";
      echo $after_widget;
    }
  }
  
  function tdomf_theme_widget_control() {
    $options = get_option('tdomf_theme_widget');
  
    if ( $_POST['tdomf-mod'] ) {
      $newoptions['title'] = htmlentities(strip_tags($_POST['tdomf-title']));
      $newoptions['mod'] = intval($_POST['tdomf-mod']);
        if ( $options != $newoptions ) {
          $options = $newoptions;
          update_option('tdomf_theme_widget', $options);
        }
    }
    
    if($options == false) {
      $title = 'Recent Submissions';
      $mod = 5;
    } else {
      $title = $options['title'];
      $mod = $options['mod'];
    }
  
  ?>
  <div>
  
  <label for="tdomf-title">
  Title
  <input type="text" id="tdomf-title" name="tdomf-title" value="<?php echo htmlentities($title,ENT_QUOTES); ?>" size="20" />
  </label>
  <br/><br/>
  <label for="tdomf-mod">
  Number of posts to show:
  <input type="text" id="tdomf-mod" name="tdomf-mod" value="<?php echo htmlentities($mod,ENT_QUOTES); ?>" size="2" />
  </label>
  
  </div>
  <?php
  }
  
  function tdomf_theme_widget_admin_control() {
    $options = get_option('tdomf_theme_widget_admin');
  
    if ( $_POST['tdomf-admin-info-log'] ) {
      $newoptions['log'] = intval($_POST['tdomf-admin-info-log']);
      $newoptions['mod'] = intval($_POST['tdomf-admin-info-mod']);
        if ( $options != $newoptions ) {
          $options = $newoptions;
          update_option('tdomf_theme_widget_admin', $options);
        }
    }
    
    if($options == false) {
      $log = 5;
      $mod = 5;
    } else {
      $log = $options['log'];
      $mod = $options['mod'];
    }  
  ?>
  <div>
  
  <label for="tdomf-admin-info-log">
  Number of log lines to show:
  <input type="text" id="tdomf-admin-info-log" name="tdomf-admin-info-log" value="<?php echo htmlentities($log,ENT_QUOTES); ?>" size="2" />
  </label>
  <br/><br/>
  <label for="tdomf-admin-info-mod">
  Number of posts to show:
  <input type="text" id="tdomf-admin-info-mod" name="tdomf-admin-info-mod" value="<?php echo htmlentities($mod,ENT_QUOTES); ?>" size="2" />
  </label>
  
  </div>
  <?php
  }

  register_sidebar_widget('TDOMF Admin Info', 'tdomf_theme_widget_admin');
  register_widget_control('TDOMF Admin Info', 'tdomf_theme_widget_admin_control', 220, 100);
  register_sidebar_widget('TDOMF Recent Submissions', 'tdomf_theme_widget');
  register_widget_control('TDOMF Recent Submissions', 'tdomf_theme_widget_control', 220, 100);
}
add_action('plugins_loaded', 'tdomf_theme_widgets_init');
?>