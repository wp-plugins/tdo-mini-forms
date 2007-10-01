<?php
/*
Plugin Name: TDO Mini Forms
Plugin URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: This plugin allows you to provide a form so that your registered and non-registered users can submit posts.</a>
Version: 0.71
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

////////////////////////////////////////////////////////////////////////////////
// Note for translators
//
// See http://codex.wordpress.org/Translating_WordPress for details of 
// translating wordpress plugins. All printed text is outputted using _e and __
// and the text domain "tdomf".
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
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
//
// v0.7: 26th September 2007
// - Overview page
// - Move the various admin pages to it's own submenu
// - Updated Edit Post Panel (uses built in AJAX-SACK)
// - Updated options menu
// - Code refactored, renamed files and restructured directories
// - Logging feature
// - Can uninstall the plugin completely. Also removes v0.6 unused options too
// - "Create Dummy User" link on options page
// - "Create Page with Form" from options page
// - Properly implemented form POST and dropped AJAX support
// - Can now automatically updates "the_author" template tag with submitter info
// - Can now automatically add "This post submitted by..." to end of post content
// - Bulk moderation of submitted posts, users and IPs
// - "Nonce" support for admin backend pages
// - "Your Submissions" page for all users. Form is included on this page.
// - Form should be XHTML valid (unless a new widget breaks it!)
// - Handle magic quotes properly
// - Allow YouTube embedded code to be posted, though this option is only 
//    allowable if moderation is enabled! Otherwise Wordpress' kses filters will
//    pull it out.
// - Reject Notifications as well as Approved Notifications
// - Can now restrict tags on posted content
// - New Template Tag: tdomf_can_current_user_see_form() returns true if current
//     user can access the form
// - Simple question-captcha widget: user must answer a simple question before
//     post will be accepted.
// - "I agree" widget: user must check a checkbox before post will be accepted.
// - TODO: Documenation: Help, About and Widgets
//
// v0.71: 28th September 2007
// - Two small mistakes seemed to have wiggled into the files before 0.7 
//     was released. Still getting the hang of SVN I guess.
//
// v0.72: TBD
// - Date is not set when post is published. This was okay in WP2.2.
// - Comments are getting automatically closed (even if default is open).
//     This was okay in WP2.2.
// - widget.css in admin menu has moved in WP2.3. This is no longer compatible 
//     with WP2.2.
// - TODO: Widget page is mucked up a bit!
// - TODO: "Remove Options"
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
// The never-ending TODO list
//
// - Allow moderators append a message to the approved/rejected notification
//    (not that hard to do, store the message as a postmeta, notification code
//     grabs the message from postmeta, uses it and then deletes it from post
//     meta)
// - Allow admis to modify messages to user for...
//    * Submitted post awaiting moderation
//    * Submitted post automatically published
//    * Banned IP, Banned user and other insufficent priviliges messages
// - Multiple copies of the same widget (it exists in the current WP theme
//    widget impl.)
// - Widget Manager Menu
//    * Info about loaded widgets
//    * Disable loaded widgets?
// - More and more widgets!
//    * File-uploading widget
//    * Custom Field widgets: one for each HTML element in the rainbow!
//    * Simple Text
//    * Select Category
//    * Tags
// - Improvements for current widgets
//    * More options
//    * QuickTags, TinyMCE, etc. for content box
// - Add/select custom styles for form
// - Multiple form support (very big)
// - Edit post support (surprisingly not that big)
//    * Unregistered user editing (lots of strange reprecussions here)
// - AJAX support (maybe, maybe not)
// - Spam Protection (is spam a problem, yet?)
//    * Integration with Spam Karma?
//    * Integration with Aksimet?
// - Force Preview (user must preview first before submission)
// - Allow newly submitted posts be set to "Post ready for review" with the 
//    Wordpress 2.3
////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////
// loading text domain for language translation
//
load_plugin_textdomain('tdomf','wp-content/plugins/tdomf');

///////////////////////////////////////////////////
// Defines and other global vars for this Plugin //
///////////////////////////////////////////////////

// Older versions of PHP may not define DIRECTORY_SEPARATOR so define it here, 
// just in case.
if(!defined('DIRECTORY_SEPARATOR')) {
  define('DIRECTORY_SEPARATOR','/');
}

// Build Number (must be a integer)
define("TDOMF_BUILD", "9");
// Version Number (can be text)
define("TDOMF_VERSION", "0.72");

///////////////////////////////////////
// 0.1 to 0.5 Settings (no longer used)
//
define("TDOMF_ACCESS_LEVEL", "tdomf_access_level");
define("TDOMF_NOTIFY_LEVEL", "tdomf_notify_level");

///////////////////////////////////////
// 0.6 Settings (no longer used)
//
define('TDOMF_NOTIFY','tdomf_notify');
define("TDOMF_ITEMS_PER_PAGE",30);
define("TDOMF_POSTS_INDEX",  0);
define("TDOMF_USERS_INDEX",  1);
define("TDOMF_IPS_INDEX",    2);
define("TDOMF_OPTIONS_INDEX",3);
//$tdomf_ajax_progress_icon = get_bloginfo("wpurl")."/wp-content/plugins/tdomf/ajax-loader.gif";

/////////////////
// 0.6 Settings
//
define("TDOMF_ACCESS_ROLES", "tdomf_access_roles");
define("TDOMF_NOTIFY_ROLES", "tdomf_notify_roles");
define("TDOMF_DEFAULT_CATEGORY", "tdomf_default_category");
define("TDOMF_DEFAULT_AUTHOR", "tdomf_default_author");
define("TDOMF_AUTO_FIX_AUTHOR", "tdomf_auto_fix_author");
define("TDOMF_BANNED_IPS", "tdomf_banned_ips");
//
// These keys are used to store info about a post, on the post.
// Keys with underscore prefix (i.e. "_") are hidden from the general user. Keys
// without can be modified and displayed using Wordpress normal features such as
// template tags and custom fields editor.
//
define("TDOMF_KEY_FLAG","_tdomf_flag");
define("TDOMF_KEY_NAME","Author Name");
define("TDOMF_KEY_EMAIL","Author Email");
define("TDOMF_KEY_WEB","Author Webpage");
define("TDOMF_KEY_IP","_tdomf_original_poster_ip");
define("TDOMF_KEY_USER_ID","_tdomf_original_poster_id");
define("TDOMF_KEY_USER_NAME","Original Submitter Username");
//
// This key is very important. It determines if a user is trusted or banned..
//
define("TDOMF_KEY_STATUS","_tdomf_status");


/////////////////
// 0.7 Settings
//
define('TDOMF_FOLDER', dirname(plugin_basename(__FILE__)));
define('TDOMF_FULLPATH', ABSPATH.PLUGINDIR.'/'.TDOMF_FOLDER.'/');
define('TDOMF_URLPATH', get_option('siteurl').'/wp-content/plugins/'.TDOMF_FOLDER.'/');
define('TDOMF_WIDGET_PATH',TDOMF_FULLPATH.'widgets/');
define('TDOMF_VERSION_CURRENT', "tdomf_version_current");
define('TDOMF_LOG', "tdomf_log");
define('TDOMF_OPTION_MODERATION', "tdomf_enable_moderation");
define('TDOMF_OPTION_TRUST_COUNT', "tdomf_trust_count");
define('TDOMF_OPTION_ALLOW_EVERYONE', "tdomf_allow_everyone");
define('TDOMF_OPTION_AJAX', "tdomf_ajax");
define('TDOMF_OPTION_PREVIEW', "tdomf_preview");
define('TDOMF_OPTION_FROM_EMAIL', "tdomf_from_email");
define('TDOMF_OPTION_AUTHOR_THEME_HACK', "tdomf_author_submitter");
define('TDOMF_OPTION_FORM_ORDER', "tdomf_form_order");
define('TDOMF_OPTION_ADD_SUBMITTER', "tdomf_add_submitter_info");
define('TDOMF_CAPABILITY_CAN_SEE_FORM', "tdomf_can_see_form");
define('TDOMF_STAT_SUBMITTED', "tdomf_stat_submitted");
define('TDOMF_USER_STATUS_OK', "Normal");
define('TDOMF_USER_STATUS_BANNED', "Banned");
define('TDOMF_USER_STATUS_TRUSTED', "Trusted");
define('TDOMF_KEY_NOTIFY_EMAIL', "_tdomf_notify_email");

///////////////////////////////////
// Configure Backend Admin Menus //
///////////////////////////////////

add_action('admin_menu', 'tdomf_add_menus');
function tdomf_add_menus()
{
    add_menu_page(__('TDOMF', 'tdomf'), __('TDOMF', 'tdomf'), 'edit_others_posts', TDOMF_FOLDER, 'tdomf_overview_menu');

    // Options
    add_submenu_page( TDOMF_FOLDER , __('Options', 'tdomf'), __('Options', 'tdomf'), 'manage_options', 'tdomf_show_options_menu', 'tdomf_show_options_menu');
    //
    // Generate Form
    add_submenu_page( TDOMF_FOLDER , __('Widgets', 'tdomf'), __('Widgets', 'tdomf'), 'manage_options', 'tdomf_show_form_menu', 'tdomf_show_form_menu');
    //
    // Moderation Queue
    if(get_option(TDOMF_OPTION_MODERATION)) {
       add_submenu_page( TDOMF_FOLDER , __('Moderation', 'tdomf'), sprintf(__('Awaiting Moderation (%d)', 'tdomf'), tdomf_get_unmoderated_posts_count()), 'edit_others_posts', 'tdomf_show_mod_posts_menu', 'tdomf_show_mod_posts_menu');
    }
    else {
      add_submenu_page( TDOMF_FOLDER , __('Moderation', 'tdomf'), __('Moderation Disabled', 'tdomf'), 'edit_others_posts', 'tdomf_show_mod_posts_menu', 'tdomf_show_mod_posts_menu');
    }
    //
    // Manage Submitters
    add_submenu_page( TDOMF_FOLDER , __('Manage', 'tdomf'), __('Manage', 'tdomf'), 'edit_others_posts', 'tdomf_show_manage_menu', 'tdomf_show_manage_menu');
    //
    // Log
    add_submenu_page( TDOMF_FOLDER , __('Log', 'tdomf'), __('Log', 'tdomf'), 'manage_options', 'tdomf_show_log_menu', 'tdomf_show_log_menu');
    //
    // Uninstall
    add_submenu_page( TDOMF_FOLDER , __('Uninstall', 'tdomf'), __('Uninstall', 'tdomf'), 'manage_options', 'tdomf_show_uninstall_menu', 'tdomf_show_uninstall_menu');
    //
    // About Page
    add_submenu_page( TDOMF_FOLDER , __('About', 'tdomf'), __('About', 'tdomf'), 'edit_others_posts', 'tdomf_show_about_page', 'tdomf_show_about_page');
    
    //
    // Your submissions
    add_submenu_page('profile.php', 'Your Submissions', 'Your Submissions', 0, 'tdomf_your_submissions', 'tdomf_show_your_submissions_menu');
}

//////////////////////////////////
// Load the rest of the plugin! //
//////////////////////////////////

require_once('include'.DIRECTORY_SEPARATOR.'tdomf-log-functions.php');
require_once('include'.DIRECTORY_SEPARATOR.'tdomf-hacks.php');
require_once('include'.DIRECTORY_SEPARATOR.'tdomf-widget-functions.php');
require_once('include'.DIRECTORY_SEPARATOR.'tdomf-template-functions.php');
require_once('admin'.DIRECTORY_SEPARATOR.'tdomf-overview.php');
require_once('admin'.DIRECTORY_SEPARATOR.'tdomf-edit-post-panel.php');
require_once('admin'.DIRECTORY_SEPARATOR.'tdomf-options.php');
require_once('admin'.DIRECTORY_SEPARATOR.'tdomf-about.php');
require_once('admin'.DIRECTORY_SEPARATOR.'tdomf-edit-form.php');
require_once('admin'.DIRECTORY_SEPARATOR.'tdomf-log.php');
require_once('include'.DIRECTORY_SEPARATOR.'tdomf-form.php');
require_once('include'.DIRECTORY_SEPARATOR.'tdomf-notify.php');
require_once('admin'.DIRECTORY_SEPARATOR.'tdomf-moderation.php');
require_once('admin'.DIRECTORY_SEPARATOR.'tdomf-manage.php');
require_once('admin'.DIRECTORY_SEPARATOR.'tdomf-your-submissions.php');
require_once('admin'.DIRECTORY_SEPARATOR.'tdomf-uninstall.php');

/////////////////////////
// Start/Init/Upgrade //
////////////////////////

function tdomf_init(){
  // Pre 0.7
  if(get_option(TDOMF_VERSION_CURRENT) == false)
  {
    add_option(TDOMF_VERSION_CURRENT,TDOMF_BUILD);
    
    // Some defaults for new options!
    add_option(TDOMF_OPTION_MODERATION,true);
    add_option(TDOMF_OPTION_PREVIEW,true);
  }
}

tdomf_load_widgets();

?>
