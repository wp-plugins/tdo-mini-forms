<?php
/*
Name: "Categories"
URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: This widget allows users to select categories for their submissions
Version: 0.3
Author: Mark Cunningham
Author URI: http://thedeadone.net
*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('TDOMF: You are not allowed to call this page directly.'); }

# TODO: display = list
# TODO: multi
# TODO: include

/////////////////////////////////////
// How many widgets do you want?
//
function tdomf_widget_categories_number_bottom(){
  $count = get_option('tdomf_categories_widget_count');
  if($count <= 0){ $count = 1; } 
  
  ?>
  <div class="wrap">
    <form method="post">
      <h2><?php _e("Categories Widgets","tdomf"); ?></h2>
      <p style="line-height: 30px;"><?php _e("How many Categories widgets would you like?","tdomf"); ?>
      <select id="tdomf-widget-categories-number" name="tdomf-widget-categories-number" value="<?php echo $count; ?>">
      <?php for($i = 1; $i < 10; $i++) { ?>
        <option value="<?php echo $i; ?>" <?php if($i == $count) { ?> selected="selected" <?php } ?>><?php echo $i; ?></option>
      <?php } ?>
      </select>
      <span class="submit">
        <input type="submit" value="Save" id="tdomf-widget-categories-number-submit" name="tdomf-widget-categories-number-submit" />
      </span>
      </p>
    </form>
  </div>
  <?php 
}
add_action('tdomf_widget_page_bottom','tdomf_widget_categories_number_bottom');

///////////////////////////////////////
// Initilise multiple category widgets!
//
function tdomf_widget_categories_init(){
  if ( $_POST['tdomf-widget-categories-number-submit'] ) {
    $count = $_POST['tdomf-widget-categories-number'];
    if($count > 0){ update_option('tdomf_categories_widget_count',$count); }
  }
  $count = get_option('tdomf_categories_widget_count');

  tdomf_register_form_widget("categories","Categories 1", 'tdomf_widget_categories',1);
  tdomf_register_form_widget_control("categories", "Categories 1",'tdomf_widget_categories_control', 350, 510, 1);
  tdomf_register_form_widget_preview("categories", "Categories 1",'tdomf_widget_categories_preview', true, 1);
  tdomf_register_form_widget_post("categories", "Categories 1",'tdomf_widget_categories_post', true, 1);
  tdomf_register_form_widget_adminemail("categories", "Categories 1",'tdomf_widget_categories_adminemail', true, 1);
  
  for($i = 2; $i <= $count; $i++) {
    tdomf_register_form_widget("categories-$i","Categories $i", 'tdomf_widget_categories',$i);
    tdomf_register_form_widget_control("categories-$i", "Categories $i",'tdomf_widget_categories_control', 350, 510, $i);
    tdomf_register_form_widget_preview("categories-$i", "Categories $i",'tdomf_widget_categories_preview', true, $i);
    tdomf_register_form_widget_post("categories-$i", "Categories $i",'tdomf_widget_categories_post', true, $i);
    tdomf_register_form_widget_adminemail("categories-$i", "Categories $i",'tdomf_widget_categories_adminemail', true, $i);
  }
}
tdomf_widget_categories_init();

// Get Options for this widget
//
function tdomf_widget_categories_get_options($number = 1) {
  $postfix = "";
  if($number != 1){ $postfix = "_$number"; }
  $options = get_option('tdomf_categories_widget'.$postfix);
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
// Display widget
//
function tdomf_widget_categories($args,$params) {
  $number = 1;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  $options = tdomf_widget_categories_get_options($number);
  $postfix = "";
  if($number != 1){ $postfix = "-$number"; }
  
  $defcat = get_option(TDOMF_DEFAULT_CATEGORY);
  if(isset($args["categories$postfix"])) {
    $defcat = $args["categories$postfix"];
  }

  extract($args);
  
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
    
    $output .= "<label for='categories$postfix'>Select a category: ";
    
    $catargs = array( 'exclude'          => $options['exclude'],
                      'hide_empty'       => false, 
                      'hierarchical'     => $options['hierarchical'], 
                      'echo'             => false,
                      'name'             => "categories$postfix",
                      'class'            => "tdomf_categories$postfix",
                      'selected'         => $defcat );
    $output .= wp_dropdown_categories($catargs);
    
    $output .= "</label>";
    
  } else {
    $output .= __("Not Yet Implemented.","tdomf");
  }
  /* dropdown_categories() (wp-admin/include/templates.php) */

  $output .= $after_widget;
  return $output;
  }

