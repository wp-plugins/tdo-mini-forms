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

// TODO: Add a box to allow customised formatting of custom field and 
// automatically added it to the post content

// Add a menu option to control the number of cf widgets to the bottom of the 
// tdomf widget page
//
function tdomf_widget_customfields_number_bottom(){
  $count = get_option('tdomf_customfields_widget_count');
  if($count <= 0){ $count = 1; } 
  
  ?>
  <div class="wrap">
    <form method="post">
      <h2><?php _e("Custom Fields Widgets","tdomf"); ?></h2>
      <p style="line-height: 30px;"><?php _e("How many Custom Fields widgets would you like?","tdomf"); ?>
      <select id="tdomf-widget-customfields-number" name="tdomf-widget-customfields-number" value="<?php echo $count; ?>">
      <?php for($i = 1; $i < 10; $i++) { ?>
        <option value="<?php echo $i; ?>" <?php if($i == $count) { ?> selected="selected" <?php } ?>><?php echo $i; ?></option>
      <?php } ?>
      </select>
      <span class="submit">
        <input type="submit" value="Save" id="tdomf-widget-customfields-number-submit" name="tdomf-widget-customfields-number-submit" />
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
       $options['key'] = "";
       $options['title'] = "";
       $options['required'] = false;
       $options['defval'] = "";
       $options['size'] = 30;
       $options['type'] = 'textfield';
       $options['cols'] = 40;
       $options['rows'] = 10; 
       // textfield specific
       $options['tf-subtype'] = 'text';
       // textarea specific
       $options['ta-restrict-tags'] = false;
       $options['ta-allowable-tags'] = "<p><b><i><u><strong><a><img><table><tr><td><blockquote><ul><ol><li><br><sup>";
       $options['ta-quicktags'] = true;
       $options['ta-content-filter'] = true;       
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
  $options = tdomf_widget_customfields_get_options($number);
  
  if($options['type'] == 'textfield') {
    return tdomf_widget_customfields_textfield($args,$number,$options);
  } else if($options['type'] == 'hidden') {
    return tdomf_widget_customfields_hidden($args,$number,$options);
  } else if($options['type'] == 'textarea') {
    return tdomf_widget_customfields_textarea($args,$number,$options);
  }
  
  return "";
}

///////////////////////////////////////
// Preview 
//
function tdomf_widget_customfields_preview($args,$params) {
  $number = 0;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  $options = tdomf_widget_customfields_get_options($number);
  
  if($options['type'] == 'textfield') {
    return tdomf_widget_customfields_textfield_preview($args,$number,$options);
  } else if($options['type'] == 'textarea') {
    return tdomf_widget_customfields_textarea_preview($args,$number,$options);
  }
  
  return "";
}
  
function tdomf_widget_customfields_validate($args,$params) {
  $number = 0;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  $options = tdomf_widget_customfields_get_options($number);
  
  if($options['type'] == 'textfield') {
    return tdomf_widget_customfields_textfield_validate($args,$number,$options);
  } else if($options['type'] == 'textarea') {
    return tdomf_widget_customfields_textarea_validate($args,$number,$options);
  }
  
  return NULL;
}

function tdomf_widget_customfields_post($args,$params) {
  $number = 0;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  $options = tdomf_widget_customfields_get_options($number);
  
  if($options['type'] == 'textfield') {
    return tdomf_widget_customfields_textfield_post($args,$number,$options);
  } else if($options['type'] == 'hidden') {
    return tdomf_widget_customfields_hidden_post($args,$number,$options);
  } else if($options['type'] == 'textarea') {
    return tdomf_widget_customfields_textarea_post($args,$number,$options);
  }
  
  return NULL;
}

