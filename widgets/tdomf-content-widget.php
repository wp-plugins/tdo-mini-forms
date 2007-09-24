<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

#TODO: QuickTags and/or FckEditor

/////////////////////////////////////////
// Default options for the content widget 
//
function tdomf_widget_content_get_options() {
  $options = get_option('tdomf_content_widget');
    if($options == false) {
       $options = array();
       $options['title'] = "";
       $options['title-enable'] = true;
       $options['title-required'] = false;
       $options['title-size'] = 30;
       $options['text-enable'] = true;
       $options['text-required'] = true;
       $options['text-cols'] = 40;
       $options['text-rows'] = 10; 
       $options['restrict-tags'] = false;
       $options['allowable-tags'] = "<p><b><i><u><strong><a><img><table><tr><td><blockquote><ul><ol><li><br>";
    }
  return $options;
}

//////////////////////////////
// Display the content widget! 
//
function tdomf_widget_content($args) {
  extract($args);
  $options = tdomf_widget_content_get_options();
  if(!$options['title-enable'] && !$options['text-enable']) { return ""; }
  $output = $before_widget;
  if($options['title'] != "") {
    $output .= $before_title.$options['title'].$after_title;
  }
  if($options['title-enable']) {
    if($options['title-required']) {
      $output .= '<label for="content_title" class="required">'.__("Post Title (Required): ","tdomf")."<br/>\n";
    } else {
      $output .= '<label for="content_title">'.__("Post Title: ","tdomf")."<br/>\n";
    }
    $output .= '<input type="text" name="content_title" id="content_title" size="'.$options['title-size'].'" value="'.$content_title.'" />';
    $output .= "</label>\n";
    if($options['text-enable']) {
      $output .= "<br/><br/>";
    }
  }
  if($options['text-enable']) {
    if($options['text-required']) {
      $output .= '<label for="content_content" class="required">'.__("Post Text (Required): ","tdomf")."<br/>\n";      
    } else {
      $output .= '<label for="content_content">'.__("Post Text: ","tdomf")."<br/>\n";
    }
    $output .= "</label>\n";    
    if($options['allowable-tags'] != "" && $options['restrict-tags']) {
      $output .= sprintf(__("<small>Allowable Tags: %s</small>","tdomf"),htmlentities($options['allowable-tags']))."<br/>";
    }
    $output .= '<textarea title="true" rows="'.$options['text-rows'].'" cols="'.$options['text-cols'].'" name="content_content" id="content_content" >'.$content_content.'</textarea>';
  }
  $output .= $after_widget;
  return $output;
}
tdomf_register_form_widget('Content', 'tdomf_widget_content');

///////////////////////////////////////
// Preview the post's content and title
//
function tdomf_widget_content_preview($args) {
  extract($args);
  $options = tdomf_widget_content_get_options();
  if(!$options['title-enable'] && !$options['text-enable']) { return ""; }
  $output = $before_widget;
  if($options['title'] != "") {
    $output .= $before_title.$options['title'].$after_title;
  }
  if($options['title-enable']) {
    $output .= "<b>".__("Title: ","tdomf")."</b>";
    $output .= $content_title;
    $output .= "<br/>";
  }
  if($options['text-enable']) {
    $content_content = preg_replace('|\[tdomf_form1\]|', '', $content_content);
    $output .= "<b>".__("Text: ","tdomf")."</b><br/>";
    if($options['allowable-tags'] != "" && $options['restrict-tags']) {
      $output .= apply_filters('the_content', strip_tags($content_content,$options['allowable-tags']));
    } else {
      $output .= apply_filters('the_content', $content_content);
    }
  }
  $output .= $after_widget;
  return $output;
}
tdomf_register_form_widget_preview('Content', 'tdomf_widget_content_preview');

///////////////////////////////////////
// Add the title and content to the post 
//
function tdomf_widget_content_post($args) {
  extract($args);
  $options = tdomf_widget_content_get_options();
  if($options['allowable-tags'] != "" && $options['restrict-tags']) {
    $post_content = strip_tags($content_content,$options['allowable-tags']);
  } else {
    $post_content = $content_content;
  }
  $post_content = 
  $post = array (
      "ID"             => $post_ID,
      "post_content"   => $post_content,
      "post_title"     => $content_title
  );
  $post_ID = wp_insert_post($post);
}
tdomf_register_form_widget_post('Content', 'tdomf_widget_content_post');

