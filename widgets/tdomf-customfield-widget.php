<?php
/*
Name: "Custom Fields"
URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: Add a custom field to your form!
Version: 0.1
Author: Mark Cunningham
Author URI: http://thedeadone.net
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

#function tdomf_widget_customfields_number_top(){  
  if ( $_POST['customfields-number-submit'] ) {
    $count = $_POST['customfields-number'];
    if($count > 0){ update_option('tdomf_customfields_widget_count',$count); }
  }
#}
#add_action('tdomf_widget_page_top','tdomf_widget_customfields_number_top');

function tdomf_widget_customfields_number_bottom(){
  $count = get_option('tdomf_customfields_widget_count');
  if($count <= 0){ $count = 1; } 
  
  ?>
  <div class="wrap">
    <form method="post">
      <h2><?php _e("Custom Fields Widgets","tdomf"); ?></h2>
      <p style="line-height: 30px;"><?php _e("How many Custom Fields widgets would you like?","tdomf"); ?>
      <select id="customfields-number" name="customfields-number" value="<?php echo $count; ?>">
      <?php for($i = 1; $i < 10; $i++) { ?>
        <option value="<?php echo $i; ?>" <?php if($i == $count) { ?> selected="selected" <?php } ?>><?php echo $i; ?></option>
      <?php } ?>
      </select>
      <span class="submit">
        <input type="submit" value="Save" id="customfields-number-submit" name="customfields-number-submit" />
      </span>
      </p>
    </form>
  </div>
  <?php 
}
add_action('tdomf_widget_page_bottom','tdomf_widget_customfields_number_bottom');

// Get Options for this widget
//
function tdomf_widget_customfields_get_options($index) {
  $options = get_option('tdomf_customfields_widget_'.$index);
    if($options == false) {
       $options = array();
       $options['customfields'] = "";
       $options['title'] = "";
    }
  return $options;
}

//////////////////////////////
// Display the widget! 
//
function tdomf_widget_customfields($args,$params) {
  $number = 0;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }

  extract($args);
  $options = tdomf_widget_customfields_get_options($number);
  
  $output  = $before_widget;
  if($options['title'] != "") {
    $output .= $before_title;
    $output .= $options['title'];
    $output .= $after_title;
  }
  $output .= $options['customfields'];
  $output .= $after_widget;
  return $output;
}

///////////////////////////////////////////////////
// Display and handle content widget control panel 
//
function tdomf_widget_customfields_control($params) {
  $number = 0;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  
  $options = tdomf_widget_customfields_get_options($number);
  // Store settings for this widget
  if ( $_POST["customfields-$number-submit"] ) {
     $newoptions['customfields'] = $_POST["customfields-customfields-$number"];
     $newoptions['title'] = $_POST["customfields-title-$number"];
     if ( $options != $newoptions ) {
        $options = $newoptions;
        update_option('tdomf_customfields_widget_'.$number, $options);
        
     }
  }
// Display control panel for this widget
  
  extract($options);

        ?>
<div>

<label for="customfields-title-<?php echo $number; ?>">
<?php _e("Title:","tdomf"); ?><br/>
<input type="textfield" size="40" id="customfields-title-<?php echo $number; ?>" name="customfields-title-<?php echo $number; ?>" value="<?php echo $options['title']; ?>" />
</label>

<br/><br/>

<label for="customfields-name-<?php echo $number; ?>">
<?php _e("Custom Field Key:","tdomf"); ?><br/>
<input type="textfield" size="40" id="customfields-key-<?php echo $number; ?>" name="customfields-key-<?php echo $number; ?>" value="<?php echo $options['title']; ?>" />
</label>

# Required
# Insert Value to Post Content
# Insert Key and Value to Post Content

# TextField
# Size
# Default Value
# is URL (insert as hyperlink, page link, display as domainname)
# is EMAIL (insert as mailto)

</div>
        <?php 
}
    
function tdomf_widget_customfields_init(){
  $count = get_option('tdomf_customfields_widget_count');
  if($count <= 0){ $count = 1; } 
  for($i = 1; $i <= $count; $i++) {
    tdomf_register_form_widget("customfields-$i","Custom Fields $i", 'tdomf_widget_customfields',$i);
    tdomf_register_form_widget_control("customfields-$i", "Custom Fields $i",'tdomf_widget_customfields_control', 400, 300, $i);
  }
}
tdomf_widget_customfields_init();

?>