function tdomf_widget_customfields_adminemail($args,$params) {
  $number = 0;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  $options = tdomf_widget_customfields_get_options($number);
  
  if($options['type'] == 'textfield') {
    return tdomf_widget_customfields_textfield_adminemail($args,$number,$options);
  } else if($options['type'] == 'textarea') {
    return tdomf_widget_customfields_textarea_adminemail($args,$number,$options);
  }
  
  return "";
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
     $newoptions['title'] = $_POST["customfields-title-$number"];
     $newoptions['key'] = $_POST["customfields-key-$number"];;
     $newoptions['required'] = isset($_POST["customfields-required-$number"]);
     $newoptions['defval'] = $_POST["customfields-defval-$number"];
     $newoptions['size'] = intval($_POST["customfields-size-$number"]);
     $newoptions['cols'] = intval($_POST["customfields-cols-$number"]);
     $newoptions['rows'] = intval($_POST["customfields-rows-$number"]);
     $newoptions['type'] = $_POST["customfields-type-$number"];
     $newoptions = tdomf_widget_customfields_textfield_control_handler($number,$newoptions);
     $newoptions = tdomf_widget_customfields_textarea_control_handler($number,$newoptions);     
     if ( $options != $newoptions ) {
        $options = $newoptions;
        update_option('tdomf_customfields_widget_'.$number, $options);
        
     }
  }
// Display control panel for this widget
  
        ?>
<div>

<label for="customfields-title-<?php echo $number; ?>">
<?php _e("Title:","tdomf"); ?><br/>
<input type="textfield" size="40" id="customfields-title-<?php echo $number; ?>" name="customfields-title-<?php echo $number; ?>" value="<?php echo $options['title']; ?>" />
</label>

<br/><br/>

<label for="customfields-name-<?php echo $number; ?>">
<?php _e("Custom Field Key:","tdomf"); ?><br/>
<input type="textfield" size="40" id="customfields-key-<?php echo $number; ?>" name="customfields-key-<?php echo $number; ?>" value="<?php echo $options['key']; ?>" />
</label>

<br/><br/>

<script type="text/javascript">
  //<![CDATA[
  function customfields_change_specific<?php echo $number; ?>(){
    var type = document.getElementById("customfields-type-<?php echo $number; ?>").value;
    if(type == 'textfield') {
      document.getElementById("customfiles-specific-textfield-<?php echo $number; ?>").style.display = 'inline';
      document.getElementById("customfiles-specific-hidden-<?php echo $number; ?>").style.display = 'none';
      document.getElementById("customfiles-specific-textarea-<?php echo $number; ?>").style.display = 'none';
    } else if(type == 'hidden') {
      document.getElementById("customfiles-specific-textfield-<?php echo $number; ?>").style.display = 'none';
      document.getElementById("customfiles-specific-hidden-<?php echo $number; ?>").style.display = 'inline';
      document.getElementById("customfiles-specific-textarea-<?php echo $number; ?>").style.display = 'none';
    } else if(type == 'textarea') {
      document.getElementById("customfiles-specific-textfield-<?php echo $number; ?>").style.display = 'none';
      document.getElementById("customfiles-specific-hidden-<?php echo $number; ?>").style.display = 'none';
      document.getElementById("customfiles-specific-textarea-<?php echo $number; ?>").style.display = 'inline';
    }
  }
  //]]>
</script>

<label for="customfields-type-<?php echo $number; ?>">
<?php _e("Type: ","tdomf"); ?>
<select name="customfields-type-<?php echo $number; ?>" onChange="customfields_change_specific<?php echo $number; ?>();">
<option value="textfield" <?php if($options['type'] == 'textfield') { ?> selected <? } ?> /><?php _e("Text Field","tdomf"); ?>
<option value="hidden" <?php if($options['type'] == 'hidden') { ?> selected <? } ?> /><?php _e("Hidden","tdomf"); ?>
<option value="textarea" <?php if($options['type'] == 'textarea') { ?> selected <? } ?> /><?php _e("Text Area","tdomf"); ?>
<!-- TODO <option value="checkbox" /><?php _e("Check Box","tdomf"); ?>
<option value="select" /><?php _e("Drop Down List","tdomf"); ?>
<option value="radio" /><?php _e("Radio","tdomf"); ?> -->
</select>
</label>


<div id="customfiles-specific-textfield-<?php echo $number; ?>" <?php if($options['type'] == 'textfield') { ?> style="display:inline;" <? } else { ?> style="display:none;" <?php } ?>>
<?php echo tdomf_widget_customfields_textfield_control($number,$options); ?>
</div>