///////////////////////////////////////////////////
// Display and handle content widget control panel 
//
function tdomf_widget_content_control() {
  $options = tdomf_widget_content_get_options();
  // Store settings for this widget
    if ( $_POST['content-submit'] ) {
     $newoptions['title'] = strip_tags(stripslashes($_POST['content-title']));
     $newoptions['title-enable'] = isset($_POST['content-title-enable']);
     $newoptions['title-required'] = isset($_POST['content-title-required']);
     $newoptions['title-size'] = intval($_POST['content-title-size']); 
     $newoptions['text-enable'] = isset($_POST['content-text-enable']);
     $newoptions['text-required'] = isset($_POST['content-text-required']);
     $newoptions['text-cols'] = intval($_POST['content-text-cols']);
     $newoptions['text-rows'] = intval($_POST['content-text-rows']); 
     $newoptions['restrict-tags'] = isset($_POST['content-restrict-tags']);
     $newoptions['allowable-tags'] = $_POST['content-allowable-tags'];
     if ( $options != $newoptions ) {
        $options = $newoptions;
        update_option('tdomf_content_widget', $options);
        
     }
  }

   // Display control panel for this widget
  
  extract($options);

        ?>
<div>
<label for="content-title" style="line-height:35px;display:block;"><?php _e("Title: ","tdomf"); ?><input type="text" id="content-title" name="content-title" value="<?php echo $options['title']; ?>" /></label>

<h4><?php _e("Title of Post","tdomf"); ?></h4>
<label for="content-title-enable" style="line-height:35px;"><?php _e("Show","tdomf"); ?> <input type="checkbox" name="content-title-enable" id="content-title-enable" <?php if($options['title-enable']) echo "checked"; ?> ></label>
<label for="content-title-required" style="line-height:35px;"><?php _e("Required","tdomf"); ?> <input type="checkbox" name="content-title-required" id="content-title-required" <?php if($options['title-required']) echo "checked"; ?> ></label>
<label for="content-title-size" style="line-height:35px;"><?php _e("Size","tdomf"); ?> <input type="textfield" name="content-title-size" id="content-title-size" value="<?php echo $options['title-size']; ?>" size="3" /></label>

<h4><?php _e("Content of Post","tdomf"); ?></h4>
<label for="content-text-enable" style="line-height:35px;"><?php _e("Show","tdomf"); ?> <input type="checkbox" name="content-text-enable" id="content-text-enable" <?php if($options['text-enable']) echo "checked"; ?> ></label>
<label for="content-text-required" style="line-height:35px;"><?php _e("Required","tdomf"); ?> <input type="checkbox" name="content-text-required" id="content-text-required" <?php if($options['text-required']) echo "checked"; ?> ></label>
<br/>
<label for="content-text-cols" style="line-height:35px;"><?php _e("Cols","tdomf"); ?> <input type="textfield" name="content-text-cols" id="content-text-cols" value="<?php echo $options['text-cols']; ?>" size="3" /></label>
<label for="content-text-rows" style="line-height:35px;"><?php _e("Rows","tdomf"); ?> <input type="textfield" name="content-text-rows" id="content-text-rows" value="<?php echo $options['text-rows']; ?>" size="3" /></label>
<br/>
<label for="content-restrict-tags" style="line-height:35px;"><?php _e("Restrict Tags","tdomf"); ?> <input type="checkbox" name="content-restrict-tags" id="content-restrict-tags" <?php if($options['restrict-tags']) echo "checked"; ?> ></label>
<br/>
<label for="content-allowable-tags" style="line-height:35px;"><?php _e("Allowable Tags","tdomf"); ?> <textarea title="true" cols="30" name="content-allowable-tags" id="content-allowable-tags" ><?php echo $options['allowable-tags']; ?></textarea></label>
</div>
        <?php 
}
tdomf_register_form_widget_control('Content', 'tdomf_widget_content_control', 300, 400);

///////////////////////////////////////
// Validate title and content from form 
//
function tdomf_widget_content_validate($args) {
  $options = tdomf_widget_content_get_options();
  if(!$options['title-enable'] && !$options['text-enable']) { return ""; }  
  extract($args);
  $output = "";
  if($options['title-enable'] && $options['title-required']
       && (empty($content_title) || trim($content_title) == "")) {
      if($output != "") { $output .= "<br/>"; }
      $output .= __("You must specify a post title.","tdomf");
  }
  if($options['text-enable'] && $options['text-required']
       && (empty($content_content) || trim($content_content) == "")) {
      if($output != "") { $output .= "<br/>"; }
      $output .= __("You must specify some post text.","tdomf");
  }
  // return output if any
  if($output != "") {
    return $before_widget.$output.$after_widget;
  } else {
    return NULL;
  }
}
tdomf_register_form_widget_validate('Content', 'tdomf_widget_content_validate');

?>