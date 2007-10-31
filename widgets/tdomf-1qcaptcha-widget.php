<?php
/*
Name: "1 Question Captcha"
URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: The user must answer a simple question before a post is submitted
Version: 2
Author: Mark Cunningham
Author URI: http://thedeadone.net
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

// Get options for this widget
//
function tdomf_widget_1qcaptcha_get_options() {
  $options = get_option('tdomf_1qcaptcha_widget');
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
function tdomf_widget_1qcaptcha($args) {
  extract($args);
  $options = tdomf_widget_1qcaptcha_get_options();
  $output  = $before_widget;  
  $output .= '<label for="1qcaptcha" class="required" >';
  $output .= $options['question'];
  $output .= '<br/><input type="textfield" id="simplecaptcha" name="simplecaptcha" size="30" value="'.$simplecaptcha.'" />';
  $output .= '</label>';
  $output .= $after_widget;
  return $output;
}
tdomf_register_form_widget('1-question-captcha','1 Question Captcha', 'tdomf_widget_1qcaptcha');

//////////////////////////////////////
// Validate answer
//
function tdomf_widget_1qcaptcha_validate($args) {
  $options = tdomf_widget_1qcaptcha_get_options();
  extract($args);
  $simplecaptcha = trim(strtolower($simplecaptcha));
  $answer = trim(strtolower($options['answer']));
  if($simplecaptcha != $answer) {
    return $before_widget.sprintf(__("You must answer the captcha question. Hint: the answer is \"%s\".","tdomf"),$answer).$after_widget;
  } else {
    return NULL;
  }
}
tdomf_register_form_widget_validate('1-question-captcha','1 Question Captcha', 'tdomf_widget_1qcaptcha_validate');

///////////////////////////////////////////////////
// Display and handle content widget control panel 
//
function tdomf_widget_1qcaptcha_control() {
  $options = tdomf_widget_1qcaptcha_get_options();
  // Store settings for this widget
    if ( $_POST['1-question-captcha-submit'] ) {
     $newoptions['question'] = strip_tags($_POST['1-question-captcha-question']);
     $newoptions['answer'] = strip_tags($_POST['1-question-captcha-answer']);
     if ( $options != $newoptions ) {
        $options = $newoptions;
        update_option('tdomf_1qcaptcha_widget', $options);
        
     }
  }

   // Display control panel for this widget
  
        ?>
<div>

<label for="1-question-captcha-question" ><?php _e("The simple question:","tdomf"); ?><br/>
<input type="textfield" size="40" id="1-question-captcha-question" name="1-question-captcha-question" value="<?php echo $options['question']; ?>" />
</label>
<br/><br/>
<label for="1-question-captcha-question" ><?php _e("The simple answer:","tdomf"); ?><br/>
<input type="textfield" size="40" id="1-question-captcha-answer" name="1-question-captcha-answer" value="<?php echo $options['answer']; ?>" />
</label>

</div>
        <?php 
}
tdomf_register_form_widget_control('1-question-captcha','1 Question Captcha', 'tdomf_widget_1qcaptcha_control', 350, 150);


?>