<div id="customfiles-specific-hidden-<?php echo $number; ?>" <?php if($options['type'] == 'hidden') { ?> style="display:inline;" <? } else { ?> style="display:none;" <?php } ?>>
<?php echo tdomf_widget_customfields_hidden_control($number,$options); ?>
</div>

<div id="customfiles-specific-textarea-<?php echo $number; ?>" <?php if($options['type'] == 'textarea') { ?> style="display:inline;" <? } else { ?> style="display:none;" <?php } ?>>
<?php echo tdomf_widget_customfields_textarea_control($number,$options); ?>
</div>

</div>
        <?php 
}


function tdomf_widget_customfields_init(){
  if ( $_POST['tdomf-widget-customfields-number-submit'] ) {
    $count = $_POST['tdomf-widget-customfields-number'];
    if($count > 0){ update_option('tdomf_customfields_widget_count',$count); }
  }
  $count = get_option('tdomf_customfields_widget_count');
  if($count <= 0){ $count = 1; } 
  for($i = 1; $i <= $count; $i++) {
    tdomf_register_form_widget("customfields-$i","Custom Fields $i", 'tdomf_widget_customfields',$i);
    tdomf_register_form_widget_control("customfields-$i", "Custom Fields $i",'tdomf_widget_customfields_control', 400, 550, $i);
    tdomf_register_form_widget_preview("customfields-$i", "Custom Fields $i",'tdomf_widget_customfields_preview', true, $i);
    tdomf_register_form_widget_validate("customfields-$i", "Custom Fields $i",'tdomf_widget_customfields_validate', true, $i);
    tdomf_register_form_widget_post("customfields-$i", "Custom Fields $i",'tdomf_widget_customfields_post', true, $i);
    tdomf_register_form_widget_adminemail("customfields-$i", "Custom Fields $i",'tdomf_widget_customfields_adminemail', $i);
  }
}
tdomf_widget_customfields_init();

////////////////////////////////////////////////////////////////////////////////
//                                                Custom Field as a Textfield //
////////////////////////////////////////////////////////////////////////////////

function tdomf_widget_customfields_textfield($args,$number,$options) {
  extract($args);
  
  $value = $options['defval'];
  if(isset($args["customfields-textfield-$number"])){
    $value = $args["customfields-textfield-$number"];
  }
  
  $output  = $before_widget;
  
  if($options['required']) {
    $output .= "<label for=\"customfields-textfield-$number\" class=\"required\">".$options['title']." ".__("(Required)","tdomf")."<br/>\n";
  } else {
    $output .= "<label for=\"customfields-textfield-$number\">".$options['title']."<br/>\n";
  }
  if($options['tf-subtype'] == 'email') {
    $output .= __("Email:","tdomf")." "; 
  } else if($options['tf-subtype'] == 'url') {
    $output .= __("URL:","tdomf")." ";
  }
  $output .= "<input type=\"text\" name=\"customfields-textfield-$number\" id=\"customfields-textfield-$number\" size=\"".$options['title-size']."\" value=\"$value\" />";
  $output .= "</label>\n";
  
  $output .= $after_widget;
  return $output;
}

function tdomf_widget_customfields_textfield_control_handler($number,$options) {
  $options['tf-subtype'] = $_POST["customfields-tf-subtype-$number"];
  return $options;
}

