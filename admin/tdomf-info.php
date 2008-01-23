<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

///////////////////////
// TDOMF Debug Info  //
///////////////////////

 ?>

 <?php if(current_user_can('manage_options')) { ?>
 
   <div class="wrap">
  
      <h2><?php _e('TDOMF Debug', 'tdomf') ?></h2>
  
      <table border="0">
      <?php $alloptions = wp_load_alloptions();
            foreach($alloptions as $id => $val) {
              if($id == TDOMF_LOG) { ?>
                <tr>
                   <td><?php echo $id; ?></td>
                   <td><a href="admin.php?page=tdomf_show_log_menu"><?php _e("View Log","tdomf"); ?></td>
                </tr>
              <?php } else if(preg_match('#^tdomf_.+#',$id)) { ?>
                <tr>
                   <td><?php echo $id; ?></td>
                   <td><?php echo htmlentities(strval($val)); ?></td>
                </tr>
              <?php }
            } ?>
      <?php $form_ids = tdomf_get_form_ids();
      foreach($form_ids as $form_id) {
        $name = tdomf_get_option_form(TDOMF_OPTION_NAME,$form_id->form_id); ?>
        <tr><td colspan="2"><b><center>Form <?php echo $form_id->form_id ?></center></b></td></tr>
        <tr>
          <td>Name</td>
          <td><?php echo $name; ?></td>
          </tr>
        <?php $options = tdomf_get_options_form($form_id->form_id);
        foreach($options as $option => $value) { ?>
          <tr>
          <td><?php echo $option; ?></td>
          <td><?php var_dump($value); ?></td>
          </tr>
        <?php } 
        $widgets = tdomf_get_widgets_form($form_id->form_id);
      if(!empty($widgets)) { ?>
        <tr><td colspan="2"><center>Widgets for Form <?php echo $form_id->form_id ?></center></td></tr>
      <?php foreach($widgets as $widget) { ?>
        <tr>
          <td><?php echo $widget->widget_key; ?></td>
          <td><?php echo htmlentities($widget->widget_value); ?></td>
          </tr>
      <?php } }
      } ?>
      </table>
      
  </div>

 <?php } ?>

