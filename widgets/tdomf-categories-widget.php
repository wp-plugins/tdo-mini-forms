<?php
/*
Name: "Categories"
URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: This widget allows users to select categories for their submissions
Version: 0.1
Author: Mark Cunningham
Author URI: http://thedeadone.net
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

# TODO: display = list
# TODO: multi
# TODO: include

// Get Options for this widget
//
function tdomf_widget_categories_get_options() {
  $options = get_option('tdomf_categories_widget');
    if($options == false) {
       $options = array();
       $options['title'] = '';
       $options['overwrite'] = false;
       $options['display'] = "dropdown";
       $options['multi'] = false;
       $options['hierarchical'] = true;
       $options['include'] = "";
       $options['exclude'] = "";
    }
  return $options;
}


//////////////////////////////
// 
//
function tdomf_widget_categories($args) {
  extract($args);
  $options = tdomf_widget_categories_get_options();
  
  $output  = $before_widget;  

  if(!empty($options['title'])) {
    $output .= $before_title.$options['title'].$after_title;
  }
  
  /* $defaults = array(
                'show_option_all' => '', 'show_option_none' => '',
                'orderby' => 'ID', 'order' => 'ASC',
                'show_last_update' => 0, 'show_count' => 0,
                'hide_empty' => 1, 'child_of' => 0,
                'exclude' => '', 'echo' => 1,
                'selected' => 0, 'hierarchical' => 0,
                'name' => 'cat', 'class' => 'postform'
        ); */
  if($options['display'] == "dropdown" ) {
    
    $output .= "<label for='categories'>Select a category: ";
    
    $catargs = array( 'exclude'          => $options['exclude'],
                      'hide_empty'       => false, 
                      'hierarchical'     => $options['hierarchical'], 
                      'echo'             => false,
                      'name'             => "categories",
                      'class'            => "tdomf_categories",
                      'selected'         => get_option(TDOMF_DEFAULT_CATEGORY));
    $output .= wp_dropdown_categories($catargs);
    
    $output .= "</label>";
    
  } else {
    $output .= __("Not Yet Implemented.","tdomf");
  }
  /* dropdown_categories() (wp-admin/include/templates.php) */

  $output .= $after_widget;
  return $output;
  }
tdomf_register_form_widget('Categories', 'tdomf_widget_categories');

///////////////////////////////////////////////////
// Display and handle content widget control panel 
//
function tdomf_widget_categories_control() {
  $options = tdomf_widget_categories_get_options();
  // Store settings for this widget
    if ( $_POST['categories-submit'] ) {
       $newoptions['title'] = strip_tags($_POST['categories-title']);
       $newoptions['overwrite'] = isset($_POST['categories-overwrite']);
       $newoptions['multi'] = isset($_POST['categories-multi']);
       $newoptions['hierarchical'] = isset($_POST['categories-hierarchical']);
       $newoptions['include'] = str_replace(' ', '', strip_tags($_POST['categories-include']));
       $newoptions['exclude'] = str_replace(' ', '',strip_tags($_POST['categories-exclude']));
       $newoptions['display'] = "dropdown";
     if ( $options != $newoptions ) {
        $options = $newoptions;
        update_option('tdomf_categories_widget', $options);
        
     }
  }

   // Display control panel for this widget
   
        ?>
<div>

<p>A number of fields are disabled as they have not yet been implemented. They will be implemented in later releases of TDO Mini Forms</p>

<label for="categories-title" >
<?php _e("Title:","tdomf"); ?><br/>
<input type="textfield" size="40" id="categories-title" name="categories-title" value="<?php echo $options['title']; ?>" />
</label>
<br/><br/>

<label for="categories-overwrite">
<input type="checkbox" name="categories-overwrite" id="categories-overwrite" <?php if($options['overwrite']) { ?> checked <?php } ?> />
<?php _e("Overwrite Default Categories","tdomf"); ?>
</label>
<br/><Br/>

<label for="categories-multi">
<input type="checkbox" name="categories-multi" id="categories-multi" <?php if($options['multi']) { ?> checked <?php } ?> disabled />
<?php _e("Allow users to select more than one category","tdomf"); ?>
</label>
<br/><Br/>

<label for="categories-hierarchical">
<input type="checkbox" name="categories-hierarchical" id="categories-hierarchical" <?php if($options['hierarchical']) { ?> checked <?php } ?> />
<?php _e("Display categories in hierarchical mode","tdomf"); ?>
</label>
<br/><Br/>

<label for="categories-include" >
<?php _e("List of categories to include (leave blank for all) (separate multiple categories with commas: 0,2,3)","tdomf"); ?><br/>
<input type="textfield" size="40" id="categories-include" name="categories-include" value="<?php echo $options['include']; ?>" disabled />
</label>
<br/><br/>

<label for="categories-exclude" >
<?php _e("List of categories to exclude (separate multiple categories with commas: 0,2,3)","tdomf"); ?><br/>
<input type="textfield" size="40" id="categories-exclude" name="categories-exclude" value="<?php echo $options['exclude']; ?>" />
</label>
<br/><br/>

<label for"categories-display">
<?php _e("Display categtories as:","tdomf"); ?><br/>
<input type="radio" name="categories-display" id="categories-display" value="dropdown" checked><?php _e("Dropdown","tdomf"); ?><br>
<input type="radio" name="categories-display" id="categories-display" value="list" disabled><?php _e("List","tdomf"); ?><br>
</label>
<br/><br/>

</div>
        <?php 
}
tdomf_register_form_widget_control('Categories', 'tdomf_widget_categories_control', 350, 510);


////////////////////////
// Preview categories
//
function tdomf_widget_categories_preview($args) {
  extract($args);
  $output  = $before_widget;
  $output .= sprintf(__("<b>This post will be categorized under</b>:<br/>%s","tdomf"), get_cat_name($categories));  
  $output .= $after_widget;
  return $output;
}
tdomf_register_form_widget_preview('Categories', 'tdomf_widget_categories_preview');


////////////////////////
// Add categories to the post
//
function tdomf_widget_categories_post($args) {
  extract($args);
  $options = tdomf_widget_categories_get_options();

  // Grab existing data
  $post = wp_get_single_post($post_ID, ARRAY_A);
  $current_cats = $post['post_category'];
  
  // Overwrite or append!
  if($options['overwrite']) {
    $post_cats = array( $categories );
  } else {
    $post_cats = array_merge( $current_cats, array( $categories ) );
  }

  // Update categories
  $post = array (
    "ID"            => $post_ID,
    "post_category" => $post_cats,
  );
  wp_update_post($post);
 
  return NULL;
}
tdomf_register_form_widget_post('Categories', 'tdomf_widget_categories_post');

///////////////////////////////////////////////////////////
// Show what categories are on the post to admins for moderating
//
function tdomf_widget_categories_adminemail($args) {
  extract($args);

  $cats_str = "";
  $cats = wp_get_post_categories($post_ID);
  foreach($cats as $cat) {
    $cats_str .= get_cat_name($cat);
  }
    
  extract($args);
  $output  = $before_widget;
  $output .= sprintf(__("This post will be categorized under:\r\n%s","tdomf"), $cats_str);  
  $output .= $after_widget;
  return $output;
}
tdomf_register_form_widget_adminemail('Categories', 'tdomf_widget_categories_adminemail');

?>