function tdomf_widget_customfields_textfield_control($number,$options){ 
  $output  = "<h3>".__("Text Field","tdomf")."</h3>";

  $output .= "<label for=\"customfields-required-$number\">";
  $output .= "<input type=\"checkbox\" name=\"customfields-required-$number\" id=\"customfields-required-$number\"";
  if($options['required']) { $output .= " checked "; }
  $output .= "/>".__("Required","tdomf")."</label><br/><Br/>";

  $output .= "<label for=\"customfields-size-$number\">";
  $output .= __("Size:","tdomf");;
  $output .= "<input type=\"textfield\" name=\"customfields-size-$number\" id=\"customfields-size-$number\" value=\"".$options['size']."\" size=\"3\" />";
  $output .= "</label><br/><br/>";

  $output .= "<label for=\"customfields-defval-$number\">";
  $output .= __("Default Value:","tdomf")."<br/>";
  $output .= "<input type=\"textfield\" size=\"40\" id=\"customfields-defval-$number\" name=\"customfields-defval-$number\" value=\"".$options['defval']."\" />";
  $output .= "</label><br/><br/>";

  $output .= "<label for \"customfields-tf-subtype-$number\">";
  $output .= "<input type=\"radio\" name=\"customfields-tf-subtype-$number\" id=\"customfields-tf-subtype-$number\" value=\"text\"";
  if($options['tf-subtype'] == "text") { $output .= " checked "; }
  $output .= ">".__("Text","tdomf")."<br>";
  $output .= "<input type=\"radio\" name=\"customfields-tf-subtype-$number\" id=\"customfields-tf-subtype-$number\" value=\"email\"";
  if($options['tf-subtype'] == "email") { $output .= " checked "; }
  $output .= ">".__("Email (only valid email addresses will be accepted)","tdomf")."<br>";
  $output .= "<input type=\"radio\" name=\"customfields-tf-subtype-$number\" id=\"customfields-tf-subtype-$number\" value=\"url\"";
  if($options['tf-subtype'] == "url") { $output .= " checked "; }
  $output .= ">".__("URL (only valid URLs will be accepted)","tdomf")."<br>";
  $output .= "</label>";

  return $output;
}

function tdomf_widget_customfields_textfield_preview($args,$number,$options) {
  extract($args);
  $output = $before_widget;
  if($options['title'] != "") {
    $output .= $before_title.$options['title'].$after_title;
  }
  $output .= $args["customfields-textfield-$number"];
  $output .= $after_widget;
  return $output;
}

function tdomf_widget_customfields_textfield_validate($args,$number,$options) {
  extract($args);
  
  if($options['required'] && empty($args["customfields-textfield-$number"])) {
    return $before_widget.sprintf(__("You must enter a value for %s!","tdomf"),$options['title']).$after_widget;
  }
  
  if($options['tf-subtype'] == 'url' && $args["customfields-textfield-$number"] != $options['defval'] && !tdomf_check_url($args["customfields-textfield-$number"])) {
    return $before_widget.sprintf(__("The URL \"%s\" does not look correct.","tdomf"),$args["customfields-textfield-$number"]).$after_widget;
  }
  
  if($options['tf-subtype'] == 'email' && $args["customfields-textfield-$number"] != $options['defval'] && !tdomf_check_email_address($args["customfields-textfield-$number"])) {
     return $before_widget.sprintf(__("The email address \"%s\" does not look correct.","tdomf"),$args["customfields-textfield-$number"]).$after_widget;
  }
  
  return NULL;
}

function tdomf_widget_customfields_textfield_post($args,$number,$options) {
  extract($args);
  add_post_meta($post_ID,$options['key'],$args["customfields-textfield-$number"]);
  return NULL;
}

function tdomf_widget_customfields_textfield_adminemail($args,$number,$options) {
  extract($args);
  $output  = $before_widget;
  $output .= $before_title.__("Custom Field: ","tdomf");
  if($options['title'] != "") {
    $output .= '"'.$options['title'].'" ';
  }
  $output .= '['.$options['key'].']';
  $output .= $after_title;
  $output .= get_post_meta($post_ID,$options['key'],true);
  $output .= $after_widget;
  return $output;
}

////////////////////////////////////////////////////////////////////////////////
//                                                   Custom Field as a Hidden //
////////////////////////////////////////////////////////////////////////////////

function tdomf_widget_customfields_hidden($args,$number,$options) {
  $value = htmlentities($options['defval']);
  $output = "<input type=\"hidden\" name=\"customfields-hidden-$number\" id=\"customfields-hidden-$number\" value=\"$value\" />";
  return $output;
}

function tdomf_widget_customfields_hidden_post($args,$number,$options) {
  extract($args);
  add_post_meta($post_ID,$options['key'],$args["customfields-hidden-$number"]);
  return NULL;
}

