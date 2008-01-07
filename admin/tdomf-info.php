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
      </table>
      
  </div>

 <?php } ?>

