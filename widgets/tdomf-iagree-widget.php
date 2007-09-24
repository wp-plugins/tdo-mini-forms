<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

function tdomf_widget_iagree_get_options() {
  $options = get_option('tdomf_iagree_widget');
    if($options == false) {
       $options = array();
       $options['text'] = __("I agree with the <a href='#'>posting policy</a>.","tdomf");
       $options['error-text'] = __("You must agree with <a href='#'>posting policy</a> policy before submission!","tdomf");
    }
  return $options;
}

//////////////////////////////
// Display the widget! 
//
function tdomf_widget_iagree($args) {
  extract($args);
  $options = tdomf_widget_iagree_get_options();
  
  $output  = $before_widget;  
  $output .= '<input type="checkbox" name="iagree" id="iagree" ';
  if($args['iagree']) { $output .= "checked "; }
  $output .= '/><label for="iagree" class="required" > ';
  $output .= $options['text'];
  $output .= ' </label>';
  $output .= $after_widget;
  return $output;
}
tdomf_register_form_widget('I Agree', 'tdomf_widget_iagree');

//////////////////////////////////////
// User must Agree! 
//
function tdomf_widget_iagree_validate($args) {
  $options = tdomf_widget_iagree_get_options();
  extract($args);
  if(!isset($iagree)) {
    return $before_widget.$options['error-text'].$after_widget;
  } else {
    return NULL;
  }
}
tdomf_register_form_widget_validate('I Agree', 'tdomf_widget_iagree_validate');

///////////////////////////////////////////////////
// Display and handle content widget control panel 
//
function tdomf_widget_iagree_control() {
  $options = tdomf_widget_iagree_get_options();
  // Store settings for this widget
    if ( $_POST['i-agree-submit'] ) {
     $newoptions['text'] = $_POST['i-agree-text'];
     $newoptions['error-text'] = $_POST['i-agree-error-text'];
     if ( $options != $newoptions ) {
        $options = $newoptions;
        update_option('tdomf_iagree_widget', $options);
        
     }
  }

   // Display control panel for this widget
  
  extract($options);

        ?>
<div>

<i><?php _e("HTML is permissible in messages.","tdomf"); ?></i>

<br/><br/>

<label for="i-agree-text" ><?php _e("The message to show beside the checkbox:","tdomf"); ?><br/>
<textarea cols="40" rows="2" id="i-agree-text" name="i-agree-text" ><?php echo $options['text']; ?></textarea>
</label>
<br/><br/>
<label for="i-agree-error-text" ><?php _e("The message to show when the user has failed to check the box:","tdomf"); ?><br/>
<textarea cols="40" rows="2" id="i-agree-error-text" name="i-agree-error-text" ><?php echo $options['error-text']; ?></textarea>
</label>

</div>
        <?php 
}
tdomf_register_form_widget_control('I Agree', 'tdomf_widget_iagree_control', 350, 300);


?>