function tdomf_widget_customfields_hidden_control($number,$options){ 
  $output  = "<h3>".__("Hidden","tdomf")."</h3>";
  $output .= "<label for=\"customfields-defval-$number\">";
  $output .= __("Value:","tdomf")."<br/>";
  $output .= "<input type=\"textfield\" size=\"40\" id=\"customfields-defval-$number\" name=\"customfields-defval-$number\" value=\"".$options['defval']."\" />";
  $output .= "</label><br/><br/>";
  return $output;
}

////////////////////////////////////////////////////////////////////////////////
//                                                 Custom Field as a Textarea //
////////////////////////////////////////////////////////////////////////////////

function tdomf_widget_customfields_textarea_control_handler($number,$options) {
  $options['ta-quicktags'] = isset($_POST["customfields-ta-quicktags-$number"]);
  $options['ta-restrict-tags'] = isset($_POST["customfields-ta-restrict-tags-$number"]);
  $options['ta-allowable-tags'] = $_POST["customfields-ta-allowable-tags-$number"];
  $options['ta-content-filter'] = isset($_POST["customfields-ta-content-filter-$number"]);
  return $options;
}


function tdomf_widget_customfields_textarea_control($number,$options){ 
  
  // TODO: 'the_content' filter
  
  $output  = "<h3>".__("Text Area","tdomf")."</h3>";

  $output .= "<label for=\"customfields-required-$number\">";
  $output .= "<input type=\"checkbox\" name=\"customfields-required-$number\" id=\"customfields-required-$number\"";
  if($options['required']) { $output .= " checked "; }
  $output .= "/>".__("Required","tdomf")."</label><br/><Br/>";

  $output .= "<label for=\"customfields-defval-$number\">";
  $output .= __("Default Value:","tdomf")."<br/>";
  $output .= "<textarea title='true' cols=\"30\" rows=\"3\" id=\"customfields-defval-$number\" name=\"customfields-defval-$number\">".$options['defval']."</textarea>";
  $output .= "</label><br/><br/>";
  
  $output .= "<label for=\"customfields-quicktags-$number\">";
  $output .=  __("Use Quicktags","tdomf"); 
  $output .= " <input type=\"checkbox\" name=\"customfields-ta-quicktags-$number\" id=\"customfields-ta-quicktags-$number\"";
  if($options['ta-quicktags']){ $output .= " checked "; }
  $output .= "></label><br/><br/>";
  
  $output .= "<label for=\"customfields-ta-content-filter-$number\">";
  $output .=  __("Format like Post Content <i>(convert new lines to paragraphs, etc.)</i>","tdomf"); 
  $output .= " <input type=\"checkbox\" name=\"customfields-ta-content-filter-$number\" id=\"customfields-ta-content-filter-$number\"";
  if($options['ta-content-filter']){ $output .= " checked "; }
  $output .= "></label><br/><br/>";
  
  $output .= "<label for=\"customfields-cols-$number\" >";
  $output .= __("Cols","tdomf");
  $output .= " <input type=\"textfield\" name=\"customfields-cols-$number\" id=\"customfields-cols-$number\" value=\"";
  $output .= $options['cols']."\" size=\"3\" /></label>";
  $output .= " <label for=\"customfields-rows-$number\" >";
  $output .= __("Rows","tdomf");
  $output .= " <input type=\"textfield\" name=\"customfields-rows-$number\" id=\"customfields-rows-$number\" value=\"";
  $output .= $options['rows']."\" size=\"3\" /></label><br/><br/>";

  $output .= "<label for=\"customfields-restrict-tags-$number\">";
  $output .= __("Restrict Tags","tdomf");
  $output .= " <input type=\"checkbox\" name=\"customfields-ta-restrict-tags-$number\" id=\"customfields-ta-restrict-tags-$number\"";
  if($options['ta-restrict-tags']){ $output .= " checked "; }
  $output .= "></label><br/><br/>";
  
  $output .= "<label for=\"customfields-allowable-tags-$number\">";
  $output .= __("Allowable Tags","tdomf");
  $output .= "<br/><textarea title=\"true\" cols=\"30\" name=\"customfields-ta-allowable-tags-$number\" id=\"customfields-ta-allowable-tags-$number\" >".$options['ta-allowable-tags']."</textarea></label>";

  return $output;
}

