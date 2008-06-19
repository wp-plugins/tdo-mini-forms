<?php
/*
Name: "Append to Content"
URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: Add to post content
Version: 1
Author: Mark Cunningham
Author URI: http://thedeadone.net
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

// Add a menu option to control the number of text widgets to the bottom of the 
// tdomf widget page
//
function tdomf_widget_append_number_bottom(){
  $form_id = tdomf_edit_form_form_id();
  $count = tdomf_get_option_widget('tdomf_append_widget_count',$form_id);
  if($count <= 0){ $count = 1; }
  $max = tdomf_get_option_form(TDOMF_OPTION_WIDGET_INSTANCES,$form_id);
  if($max == false){ $max = 9; }
  if($count > ($max+1)){ $count = ($max+1); }
  
  if($max > 1) {
  ?>
  <div class="wrap">
    <form method="post">
      <h2><?php _e("Append Widgets","tdomf"); ?></h2>
      <p style="line-height: 30px;"><?php _e("How many Append widgets would you like?","tdomf"); ?>
      <select id="tdomf-widget-append-number" name="tdomf-widget-append-number" value="<?php echo $count; ?>">
      <?php for($i = 1; $i < ($max+1); $i++) { ?>
        <option value="<?php echo $i; ?>" <?php if($i == $count) { ?> selected="selected" <?php } ?>><?php echo $i; ?></option>
      <?php } ?>
      </select>
      <span class="submit">
        <input type="submit" value="<?php _e("Save","tdomf"); ?>" id="tdomf-widget-append-number-submit" name="tdomf-widget-append-number-submit" />
      </span>
      </p>
    </form>
  </div>
  <?php 
  }
}
add_action('tdomf_widget_page_bottom','tdomf_widget_append_number_bottom');

// Get Options for this widget
//
function tdomf_widget_append_get_options($index,$form_id) {
  $options = tdomf_get_option_widget('tdomf_append_widget_'.$index,$form_id);
    if($options == false) {
       $options = array();
       $options['message'] = "";
    }
  return $options;
}

function tdomf_widget_append($args,$params) {
   # do nothing
   return "";
}

function tdomf_widget_append_control($form_id,$params) {
  $number = 0;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  
  $options = tdomf_widget_append_get_options($number,$form_id);
  // Store settings for this widget
  if ( $_POST["append-$number-submit"] ) {
     $newoptions['message'] = $_POST["append-message-$number"];
     if ( $options != $newoptions ) {
        $options = $newoptions;
        tdomf_set_option_widget('tdomf_append_widget_'.$number, $options,$form_id);
        
     }
  }
// Display control panel for this widget
  
  extract($options);

        ?>
<div>

<p><?php _e("This Widget allows you to add text to the created post. Widgets are processed top-down so this widget can be used to add seperators between other widget contexts. It uses the Form Hacker backend so supports all Form Hacker macros. It also supports PHP code - which can be used to create powerful post-submission processing. However, this widget will be ran <i>before</i> the submission is automatically published.","tdomf"); ?></p>

<br/><br/>

<label for="append-message-<?php echo $number; ?>" ><?php _e("Message to append to post content:","tdomf"); ?><br/>
<textarea cols="50" rows="6" id="append-message-<?php echo $number; ?>" name="append-message-<?php echo $number; ?>" ><?php echo htmlentities($options['message'],ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
</label>

</div>
        <?php 
}
    
function tdomf_widget_append_handle_number($form_id) {
  if(tdomf_form_exists($form_id)) {   
      if ( isset($_POST['tdomf-widget-append-number-submit']) ) {
        $count = $_POST['tdomf-widget-append-number'];
        if($count > 0){ tdomf_set_option_widget('tdomf_append_widget_count',$count,$form_id); }
      }
  }
}
#add_action('tdomf_widget_page_top','tdomf_widget_append_handle_number');
add_action('tdomf_control_form_start','tdomf_widget_append_handle_number');

function tdomf_widget_append_init($form_id){
  if(tdomf_form_exists($form_id)) {     
     $count = tdomf_get_option_widget('tdomf_append_widget_count',$form_id);
     if($count <= 0){ $count = 1; } 
     
     $max = tdomf_get_option_form(TDOMF_OPTION_WIDGET_INSTANCES,$form_id);
     if($max <= 1){ $count = 1; }
     else if($count > ($max+1)){ $count = $max + 1; }
     
     for($i = 1; $i <= $count; $i++) {
       tdomf_register_form_widget("append-$i",sprintf(__("Append to Post Content %s","tdomf"),$i), 'tdomf_widget_append', array(), $i);
       tdomf_register_form_widget_control("append-$i", sprintf(__("Append to Post Content %s","tdomf"),$i),'tdomf_widget_append_control', 500, 520, array(), $i);
       tdomf_register_form_widget_post("append-$i",sprintf(__("Append to Post Content %s","tdomf"),$i), 'tdomf_widget_append_post', array(), $i);
     }
  }
}
add_action('tdomf_generate_form_start','tdomf_widget_append_init');
add_action('tdomf_control_form_start','tdomf_widget_append_init');
add_action('tdomf_widget_page_top','tdomf_widget_append_init');
add_action('tdomf_create_post_start','tdomf_widget_append_init');


function tdomf_widget_append_post($args,$params) {
  $number = 0;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  extract($args);
  $options = tdomf_widget_append_get_options($number,$tdomf_form_id);
    
  $message = tdomf_prepare_string($options['message'], $tdomf_form_id, $mode, $post_ID, "", $args);  
  $post = wp_get_single_post($post_ID, ARRAY_A);
  if(!empty($post['post_content'])) {
     $post = add_magic_quotes($post);
  }

  $postdata = array (
      "ID"                      => $post_ID,
      "post_content"            => $post['post_content'].$message,
    );
    sanitize_post($postdata,"db");
    wp_update_post($postdata);

  return NULL;
}

?>
