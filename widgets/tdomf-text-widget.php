<?php
/*
Name: "Text"
URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: Insert some text
Version: 3
Author: Mark Cunningham
Author URI: http://thedeadone.net
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

// Add a menu option to control the number of text widgets to the bottom of the 
// tdomf widget page
//
function tdomf_widget_text_number_bottom(){
  $form_id = tdomf_edit_form_form_id();
  $count = tdomf_get_option_widget('tdomf_text_widget_count',$form_id);
  if($count <= 0){ $count = 1; }
  $max = tdomf_get_option_form(TDOMF_OPTION_WIDGET_INSTANCES,$form_id);
  if($max == false){ $max = 9; }
  if($count > ($max+1)){ $count = ($max+1); }
  
  if($max > 1) {
  ?>
  <div class="wrap">
    <form method="post">
      <h2><?php _e("Text Widgets","tdomf"); ?></h2>
      <p style="line-height: 30px;"><?php _e("How many text widgets would you like?","tdomf"); ?>
      <select id="tdomf-widget-text-number" name="tdomf-widget-text-number" value="<?php echo $count; ?>">
      <?php for($i = 1; $i < ($max+1); $i++) { ?>
        <option value="<?php echo $i; ?>" <?php if($i == $count) { ?> selected="selected" <?php } ?>><?php echo $i; ?></option>
      <?php } ?>
      </select>
      <span class="submit">
        <input type="submit" value="<?php _e("Save","tdomf"); ?>" id="tdomf-widget-text-number-submit" name="tdomf-widget-text-number-submit" />
      </span>
      </p>
    </form>
  </div>
  <?php 
  }
}
add_action('tdomf_widget_page_bottom','tdomf_widget_text_number_bottom');

// Get Options for this widget
//
function tdomf_widget_text_get_options($index,$form_id) {
  $options = tdomf_get_option_widget('tdomf_text_widget_'.$index,$form_id);
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
  $options = tdomf_widget_text_get_options($number,$tdomf_form_id);
  
  $output  = $before_widget;
  if($options['title'] != "") {
    $output .= $before_title;
    $output .= $options['title'];
    $output .= $after_title;
  }
  
  if(strpos($mode,'-hack') !== false) {
    $output .= "\t\t";
    $output .= $options['text'];
  } else {
    $output .= tdomf_prepare_string($options['text'], $tdomf_form_id, $mode, false, "", $args);
  }
  $output .= $after_widget;
  return $output;
}

///////////////////////////////////////////////////
// Display and handle content widget control panel 
//
function tdomf_widget_text_control($form_id,$params) {
  $number = 0;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  
  $options = tdomf_widget_text_get_options($number,$form_id);
  // Store settings for this widget
  if ( $_POST["text-$number-submit"] ) {
     $newoptions['text'] = $_POST["text-text-$number"];
     $newoptions['title'] = $_POST["text-title-$number"];
     if ( $options != $newoptions ) {
        $options = $newoptions;
        tdomf_set_option_widget('tdomf_text_widget_'.$number, $options,$form_id);
        
     }
  }
// Display control panel for this widget
  
  extract($options);

        ?>
<div>

<i><?php _e("HTML is permissible. You can also now use MACROs (see list) from the Form Hacker in the output which also means you can use PHP code.","tdomf"); ?></i>

<br/><br/>

<ul>
<li><?php printf(__("<code>%s</code> - User name of the currently logged in user","tdomf"),TDOMF_MACRO_USERNAME); ?>
<li><?php printf(__("<code>%s</code> - IP of the current visitor","tdomf"),TDOMF_MACRO_IP); ?>
<li><?php printf(__("<code>%s</code> - The ID of the current form (which is currently %d)","tdomf"),TDOMF_MACRO_FORMID,$form_id); ?>
<li><?php printf(__("<code>%s</code> - Name of the Form (set in options)","tdomf"),TDOMF_MACRO_FORMNAME); ?>
<li><?php printf(__("<code>%s</code> - Form Description (set in options)","tdomf"),TDOMF_MACRO_FORMDESCRIPTION); ?>
</ul>
             
             <br/><br/>

<label for="text-title-<?php echo $number; ?>">
<?php _e("Title:","tdomf"); ?><br/>
<input type="text" size="60" id="text-title-<?php echo $number; ?>" name="text-title-<?php echo $number; ?>" value="<?php echo htmlentities($options['title'],ENT_QUOTES,get_bloginfo('charset')); ?>" />
</label>

<br/><br/>

<label for="text-text-<?php echo $number; ?>" ><?php _e("Text:","tdomf"); ?><br/>
<textarea cols="50" rows="6" id="text-text-<?php echo $number; ?>" name="text-text-<?php echo $number; ?>" ><?php echo htmlentities($options['text'],ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
</label>

</div>
        <?php 
}
    

function tdomf_widget_text_handle_number($form_id) {
  if(tdomf_form_exists($form_id)) {   
      if ( isset($_POST['tdomf-widget-text-number-submit']) ) {
        $count = $_POST['tdomf-widget-text-number'];
        if($count > 0){ tdomf_set_option_widget('tdomf_text_widget_count',$count,$form_id); }
      }
  }
}
#add_action('tdomf_widget_page_top','tdomf_widget_text_handle_number');
add_action('tdomf_control_form_start','tdomf_widget_text_handle_number',10,1);

function tdomf_widget_text_init($form_id){
  if(tdomf_form_exists($form_id)) {     
     $count = tdomf_get_option_widget('tdomf_text_widget_count',$form_id);
     if($count <= 0){ $count = 1; } 
     
     $max = tdomf_get_option_form(TDOMF_OPTION_WIDGET_INSTANCES,$form_id);
     if($max <= 1){ $count = 1; }
     else if($count > ($max+1)){ $count = $max + 1; }
     
     for($i = 1; $i <= $count; $i++) {
       tdomf_register_form_widget("text-$i","Text $i", 'tdomf_widget_text', array(), $i);
       tdomf_register_form_widget_hack("text-$i","Text $i", 'tdomf_widget_text', array(), $i);
       tdomf_register_form_widget_control("text-$i", "Text $i",'tdomf_widget_text_control', 500, 520, array(), $i);
     }
  }
}
add_action('tdomf_generate_form_start','tdomf_widget_text_init');
add_action('tdomf_control_form_start','tdomf_widget_text_init');
#add_action('tdomf_widget_page_top','tdomf_widget_text_init');
add_action('tdomf_control_form_start','tdomf_widget_text_init',10,1);


?>
