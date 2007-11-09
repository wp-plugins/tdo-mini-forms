<?php
/*
Name: "1 Question Captcha"
URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: The user must answer a simple question before a post is submitted
Version: 3
Author: Mark Cunningham
Author URI: http://thedeadone.net
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

/////////////////////////////////////
// How many captcha's do you want?
//
function tdomf_widget_1qcaptcha_number_bottom(){
  $count = get_option('tdomf_1qcaptcha_widget_count');
  if($count <= 0){ $count = 1; } 
  
  ?>
  <div class="wrap">
    <form method="post">
      <h2><?php _e("1 Question Captcha Widgets","tdomf"); ?></h2>
      <p style="line-height: 30px;"><?php _e("How many 1 Question Captcha widgets would you like?","tdomf"); ?>
      <select id="tdomf-widget-1qcaptcha-number" name="tdomf-widget-1qcaptcha-number" value="<?php echo $count; ?>">
      <?php for($i = 1; $i < 10; $i++) { ?>
        <option value="<?php echo $i; ?>" <?php if($i == $count) { ?> selected="selected" <?php } ?>><?php echo $i; ?></option>
      <?php } ?>
      </select>
      <span class="submit">
        <input type="submit" value="Save" id="tdomf-widget-1qcaptcha-number-submit" name="tdomf-widget-1qcaptcha-number-submit" />
      </span>
      </p>
    </form>
  </div>
  <?php 
}
add_action('tdomf_widget_page_bottom','tdomf_widget_1qcaptcha_number_bottom');

/////////////////////////////////
// Initilise multiple captchas!
//
function tdomf_widget_1qcaptcha_init(){
  if ( $_POST['tdomf-widget-1qcaptcha-number-submit'] ) {
    $count = $_POST['tdomf-widget-1qcaptcha-number'];
    if($count > 0){ update_option('tdomf_1qcaptcha_widget_count',$count); }
  }
  $count = get_option('tdomf_1qcaptcha_widget_count');

  tdomf_register_form_widget("1qcaptcha","1 Question Captcha 1", 'tdomf_widget_1qcaptcha',1);
  tdomf_register_form_widget_control("1qcaptcha", "1 Question Captcha 1",'tdomf_widget_1qcaptcha_control', 350, 150, 1);
  tdomf_register_form_widget_validate("1qcaptcha", "1 Question Captcha 1",'tdomf_widget_1qcaptcha_validate', true, 1);
  
  for($i = 2; $i <= $count; $i++) {
    tdomf_register_form_widget("1qcaptcha-$i","1 Question Captcha $i", 'tdomf_widget_1qcaptcha',$i);
    tdomf_register_form_widget_control("1qcaptcha-$i", "1 Question Captcha $i",'tdomf_widget_1qcaptcha_control', 350, 150, $i);
    tdomf_register_form_widget_validate("1qcaptcha-$i", "1 Question Captcha $i",'tdomf_widget_1qcaptcha_validate', true, $i);
  }
}
tdomf_widget_1qcaptcha_init();

/////////////////////////////////
// Get options for this widget
//
function tdomf_widget_1qcaptcha_get_options($number = 1) {
  $postfix = "";
  if($number != 1){ $postfix = "_$number"; }
  $options = get_option("tdomf_1qcaptcha_widget$postfix");
    if($options == false) {
       $options = array();
       $options['question'] = __("What year is it?","tdomf");
       $options['answer'] = __("2007","tdomf");
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
  $options = tdomf_widget_1qcaptcha_get_options($number);
  $postfix = "";
  if($number != 1){ $postfix = "-$number"; }
  extract($args);
  $output  = $before_widget;  
  $output .= '<label for="1qcaptcha" class="required" >';
  $output .= $options['question'];
  $output .= '<br/><input type="textfield" id="1qcaptcha'.$postfix.'" name="1qcaptcha'.$postfix.'" size="30" value="'.$args["1qcaptcha$postfix"].'" />';
  $output .= '</label>';
  $output .= $after_widget;
  return $output;
}

//////////////////////////////////////
// Validate answer
//
function tdomf_widget_1qcaptcha_validate($args,$params) {
  $number = 1;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  $options = tdomf_widget_1qcaptcha_get_options($number);
  $postfix = "";
  if($number != 1){ $postfix = "-$number"; }
  
  extract($args);
  $simplecaptcha = trim(strtolower($args["1qcaptcha$postfix"]));
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
function tdomf_widget_1qcaptcha_control($params) {
  $number = 1;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  $options = tdomf_widget_1qcaptcha_get_options($number);
  $postfix1 = "";
  $postfix2 = "";
  if($number != 1){ 
    $postfix1 = "-$number"; 
    $postfix2 = "_$number";
  }
  // Store settings for this widget
    if ( $_POST["1qcaptcha$postfix1-submit"] ) {
     $newoptions['question'] = htmlentities(strip_tags($_POST["1qcaptcha$postfix1-question"]));
     $newoptions['answer'] = htmlentities(strip_tags($_POST["1qcaptcha$postfix1-answer"]));
     if ( $options != $newoptions ) {
        $options = $newoptions;
        update_option('tdomf_1qcaptcha_widget'.$postfix2, $options);
     }
  }
   // Display control panel for this widget
        ?>
<div>

<label for="1qcaptcha<?php echo $postfix1; ?>-question" ><?php _e("The simple question:","tdomf"); ?><br/>
<input type="textfield" size="40" id="1qcaptcha<?php echo $postfix1; ?>-question" name="1qcaptcha<?php echo $postfix1; ?>-question" value="<?php echo $options['question']; ?>" />
</label>
<br/><br/>
<label for="1qcaptcha<?php echo $postfix1; ?>-answer" ><?php _e("The simple answer:","tdomf"); ?><br/>
<input type="textfield" size="40" id="1qcaptcha<?php echo $postfix1; ?>-answer" name="1qcaptcha<?php echo $postfix1; ?>-answer" value="<?php echo $options['answer']; ?>" />
</label>

</div>
        <?php 
}

?>