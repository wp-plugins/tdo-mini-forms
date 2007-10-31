<?php
/*
Name: "Text"
URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: Insert some text
Version: 1
Author: Mark Cunningham
Author URI: http://thedeadone.net
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

#function tdomf_widget_text_number_top(){
  if ( $_POST['text-number-submit'] ) {
    $count = $_POST['text-number'];
    if($count > 0){ update_option('tdomf_text_widget_count',$count); }
  }
#}
#add_action('tdomf_widget_page_top','tdomf_widget_text_number_top');

function tdomf_widget_text_number_bottom(){
  $count = get_option('tdomf_text_widget_count');
  if($count <= 0){ $count = 1; } 
  
  ?>
  <div class="wrap">
    <form method="post">
      <h2><?php _e("Text Widgets","tdomf"); ?></h2>
      <p style="line-height: 30px;"><?php _e("How many text widgets would you like?","tdomf"); ?>
      <select id="text-number" name="text-number" value="<?php echo $count; ?>">
      <?php for($i = 1; $i < 10; $i++) { ?>
        <option value="<?php echo $i; ?>" <?php if($i == $count) { ?> selected="selected" <?php } ?>><?php echo $i; ?></option>
      <?php } ?>
      </select>
      <span class="submit">
        <input type="submit" value="Save" id="text-number-submit" name="text-number-submit" />
      </span>
      </p>
    </form>
  </div>
  <?php 
}
add_action('tdomf_widget_page_bottom','tdomf_widget_text_number_bottom');

// Get Options for this widget
//
function tdomf_widget_text_get_options($index) {
  $options = get_option('tdomf_text_widget_'.$index);
    if($options == false) {
       $options = array();
       $options['text'] = "";
       $options['title'] = "";
    }
  return $options;
}

//////////////////////////////
// Display the widget! 
//
function tdomf_widget_text($args,$params) {
  $number = 0;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }

  extract($args);
  $options = tdomf_widget_text_get_options($number);
  
  $output  = $before_widget;
  if($options['title'] != "") {
    $output .= $before_title;
    $output .= $options['title'];
    $output .= $after_title;
  }
  $output .= $options['text'];
  $output .= $after_widget;
  return $output;
}

///////////////////////////////////////////////////
// Display and handle content widget control panel 
//
function tdomf_widget_text_control($params) {
  $number = 0;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  
  $options = tdomf_widget_text_get_options($number);
  // Store settings for this widget
  if ( $_POST["text-$number-submit"] ) {
     $newoptions['text'] = $_POST["text-text-$number"];
     $newoptions['title'] = $_POST["text-title-$number"];
     if ( $options != $newoptions ) {
        $options = $newoptions;
        update_option('tdomf_text_widget_'.$number, $options);
        
     }
  }
// Display control panel for this widget
  
  extract($options);

        ?>
<div>

<i><?php _e("HTML is permissible in messages.","tdomf"); ?></i>

<br/><br/>

<label for="text-title-<?php echo $number; ?>">
<?php _e("Title:","tdomf"); ?><br/>
<input type="textfield" size="40" id="text-title-<?php echo $number; ?>" name="text-title-<?php echo $number; ?>" value="<?php echo $options['title']; ?>" />
</label>

<br/><br/>

<label for="text-text-<?php echo $number; ?>" ><?php _e("Text:","tdomf"); ?><br/>
<textarea cols="40" rows="4" id="text-text-<?php echo $number; ?>" name="text-text-<?php echo $number; ?>" ><?php echo $options['text']; ?></textarea>
</label>

</div>
        <?php 
}
    
function tdomf_widget_text_init(){
  $count = get_option('tdomf_text_widget_count');
  if($count <= 0){ $count = 1; } 
  for($i = 1; $i <= $count; $i++) {
    tdomf_register_form_widget("text-$i","Text $i", 'tdomf_widget_text',$i);
    tdomf_register_form_widget_control("text-$i", "Text $i",'tdomf_widget_text_control', 400, 300, $i);
  }
}
tdomf_widget_text_init();

?>