function tdomf_widget_customfields_textarea($args,$number,$options) {
  extract($args);
  
  $value = $options['defval'];
  if(isset($args["customfields-textarea-$number"])){
    $value = $args["customfields-textarea-$number"];
  }
  
  if($options['required']) {
    $output = "<label for=\"customfields-textarea-$number\" class=\"required\">".$options['title']." ".__("(Required)","tdomf")."<br/>\n";
  } else {
    $output = "<label for=\"customfields-textarea-$number\">".$options['title']."<br/>\n";
  }
  $output .= "</label>\n";
    
  if($options['ta-allowable-tags'] != "" && $options['ta-restrict-tags']) {
    $output .= sprintf(__("<small>Allowable Tags: %s</small>","tdomf"),htmlentities($options['ta-allowable-tags']))."<br/>";
  }
  if($options['ta-quicktags']) {
    $qt_path = TDOMF_URLPATH."tdomf-quicktags.js.php?postfix=cfta$number";
    if($options['ta-allowable-tags'] != "" && $options['ta-restrict-tags']) {
      $qt_path = TDOMF_URLPATH."tdomf-quicktags.js.php?postfix=cfta$number&allowed_tags=".urlencode($options['ta-allowable-tags']);
    }
    $output .= "\n<script src='$qt_path' type='text/javascript'></script>";
    $output .= "\n<script type='text/javascript'>edToolbarcfta$number();</script>\n";
  }
  $output .= "<textarea title=\"true\" rows=\"".$options['rows']."\" cols=\"".$options['cols']."\" name=\"customfields-textarea-$number\" id=\"customfields-textarea-$number\" >$value</textarea>";
  if($options['ta-quicktags']) {
    $output .= "\n<script type='text/javascript'>var edCanvascfta$number = document.getElementById('customfields-textarea-$number');</script>\n";
  }
  
  return $before_widget.$output.$after_widget;
}

function tdomf_widget_customfields_textarea_validate($args,$number,$options) {
  extract($args);
  $output = "";
  if($options['required'] && (empty($args["customfields-textarea-$number"]) || trim($args["customfields-textarea-$number"]) == "")) {
    if(!empty($options['title'])) {
      $output .= sprintf(__("You must specify some text for \"%s\".","tdomf"),$options['title']);
    } else {
      $output .= __("You are missing some text!","tdomf");
    }
  }
  // return output if any
  if($output != "") {
    return $before_widget.$output.$after_widget;
  } else {
    return NULL;
  }
}

function tdomf_widget_customfields_textarea_post($args,$number,$options) {
  extract($args);
  $text = $args["customfields-textarea-$number"];
  if($options['ta-restrict-tags']) {
    $text = strip_tags($text,$options['ta-allowable-tags']);
  }
  if($options['ta-content-filter']) {
    $text = apply_filters('the_content', $text);
  }
  add_post_meta($post_ID,$options['key'],$text);
  return NULL;
}

function tdomf_widget_customfields_textarea_adminemail($args,$number,$options) {
  extract($args);
  $output  = $before_widget;
  $output .= $before_title.__("Custom Field: ","tdomf");
  if($options['title'] != "") {
    $output .= '"'.$options['title'].'" ';
  }
  $output .= '['.$options['key'].']';
  $output .= $after_title;
  $output .= get_post_meta($post_ID,$options['key'],true);
  $output .= $after_widget;
  return $output;
}

function tdomf_widget_customfields_textarea_preview($args,$number,$options) {
  extract($args);
  $output = $before_widget;
  if($options['title'] != "") {
    $output .= $before_title.$options['title'].$after_title;
  }
  
  $text = $args["customfields-textarea-$number"];
  if($options['ta-restrict-tags']) {
    $text = strip_tags($text,$options['ta-allowable-tags']);
  }
  if($options['ta-content-filter']) {
    $text = apply_filters('the_content', $text);
  }
  
  $output .= $text;
  $output .= $after_widget;
  return $output;
}
?>
