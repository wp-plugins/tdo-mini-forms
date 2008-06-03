<?php
/*
Name: "Tags"
URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: This widget allows users to add tags to their submissions
Version: 1
Author: Mark Cunningham
Author URI: http://thedeadone.net
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

#TODO: Allow user to specify title, size and overwrite default tags
#TODO: Add option to core for default tags

// Only enable this widget if tags are avaliable in the installation
//
// TDOMF is only meant to support WP2.3... but ppl still ask for 2.2 support
// so this function call will disable this widget in 2.3
//
if(function_exists('wp_set_post_tags')) {

  //////////////////////////////
  // Add text field to display tags.
  //
  function tdomf_widget_tags($args) {
    extract($args);
    $output  = $before_widget;  
    $output .= '<label for="tags" >';
    $output .= __("Tags (separate multiple tags with commas: cats, pet food, dogs):","tdomf");
    $output .= '<br/><input type="textfield" id="tags" name="tags" size="60" value="'.htmlentities($tags,ENT_QUOTES,get_bloginfo('charset')).'" />';
    $output .= '</label>';
    $output .= $after_widget;
    return $output;
    }
  tdomf_register_form_widget('tags','Tags', 'tdomf_widget_tags', array("new-post"));
  
  ////////////////////////
  // Preview tags
  //
  function tdomf_widget_tags_preview($args) {
    extract($args);
  
    if(isset($tags) && !empty($tags)) {
      $output  = $before_widget;
      $output .= sprintf(__("<b>Post will be sumbmitted with these tags</b>:<br/>%s","tdomf"), strip_tags($tags));  
      $output .= $after_widget;
      return $output;
    }
    
    return "";
  }
  tdomf_register_form_widget_preview('tags','Tags', 'tdomf_widget_tags_preview', array("new-post"));
  
  
  ////////////////////////
  // Add tags to the post
  //
  function tdomf_widget_tags_post($args) {
    extract($args);
  
    if(isset($tags) && !empty($tags)) {
       # set last var to true to just append
       wp_set_post_tags($post_ID, strip_tags($tags),false);
    }
   
    return NULL;
  }
  tdomf_register_form_widget_post('tags','Tags', 'tdomf_widget_tags_post', array("new-post"));
  
  ///////////////////////////////////////////////////////////
  // Show what tags are on the post to admins for moderating
  //
  function tdomf_widget_tags_adminemail($args) {
    extract($args);
  
    $tags = wp_get_post_tags($post_ID);
    
    if(!empty($tags)) {
      $output  = $before_widget;
      $output .= __("Post tagged with\r\n","tdomf");
      foreach($tags as $tag) {
        $output .= $tag->name.", ";
      }
      $output .= $after_widget;
      return $output;
    }
    
    return "";
  }
  tdomf_register_form_widget_adminemail('tags','Tags', 'tdomf_widget_tags_adminemail', array("new-post"));

}

?>