<?php
/*
Name: "I Agree"
URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: This widget provides a checkbox that the user must click before a post will be accept such as the classic "I Agree" buttons.
Version: 4
Author: Mark Cunningham
Author URI: http://thedeadone.net
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

// Get Options for this widget
//
function tdomf_widget_iagree_get_options($form_id) {
  $options = tdomf_get_option_widget('tdomf_iagree_widget',$form_id);
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
  $options = tdomf_widget_iagree_get_options($tdomf_form_id);
  
  $output  = $before_widget;  
  $output .= '<input type="checkbox" name="iagree" id="iagree" ';
  if($args['iagree']) { $output .= "checked "; }
  $output .= '/><label for="iagree" class="required" > ';
  $output .= $options['text'];
  $output .= ' </label>';
  $output .= $after_widget;
  return $output;
}
tdomf_register_form_widget('i-agree',__('I Agree','tdomf'), 'tdomf_widget_iagree');

function tdomf_widget_iagree_hack($args) {
  extract($args);
  $options = tdomf_widget_iagree_get_options($tdomf_form_id);
  
  $output  = $before_widget;  
  $output .= "\t\t".'<input type="checkbox" name="iagree" id="iagree" ';
  $output .= "<?php if(\$iagree) { echo 'checked'; } ?>";
  $output .= ' />'."\n\t\t".'<label for="iagree" class="required" > ';
  $output .= $options['text'];
  $output .= ' </label>';
  $output .= $after_widget;
  return $output;
}
tdomf_register_form_widget_hack('i-agree',__('I Agree','tdomf'), 'tdomf_widget_iagree_hack');

//////////////////////////////////////
// User must Agree! 
//
function tdomf_widget_iagree_validate($args,$preview) {
  if($preview) {
    return NULL;
  }
  extract($args);
  $options = tdomf_widget_iagree_get_options($tdomf_form_id);
  if(!isset($iagree)) {
    return $before_widget.$options['error-text'].$after_widget;
  } else {
    return NULL;
  }
}
tdomf_register_form_widget_validate('i-agree',__('I Agree','tdomf'), 'tdomf_widget_iagree_validate');

///////////////////////////////////////////////////
// Display and handle content widget control panel 
//
function tdomf_widget_iagree_control($form_id) {
  $options = tdomf_widget_iagree_get_options($form_id);
  // Store settings for this widget
    if ( $_POST['i-agree-submit'] ) {
     $newoptions['text'] = $_POST['i-agree-text'];
     $newoptions['error-text'] = $_POST['i-agree-error-text'];
     if ( $options != $newoptions ) {
        $options = $newoptions;
        tdomf_set_option_widget('tdomf_iagree_widget', $options, $form_id);
        
     }
  }

   // Display control panel for this widget
  
  extract($options);

        ?>
<div>

<i><?php _e("HTML is permissible in messages.","tdomf"); ?></i>

<br/><br/>

<label for="i-agree-text" ><?php _e("The message to show beside the checkbox:","tdomf"); ?><br/>
<textarea cols="40" rows="2" id="i-agree-text" name="i-agree-text" ><?php echo htmlentities($options['text'],ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
</label>
<br/><br/>
<label for="i-agree-error-text" ><?php _e("The message to show when the user has failed to check the box:","tdomf"); ?><br/>
<textarea cols="40" rows="2" id="i-agree-error-text" name="i-agree-error-text" ><?php echo htmlentities($options['error-text'],ENT_NOQUOTES,get_bloginfo('charset')); ?></textarea>
</label>

</div>
        <?php 
}
tdomf_register_form_widget_control('i-agree',__('I Agree','tdomf'), 'tdomf_widget_iagree_control', 400, 300);

function tdomf_widget_iagree_admin_error($form_id) {
    
  $options = tdomf_widget_iagree_get_options($form_id,true);

  $output = "";  
  if($options['text'] == __("I agree with the <a href='#'>posting policy</a>.","tdomf")) {
      $output .= __('<b>Warning</b>: You have not modified the text in "I Agree" widget. This contains just a place holder text and should be at least updated to point to <i>your</i> submission policy.','tdomf');
  }
  
  return $output;
}
tdomf_register_form_widget_admin_error('i-agree',__('I Agree','tdomf'), 'tdomf_widget_iagree_admin_error');
?>