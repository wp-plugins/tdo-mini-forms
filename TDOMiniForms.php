<?php
/*
Plugin Name: TDO Mini Forms
Plugin URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: This plugin allows you to provide a form so that your registered and non-registered users can submit posts. You can configure who can post and other details via the options and manage menus. <a href="options-general.php?page=TDOMiniForms/OptionsMenu.php">Configure Plugin.</a>
Version: 0.6
Author: Mark Cunningham
Author URI: http://thedeadone.net
*/

/*  Copyright 2006  Mark Cunningham  (email : Mark.Cunningham@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Known Bugs:
//
//FIXME: Incompatibility with the "Bad Behaviour" Wordpress plugin
//FIXME: CSS code being erased from published posts when submitter is used as author (more information required please!!)
//
// Planned Features (in no particular order)
// 
//TODO: Options Menu: Allow complete customization of text
//TODO: Tidy up HTML/CSS of form
//TODO: Custom Fields: textfields, textareas, checkboxes and radio groups
//TODO: Non-AJAX version of submit form
//TODO: File attachment/Upload
//
// Potential Future Features:
//
//TODO: Manage Menu: Bulk post moderation.
//TODO: Reject Post option. Additionally send email to submitter with reason why.
//TODO: Button to add form to post/page (this is an issue currently in WP2.1.x).
//TODO: Options Menu: More than one default category.
//TODO: Options Menu: AJAX loading icon.
//TODO: Options Menu: Configurable size of content form.
//TODO: Multiple configurable forms.
//TODO: Preview Post before submission.
//TODO: Allow submitters to "edit" their submissions.
//TODO: Allow submitters to claim their posts.
//TODO: Automatically set a submitter/user to the "trust" state after a configurable number of posts have been published.
//TODO: Custom Fields: Allow submitter to select the categories for the post.
//TODO: Custom Fields: Fields for "Extract" and optionally split Content into two fields.
//TODO: Custom Fields: Integration with Ultimate Tag Master.
//TODO: Configurable order and grouping (fieldsets) of form elements.
//TODO: Spam protection: Integration with Akismet and Wordpress' IP ban list.
//TODO: Role Management: Use role capabilities to manage who can access the form and who are submission moderators. Currently, the plugin keeps a list of applicable roles. This would the correct way to integrate with Wordpress' roles.
//
// Version History
//
// Preview: 21 November 2006
// - Preview Release, only on wordpress.org/support forums.
//
// v0.1: 22 November 2006
// - Initial Release with basic features
// 
// v0.2: 29 November 2006
// - Fixed bug: If default author had rights to post, anon posts would be automatically published.
// - Replaced the word "download" used in messages to the user.
// - Added a "webpage" field when posting anonymously.
//
// v0.3: 6 March 2007
// - Ported to Wordpress 2.1.x
//
// v0.4: 9 March 2007
// - New template tags: tdomf_get_submitter and tdomf_the_submitter.
// - The plugin should work on Windows based servers
// - A TDOMF panel on the edit post page
// - Posts can now be very long (no 250 word limit)
//
// v0.5: 15 March 2007
// - Tested on Windows based host
// - Chinese text does not get mangled
// - Post Edit Panel now works properly on Firefox.
//
// v0.6: 20 March 2007
// - Options Menu: Control access to form based on roles
// - Options Menu: Control who gets notified to approve posts by role.
// - Options Menu: Default author is now chosen by login name instead of username
// - Javascript code only included as necessary (i.e. not in every header)

// You can change this to use a different icon for AJAX in progress
$tdomf_ajax_progress_icon = get_bloginfo("wpurl")."/wp-content/plugins/TDOMiniForms/ajax-loader.gif";

// Do not modify anything below this line unless you know exactly what you 
// are doing!

// loading text domain for language translation
load_plugin_textdomain('tdomf','wp-content/plugins/TDOMiniForms');

// version
define("TDOMF_VERSION", "v0.6");

// Old settings
define("TDOMF_ACCESS_LEVEL", "tdomf_access_level");
define("TDOMF_NOTIFY_LEVEL", "tdomf_notify_level");

// Settings
define("TDOMF_ACCESS_ROLES", "tdomf_access_roles");
define("TDOMF_NOTIFY_ROLES", "tdomf_notify_roles");
define("TDOMF_DEFAULT_CATEGORY", "tdomf_default_category");
define("TDOMF_DEFAULT_AUTHOR", "tdomf_default_author");
define("TDOMF_AUTO_FIX_AUTHOR", "tdomf_auto_fix_author");
define("TDOMF_BANNED_IPS", "tdomf_banned_ips");

define("TDOMF_KEY_FLAG","_tdomf_flag");
define("TDOMF_KEY_NAME","Author Name");
define("TDOMF_KEY_EMAIL","Author Email");
define("TDOMF_KEY_WEB","Author Webpage");
define("TDOMF_KEY_IP","_tdomf_original_poster_ip");
define("TDOMF_KEY_USER_ID","_tdomf_original_poster_id");
define("TDOMF_KEY_USER_NAME","Original Submitter Username");
define("TDOMF_STATUS","_tdomf_status");

// Notify key (used to notify poster when post is published)-
define('TDOMF_NOTIFY','tdomf_notify');

// Manage page
define("TDOMF_ITEMS_PER_PAGE",30);

// Index of which menu has been selected
define("TDOMF_POSTS_INDEX",0);
define("TDOMF_USERS_INDEX",1);
define("TDOMF_IPS_INDEX",2);
define("TDOMF_OPTIONS_INDEX",3);

// Older versions of PHP may not define DIRECTORY_SEPARATOR so define it here,
// just in case.
if(!defined('DIRECTORY_SEPARATOR')) {
  define('DIRECTORY_SEPARATOR','/');
}

// initilise plugin
function tdomf_init(){
  if(get_option(TDOMF_NOTIFY_LEVEL) != false 
   || false == get_option(TDOMF_NOTIFY_ROLES))
  {
     global $wp_roles;
	  if (!isset($wp_roles)) { $wp_roles = new WP_Roles(); }
	  $roles = $wp_roles->roles;
	  foreach($roles as $role) { 
        if(isset($role['capabilities']['edit_others_posts'])
           && isset($role['capabilities']['publish_posts'])) {
           add_option(TDOMF_NOTIFY_ROLES,$role['name'].';',"What roles to notify when post submitted via the form");
           break;
		  }
	  }
  }
  if(false == get_option(TDOMF_DEFAULT_CATEGORY)) {
    add_option(TDOMF_DEFAULT_CATEGORY,"1","Default Category");
  }
  if(false == get_option(TDOMF_DEFAULT_AUTHOR)) {
    add_option(TDOMF_DEFAULT_AUTHOR,"1","Default Author");
  }
  if(false == get_option(TDOMF_AUTO_FIX_AUTHOR)) {
    add_option(TDOMF_AUTO_FIX_AUTHOR,true,"Auto-correct author");
  }
  // TDOMF_ACCESS_ROLE does not need to exist
}

// generate the header for the menus
function tdomf_show_menu_header($selected_index = TDOMF_POSTS_INDEX){ ?>
  <div class="wrap">
  <form method="post" action="<?php echo $_SERVER[REQUEST_URI]; ?>">
    <h2><?php echo __('TDO Mini Forms',"tdomf").' '.TDOMF_VERSION; ?></h2>
      <ul id="tdomf_navlist" >
        <li <?php if($selected_index == TDOMF_POSTS_INDEX){ ?> id="active" <?php } ?> >
        <a href="edit.php?page=TDOMiniForms<?php echo DIRECTORY_SEPARATOR; ?>ManageMenu.php&mode=posts" <?php if($selected_index == TDOMF_POSTS_INDEX){ ?> id="tdomf_current" <?php } ?> >
            <?php _e("Posts","tdomf"); ?></a></li>
        <li <?php if($selected_index == TDOMF_USERS_INDEX){ ?> id="active" <?php } ?>>
          <a href="users.php?page=TDOMiniForms<?php echo DIRECTORY_SEPARATOR; ?>ManageMenu.php" <?php if($selected_index == TDOMF_USERS_INDEX){ ?> id="tdomf_current" <?php } ?> >
            <?php _e("Users","tdomf"); ?></a></li>
        <li <?php if($selected_index == TDOMF_IPS_INDEX){ ?> id="active" <?php } ?>>
          <a href="edit.php?page=TDOMiniForms<?php echo DIRECTORY_SEPARATOR; ?>ManageMenu.php&mode=ips" <?php if($selected_index == TDOMF_IPS_INDEX){ ?> id="tdomf_current" <?php } ?> >
            <?php _e("IP Address","tdomf"); ?></a></li>
        <li <?php if($selected_index == TDOMF_OPTIONS_INDEX){ ?> id="active" <?php } ?> >
         <a href="options-general.php?page=TDOMiniForms<?php echo DIRECTORY_SEPARATOR; ?>OptionsMenu.php" <?php if($selected_index == TDOMF_OPTIONS_INDEX){ ?> id="tdomf_current" <?php } ?> >
         <?php _e("Configure","tdomf"); ?></a></li>
        </ul>
<?php }

// generate the footer
function tdomf_show_menu_footer(){ ?>
  </div>
<?php }

// add styles to admin menu for tabs in management and options
function tdomf_admin_header() {
  ?>
    <!-- tabbed code http://css.maxdesign.com.au/listamatic/horizontal05.htm -->
    <style>
      #tdomf_navlist
      {
        padding: 3px 0;
        margin-left: 0;
        border-bottom: 1px solid #778;
        font: bold 12px Verdana, sans-serif;
      }
      #tdomf_navlist li
      {
        list-style: none;
        margin: 0;
        display: inline;
      }
      #tdomf_navlist li a
      {
        padding: 3px 0.5em;
        margin-left: 3px;
        border: 1px solid #778;
        border-bottom: none;
        background: #DDE;
        text-decoration: none;
      }
      #tdomf_navlist li a:link { color: #448; }
      #tdomf_navlist li a:visited { color: #667; }
      #tdomf_navlist li a:hover
      {
        color: #000;
        background: #AAE;
        border-color: #227;
      }
      #tdomf_navlist li a#tdomf_current
      {
        background: white;
        border-bottom: 2px solid white;
      }
    </style>
<?php }
add_action("admin_head","tdomf_admin_header");

// there is a "bug" in wordpress if you publish a post using the
// edit menu, the author cannot be a user so if your user is a subscriber,
// it will become the person who published it. This is the only way to
// fix it without hacking the code base
function tdomf_auto_fix_authors() {
  global $wpdb;
  if(get_option(TDOMF_AUTO_FIX_AUTHOR)) {
    // grab posts
    $query = "SELECT ID, post_author, meta_value ";
    $query .= "FROM $wpdb->posts ";
    $query .= "LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
    $query .= "WHERE meta_key = '".TDOMF_KEY_USER_ID."' ";
    $query .= "AND meta_value != post_author ";
    $query .= "ORDER BY ID DESC";
    $posts = $wpdb->get_results( $query );
    if(!empty($posts)) {
      echo "<!-- tdomf: fixing posts ";
      $count = 0;
      foreach($posts as $post) {
        if($post->meta_value != $post->post_author && !empty($post->meta_value) && $post->meta_value > 0 ) {
          $count++;
          echo $post->ID.", ";
          $postargs = array (
            "ID"             => $post->ID,
            "post_author"    => $post->meta_value,
          );
          wp_update_post($postargs);
        }
      }
      echo " -->";
      return $count;
    } else {
      echo "<!-- tdomf: no posts to fix! -->";
      return 0;
    }
  }
  return false;
}

// AJAX library
require_once('Sajax.php');
// Use "post" instead of "get" so we can send *long* posts!
$sajax_request_type = "POST";
// Set to 1 to enable SAJAX debug
$sajax_debug_mode = 0;
sajax_init();

// add common stuff to header!
function tdomf_header() {
  // Is this a good place to do it?
  tdomf_auto_fix_authors();
  
  // use to have other stuff here! 
}
add_action("wp_head","tdomf_header");

// Go...
tdomf_init();
// Options Menu
require_once('OptionsMenu.php');
// Manage Menu
require_once('ManageMenu.php');
// The Form
require_once('Form.php');
 
// now initialise the AJAX
sajax_handle_client_request();

// template functions
function tdomf_get_the_submitter($post_id = 0){
  global $post;
  if($post_id == 0 && isset($post)) { $post_id = $post->ID; }
  else if($post_id == 0){ return ""; }
  
  $flag = get_post_meta($post_id, TDOMF_KEY_FLAG, true);
  if(!empty($flag)) {
     $submitter_user_id = get_post_meta($post_id, TDOMF_KEY_USER_ID, true);
     if(!empty($submitter_user_id) && $submitter_user_id != get_option(TDOMF_DEFAULT_AUTHOR)) {
        $user = get_userdata($submitter_user_id);
        if(isset($user)) {
          $retValue = "";
          // bit of a crappy hack to make sure that if it's only "http://" it isn't printed
          $web_url = trim($user->user_url);
          if(strlen($web_url) < 8 || strpos($web_url, "http://", 0) !== 0 ) {
            $web_url = "";
          }
          if(!empty($web_url)) {
            $retValue .= "<a href=\"$web_url\" rel=\"nofollow\">";
          }
          $retValue .= $user->display_name;
          if(!empty($web_url)) {
            $retValue .= "</a>";
          }
          return $retValue;
        } else {
          #return "{ ERROR: bad submitter id for this post }";
          return "";
        }
     } else {
        $submitter_web = get_post_meta($post_id, TDOMF_KEY_WEB, true);
        $submitter_name = get_post_meta($post_id, TDOMF_KEY_NAME, true);
        if(empty($submitter_name)) {
          #return "{ ERROR: no submitter name set for this post }";
          return "";
        } else {
          $retValue = "";
          $web_url = trim($submitter_web);
          if(strlen($web_url) < 8 || strpos($web_url, "http://") !== 0) {
            $web_url = "";
          }
          if(!empty($web_url)) {
            $retValue .= "<a href=\"$web_url\" rel=\"nofollow\">";
          }
          $retValue .= $submitter_name;
          if(!empty($web_url)) {
            $retValue .= "</a>";
          }
          return $retValue;
        }
     }
  }
  else {
    return "";
  }
}
function tdomf_the_submitter($post_id = 0){
  echo tdomf_get_the_submitter($post_id);
}

// grab a list of user ids of users that have submitted a post
function tdomf_get_all_users() {
    global $wpdb;
    $query = "SELECT * ";
    $query .= "FROM $wpdb->users ";
    $query .= "ORDER BY ID DESC";
    return $wpdb->get_results( $query );
}

// edit post panel
require_once('EditPostPanel.php');

?>