///////////////////////////////////////////////////
// Display and handle content widget control panel 
//
function tdomf_widget_categories_control($params) {
  $number = 1;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  $options = tdomf_widget_categories_get_options($number);
  $postfix1 = "";
  $postfix2 = "";
  if($number != 1){ 
    $postfix1 = "-$number"; 
    $postfix2 = "_$number";
  }
  // Store settings for this widget
    if ( $_POST["categories$postfix1-submit"] ) {
       $newoptions['title'] = strip_tags($_POST["categories$postfix1-title"]);
       $newoptions['overwrite'] = isset($_POST["categories$postfix1-overwrite"]);
       $newoptions['multi'] = isset($_POST["categories$postfix1-multi"]);
       $newoptions['hierarchical'] = isset($_POST["categories$postfix1-hierarchical"]);
       $newoptions['include'] = str_replace(' ', '', strip_tags($_POST["categories$postfix1-include"]));
       $newoptions['exclude'] = str_replace(' ', '',strip_tags($_POST["categories$postfix1-exclude"]));
       $newoptions['display'] = "dropdown";
     if ( $options != $newoptions ) {
        $options = $newoptions;
        update_option('tdomf_categories_widget'.$postfix2, $options);
        
     }
  }

   // Display control panel for this widget
   
        ?>
<div>

<p>A number of fields are disabled as they have not yet been implemented. They will be implemented in later releases of TDO Mini Forms</p>

<label for="categories<?php echo $postfix1; ?>-title" >
<?php _e("Title:","tdomf"); ?><br/>
<input type="textfield" size="40" id="categories<?php echo $postfix1; ?>-title" name="categories<?php echo $postfix1; ?>-title" value="<?php echo $options['title']; ?>" />
</label>
<br/><br/>

<label for="categories<?php echo $postfix1; ?>-overwrite">
<input type="checkbox" name="categories<?php echo $postfix1; ?>-overwrite" id="categories<?php echo $postfix1; ?>-overwrite" <?php if($options['overwrite']) { ?> checked <?php } ?> />
<?php _e("Overwrite Default Categories","tdomf"); ?>
</label>
<br/><Br/>

<label for="categories<?php echo $postfix1; ?>-multi">
<input type="checkbox" name="categories<?php echo $postfix1; ?>-multi" id="categories<?php echo $postfix1; ?>-multi" <?php if($options['multi']) { ?> checked <?php } ?> disabled />
<?php _e("Allow users to select more than one category","tdomf"); ?>
</label>
<br/><Br/>

<label for="categories<?php echo $postfix1; ?>-hierarchical">
<input type="checkbox" name="categories<?php echo $postfix1; ?>-hierarchical" id="categories<?php echo $postfix1; ?>-hierarchical" <?php if($options['hierarchical']) { ?> checked <?php } ?> />
<?php _e("Display categories in hierarchical mode","tdomf"); ?>
</label>
<br/><Br/>

<label for="categories<?php echo $postfix1; ?>-include" >
<?php _e("List of categories to include (leave blank for all) (separate multiple categories with commas: 0,2,3)","tdomf"); ?><br/>
<input type="textfield" size="40" id="categories<?php echo $postfix1; ?>-include" name="categories<?php echo $postfix1; ?>-include" value="<?php echo $options['include']; ?>" disabled />
</label>
<br/><br/>

<label for="categories<?php echo $postfix1; ?>-exclude" >
<?php _e("List of categories to exclude (separate multiple categories with commas: 0,2,3)","tdomf"); ?><br/>
<input type="textfield" size="40" id="categories<?php echo $postfix1; ?>-exclude" name="categories<?php echo $postfix1; ?>-exclude" value="<?php echo $options['exclude']; ?>" />
</label>
<br/><br/>

<label for"categories<?php echo $postfix1; ?>-display">
<?php _e("Display categtories as:","tdomf"); ?><br/>
<input type="radio" name="categories<?php echo $postfix1; ?>-display" id="categories<?php echo $postfix1; ?>-display" value="dropdown" checked><?php _e("Dropdown","tdomf"); ?><br>
<input type="radio" name="categories<?php echo $postfix1; ?>-display" id="categories<?php echo $postfix1; ?>-display" value="list" disabled><?php _e("List","tdomf"); ?><br>
</label>
<br/><br/>

</div>
        <?php 
}


////////////////////////
// Preview categories
//
function tdomf_widget_categories_preview($args,$params) {
  
  // TODO: message will be displayed as many times as there is category widgets
  
  $number = 1;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  $options = tdomf_widget_categories_get_options($number);
  $postfix1 = "";
  if($number != 1){ 
    $postfix1 = "-$number"; 
  }
  extract($args);
  $output  = $before_widget;
  $output .= sprintf(__("<b>This post will be categorized under</b>:<br/>%s","tdomf"), get_cat_name($args["categories$postfix1"]));  
  $output .= $after_widget;
  return $output;
}


////////////////////////
// Add categories to the post
//
function tdomf_widget_categories_post($args,$params) {
  $number = 1;
  if(is_array($params) && count($params) >= 1){
     $number = $params[0];
  }
  $options = tdomf_widget_categories_get_options($number);
  $postfix1 = "";
  if($number != 1){ 
    $postfix1 = "-$number"; 
  }
  extract($args);
  $options = tdomf_widget_categories_get_options();

  // Grab existing data
  $post = wp_get_single_post($post_ID, ARRAY_A);
  $current_cats = $post['post_category'];
  
  // Overwrite or append!
  if($options['overwrite']) {
    $post_cats = array( $args["categories$postfix1"] );
  } else {
    $post_cats = array_merge( $current_cats, array( $args["categories$postfix1"] ) );
  }

  // Update categories
  $post = array (
    "ID"            => $post_ID,
    "post_category" => $post_cats,
  );
  wp_update_post($post);
 
  return NULL;
}

///////////////////////////////////////////////////////////
// Show what categories are on the post to admins for moderating
//
function tdomf_widget_categories_adminemail($args,$params) {
  extract($args);

  // TODO: message will be displayed as many times as there is category widgets
  
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

?>