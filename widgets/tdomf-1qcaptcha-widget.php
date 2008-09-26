<?php
/*
Name: "1 Question Captcha"
URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: The user must answer a simple question before a post is submitted
Version: 5
Author: Mark Cunningham
Author URI: http://thedeadone.net
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

/////////////////////////////////////
// How many captcha's do you want?
//
function tdomf_widget_1qcaptcha_number_bottom(){
  $form_id = tdomf_edit_form_form_id();
  $count = tdomf_get_option_widget('tdomf_1qcaptcha_widget_count',$form_id);
  $max = tdomf_get_option_form(TDOMF_OPTION_WIDGET_INSTANCES,$form_id);
  if($max == false){ $max = 9; }
  if($count <= 0){ $count = 1; }
  if($count > ($max+1)){ $count = ($max+1); }
  
  if($max > 1) {
  ?>
  <div class="wrap">
    <form method="post">
      <h2><?php _e("1 Question Captcha Widgets","tdomf"); ?></h2>
      <p style="line-height: 30px;"><?php _e("How many 1 Question Captcha widgets would you like?","tdomf"); ?>
      <select id="tdomf-widget-1qcaptcha-number" name="tdomf-widget-1qcaptcha-number" value="<?php echo $count; ?>">
      <?php for($i = 1; $i < ($max+1); $i++) { ?>
        <option value="<?php echo $i; ?>" <?php if($i == $count) { ?> selected="selected" <?php } ?>><?php echo $i; ?></option>
      <?php } ?>
      </select>
      <span class="submit">
        <input type="submit" value="<?php _e("Save","tdomf"); ?>" id="tdomf-widget-1qcaptcha-number-submit" name="tdomf-widget-1qcaptcha-number-submit" />
      </span>
      </p>
    </form>
  </div>
  <?php 
  }
}
add_action('tdomf_widget_page_bottom','tdomf_widget_1qcaptcha_number_bottom');

/////////////////////////////////
// Initilise multiple captchas!
//
function tdomf_widget_1qcaptcha_init($form_id){
  if(tdomf_form_exists($form_id)) {
    $count = tdomf_get_option_widget('tdomf_1qcaptcha_widget_count',$form_id);
    $max = tdomf_get_option_form(TDOMF_OPTION_WIDGET_INSTANCES,$form_id);
    if($max <= 1){ $count = 1; }
    else if($count > ($max+1)){ $count = $max + 1; }
  
    tdomf_register_form_widget("1qcaptcha","1 Question Captcha 1", 'tdomf_widget_1qcaptcha', array(), 1);
    tdomf_register_form_widget_control("1qcaptcha", "1 Question Captcha 1",'tdomf_widget_1qcaptcha_control', 350, 150, array(), 1);
    tdomf_register_form_widget_validate("1qcaptcha", "1 Question Captcha 1",'tdomf_widget_1qcaptcha_validate', array(), 1);
    tdomf_register_form_widget_hack("1qcaptcha", "1 Question Captcha 1",'tdomf_widget_1qcaptcha_hack', array(), 1);
    
    for($i = 2; $i <= $count; $i++) {
      tdomf_register_form_widget("1qcaptcha-$i","1 Question Captcha $i", 'tdomf_widget_1qcaptcha', array(), $i);
      tdomf_register_form_widget_control("1qcaptcha-$i", "1 Question Captcha $i",'tdomf_widget_1qcaptcha_control', 350, 150, array(), $i);
      tdomf_register_form_widget_validate("1qcaptcha-$i", "1 Question Captcha $i",'tdomf_widget_1qcaptcha_validate', array(), $i);
      tdomf_register_form_widget_hack("1qcaptcha-$i","1 Question Captcha $i", 'tdomf_widget_1qcaptcha_hack', array(), $i);
    }
  }
}
add_action('tdomf_generate_form_start','tdomf_widget_1qcaptcha_init');
add_action('tdomf_validate_form_start','tdomf_widget_1qcaptcha_init');
add_action('tdomf_control_form_start','tdomf_widget_1qcaptcha_init');
add_action('tdomf_widget_page_top','tdomf_widget_1qcaptcha_init');

/////////////////////////////////
// Update option for widget count
//
function tdomf_widget_1qcaptcha_handle_number($form_id){
  if(tdomf_form_exists($form_id)) {
    if ( $_POST['tdomf-widget-1qcaptcha-number-submit'] ) {
      $count = $_POST['tdomf-widget-1qcaptcha-number'];
      if($count > 0){ tdomf_set_option_widget('tdomf_1qcaptcha_widget_count',$count,$form_id); }
    }
  }
}
#add_action('tdomf_widget_page_top','tdomf_widget_1qcaptcha_handle_number');
add_action('tdomf_control_form_start','tdomf_widget_1qcaptcha_handle_number');

/////////////////////////////////
// Get options for this widget
//
function tdomf_widget_1qcaptcha_get_options($number = 1,$form_id = 1) {
  $postfix = "";
  if($number != 1){ $postfix = "_$number"; }
  $options = tdomf_get_option_widget("tdomf_1qcaptcha_widget$postfix", $form_id);
    if($options == false) {
       $options = array();
       $options['question'] = __("What year is it?","tdomf");
       $options['answer'] = __("2008","tdomf");
    }
  return $options;
}

//////////////////////////////
// Display the widget! 
//
function tdomf_widget_1qcaptcha($args,$params) {
  $number = 1;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  $options = tdomf_widget_1qcaptcha_get_options($number,$args['tdomf_form_id']);
  $postfix = "";
  if($number != 1){ $postfix = "-$number"; }
  extract($args);
  $output  = $before_widget;  
  $output .= "\t\t".'<label for="q1captcha" class="required" >';
  $output .= $options['question'];
  $output .= "\n\t\t<br/>\n\t\t".'<input type="text" id="q1captcha'.$postfix.'" name="q1captcha'.$postfix.'" size="30" value="'.htmlentities($args["q1captcha$postfix"],ENT_QUOTES,get_bloginfo('charset')).'" />';
  $output .= '</label>';
  $output .= $after_widget;
  return $output;
}

function tdomf_widget_1qcaptcha_hack($args,$params) {
  $number = 1;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  $options = tdomf_widget_1qcaptcha_get_options($number,$args['tdomf_form_id']);
  $postfix = "";
  if($number != 1){ $postfix = "-$number"; }
  extract($args);
  $output  = $before_widget;  
  $output .= "\t\t".'<label for="q1captcha" class="required" >';
  $output .= $options['question'];
  $output .= '<br/><input type="text" id="q1captcha'.$postfix.'" name="q1captcha'.$postfix.'" size="30" value="';
  $output .= "<?php echo htmlentities(\$post_args['q1captcha$postfix'],ENT_QUOTES,get_bloginfo('charset')); ?>".'" />';
  $output .= '</label>';
  $output .= $after_widget;
  return $output;
}

//////////////////////////////////////
// Validate answer
//
function tdomf_widget_1qcaptcha_validate($args,$preview,$params) {
    
  // don't bother validating for preview
  if($preview) {
    return NULL;
  }
  $number = 1;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  $options = tdomf_widget_1qcaptcha_get_options($number,$args['tdomf_form_id']);
  $postfix = "";
  if($number != 1){ $postfix = "-$number"; }
  
  extract($args);
  $simplecaptcha = trim(strtolower($args["q1captcha$postfix"]));
  $answer = trim(strtolower($options['answer']));
  if($simplecaptcha != $answer) {
    return $before_widget.sprintf(__("You must answer the captcha question. Hint: the answer is \"%s\".","tdomf"),$answer).$after_widget;
  } else {
    return NULL;
  }
}

///////////////////////////////////////////////////
// Display and handle content widget control panel 
//
function tdomf_widget_1qcaptcha_control($form_id,$params) {
  $number = 1;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  $options = tdomf_widget_1qcaptcha_get_options($number,$form_id);
  $postfix1 = "";
  $postfix2 = "";
  if($number != 1){ 
    $postfix1 = "-$number"; 
    $postfix2 = "_$number";
  }
  // Store settings for this widget
    if ( $_POST["1qcaptcha$postfix1-submit"] ) {
     $newoptions['question'] = $_POST["q1captcha$postfix1-question"];
     $newoptions['answer'] = $_POST["q1captcha$postfix1-answer"];
     if ( $options != $newoptions ) {
        $options = $newoptions;
        tdomf_set_option_widget('tdomf_1qcaptcha_widget'.$postfix2, $options,$form_id);
     }
  }
   // Display control panel for this widget
        ?>
<div>

<label for="q1captcha<?php echo $postfix1; ?>-question" ><?php _e("The simple question:","tdomf"); ?><br/>
<input type="text" size="40" id="q1captcha<?php echo $postfix1; ?>-question" name="q1captcha<?php echo $postfix1; ?>-question" value="<?php echo htmlentities($options['question'],ENT_QUOTES,get_bloginfo('charset')); ?>" />
</label>
<br/><br/>
<label for="q1captcha<?php echo $postfix1; ?>-answer" ><?php _e("The simple answer:","tdomf"); ?><br/>
<input type="text" size="40" id="q1captcha<?php echo $postfix1; ?>-answer" name="q1captcha<?php echo $postfix1; ?>-answer" value="<?php echo htmlentities($options['answer'],ENT_QUOTES,get_bloginfo('charset')); ?>" />
</label>

</div>
        <?php 
}

?>