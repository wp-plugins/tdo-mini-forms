<?php
/*
Plugin Name: TDO Mini Forms
Plugin URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: This plugin allows you to provide a form so that your registered and non-registered users can submit posts.</a>
Version: 0.9.3
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
//
// v0.71: 28th September 2007
// - Two small mistakes seemed to have wiggled into the files before 0.7
//     was released. Still getting the hang of SVN I guess.
//
// v0.72: 2nd October 2007
// - Date is not set when post is published. This was okay in WP2.2.
// - Comments are getting automatically closed (even if default is open).
//     This was okay in WP2.2.
// - widget.css in admin menu has moved in WP2.3. This is no longer compatible
//     with WP2.2.
// - Can now again select a default category for submissions and new submissions
//     will pick that category up. With WP2.3, tags and categories have changed
//     to a new "taxonomy" structure.
// - Added a "tdomf_widget_page" action hook
// - Fixed Widget page to work in WP2.3. WP2.3 now uses jQuery for a lot of its
//    javascript needs
// - If you happen to use as your database prefix "tdomf_", and then you
//    uninstall on WP2.3, it would delete critical options and bugger up
//    your wordpress install.
//
// v0.8: 12th October 2007
// - Upload Feature added
// - Widgets can now append information to the email sent to moderators
// - Tag Widget: allow submitters to add tags to their submissions
// - Categories Widget: First run of the categories widget.
//
// v0.9: 2nd November 2007
// - Updated Upload Files: if a file is added as attachment, Wordpress will
//    generate a thumbnail if the file is an image.
// - New Upload File Options: You can now automatically have a link added to
//    your post that goes to the attachment page (can even use the thumbnail
//    if it exists). Additionally, if the thumbnail exists, can insert a
//    direct link to file using the thumbnail.
// - Uploads added as attachments will inherit the categories of the post (but
//    remember the order of widgets is important so if the categories get
//    modified after the upload widget has done it's biz, these changes won't
//    be affected to the attachments)
// - More info and error checking!
// - "Notified" instead of "notify" in Notify Me widget
// - Added quicktags to the post "Content" widget (restrict tags option hides
//    illegal tags from toolbar)
// - Uninstall was broken! Was not deleting option settings.
// - Removed "About" menu
// - Added first draft of custom fields (only textfield and textarea supported)
// - Updated 1 Question Captcha and Categories widgets to support multiple
//    instances
// - Added a "Text" widget
// - Fixed a bug when deleting a post with uploaded files on PHP4 or less
//
// v0.9.1: 5th November 2007
// - Fixed a javascript error in Quicktags that blocked it from working on
//    Mozilla
// - Fixed the admin notification email as the Wordpress cache for the custom
//    fields for posts was being forgotten so the admin email did not contain
//    information about IP and uploaded files.
// - A define was missing from tdomf v0.9: TDOMF_KEY_DOWNLOAD_THUMB
// - Spelling mistake fixed in "Your Submissions"
//
// v0.9.2: 9th November 2007
// - Potential fix for the never-ending "session_start" problem. Using
//     template_redirect instead of get_header.
// - New Suppress Error Messages (works to a point)
// - Warnings about register_globals added
// - Fix for file uploads mkdir for windows included. Thansk to "feelexit" on
//     the TDOMF forums for the patch
// - "Latest Submissions" added to main Dashboard
// - Two widgets for your theme!
// - Fixed 1-q captcha widget not accepting quotes (")
//
// v0.9.3: 1st December 2007
// - Fixed customfield textfield control radio group in Firefox
// - Fixed customfield textfield ignoring size option
// - Fixed customfield textarea putting magic quotes on HTML
// - Fixed customfield textfield not handling HTML and quotes well.
// - Fixed customfield textfield not handling foreign characters well.
// - Fixed customfield textarea quicktag's extra button only working on post 
//     content's quicktag's toolbar
// - Updated customfield to optionally can automatically add value to post with
//     a user defined format
// - Removed any "short tag" versions (i.e. use "<?php" instead of "<?")
// - Add link to view post from moderator notification email
// - Auto add buttons to post content to "approve" or "reject" submission on the
//     spot
// - Enable/disable preview of customfield value
// - Added option to Upload Files widget to use direct links
// - Get phpinfo page
// - Conf dump page
// - Updated stylesheet to look nice in IE
// - Fixed borked thumbnails from v0.9
// - Fixed some issues with file uploading and safe_mode
// - New Option: Enable/Disable "Your Submissions" page
// - New Option: Enable extra debug log messages
// - Make the tags widget conditional on the existance of 'wp_set_post_tags'. 
//     This will improve backwards compatibility with Wordpress < 2.3 
//    (officially unsupported)
// - Category widget: Multiple category selection
// - Category widget: Display as list
// - Customfield now supports select and checkbox options
// - Added po file for translation
//
// v0.9.4: TBD
// - Added "getcat" widget
// - If moderation turned off, when post published, redirect to published post
//    page.
// - Fixed Custom Field widget javascript. Now works properly in Firefox.
// - Image Captcha Widget
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
// The never-ending TODO list
//
// - Bug: Text widget re-encodes HTML entities each form edit
// - Bug: Make all input fields handle foreign characters
// - Bug: invalid markup used in form elements!
// - Throttle number of submissions per "day" (hour/min) per "ip" (user)
// - Multiple form support
//    * Allow users to submit Pages
//    * Allow users to submit Links
//    * User groups (instead of roles)
// - More Template Tags
//    * Log
//    * Moderation Queue
//    * Approved Posts
//    * File Info
//    * Country codes on submitter's IP
// - Allow moderators append a message to the approved/rejected notification
//    (not that hard to do, store the message as a postmeta, notification code
//     grabs the message from postmeta, uses it and then deletes it from post
//     meta)
// - Allow admins to modify messages to user for...
//    * Submitted post awaiting moderation
//    * Submitted post automatically published
//    * Banned IP, Banned user and other insufficent priviliges messages
// - Widget Manager Menu
//    * Info about loaded widgets
//    * Disable loading of widgets
//    * Editor pane for widgets
// - Improvements for current widgets
//    * Upload Files: Multiple instances
//    * Upload Files: Thumbnail size options for upload files
//    * Upload Files: Limit size of image by w/h
//    * Content (and Custom Field): TinyMCE Integration
//    * Content (and Custom Field): Limit size of post
//    * Custom Fields: Radio groups, multiple checkboxs (grid-layout)
//    * 1 Question Captcha: Randoming questions for Captcha
//    * 1 Question Captcha: Validate at post time, not at preview
//    * Email verification of non-registered users
//    * Category: options for list size, width, include cats and multiple 
//        default categories
//    * Notify Me: Option to always notify submitter
// - Edit style-sheet for form inside TDOMF (possible have multiple styles)
// - Edit post support
//    * Unregistered user editing (lots of strange reprecussions here)
// - AJAX support (probably never)
// - Spam Protection
//    * Integration with Akismet
//    * SPAM button in moderation page
// - Force Preview (user must preview first before submission)
// - Allow newly submitted posts be set to "Post ready for review" with
//    Wordpress 2.3
// - A "manage download" menu
// - Documentation on creating your own widgets
// - Widget to determine category from post arguments (already implemented, 
//    needs to be tidied up)
// - Widget to popup text instead of statically presenting text
// - Allow users to define their own quicktags
// - Add "credits" for various places I pull source and other stuff from
// - Prevent plugin from being acitvate if register_globals is enabled
////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////
// Defines and other global vars for this Plugin //
///////////////////////////////////////////////////

// Older versions of PHP may not define DIRECTORY_SEPARATOR so define it here,
// just in case.
if(!defined('DIRECTORY_SEPARATOR')) {
  define('DIRECTORY_SEPARATOR','/');
}

// Build Number (must be a integer)
define("TDOMF_BUILD", "18");
// Version Number (can be text)
define("TDOMF_VERSION", "0.9.4");

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

/////////////////
// 0.8 Settings
//
define('TDOMF_UPLOAD_PERMS',0777);
define('TDOMF_UPLOAD_TIMEOUT',(60 * 60)); // 1 hour
define('TDOMF_KEY_DOWNLOAD_COUNT',"_tdomf_download_count_");
define('TDOMF_KEY_DOWNLOAD_TYPE',"_tdomf_download_type_");
define('TDOMF_KEY_DOWNLOAD_PATH',"_tdomf_download_path_");
define('TDOMF_KEY_DOWNLOAD_NAME',"_tdomf_download_name_");
define('TDOMF_KEY_DOWNLOAD_CMD_OUTPUT',"_tdomf_download_cmd_output_");

/////////////////
// 0.9 Settings
//
define('TDOMF_KEY_DOWNLOAD_THUMB',"_tdomf_download_thumb_");
define('TDOMF_OPTION_DISABLE_ERROR_MESSAGES',"tdomf_disable_error_messages");
define('TDOMF_OPTION_EXTRA_LOG_MESSAGES',"tdomf_extra_log_messages");
define('TDOMF_OPTION_YOUR_SUBMISSIONS',"tdomf_your_submissions");
define('TDOMF_WIDGET_URLPATH',TDOMF_URLPATH.'widgets/');

//////////////////////////////////////////////////
// loading text domain for language translation
//
load_plugin_textdomain('tdomf',PLUGINDIR.TDOMF_FOLDER);

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
    // Your submissions
    if(get_option(TDOMF_OPTION_YOUR_SUBMISSIONS)) {
      add_submenu_page('profile.php', 'Your Submissions', 'Your Submissions', 0, 'tdomf_your_submissions', 'tdomf_show_your_submissions_menu');
    }
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
require_once('admin'.DIRECTORY_SEPARATOR.'tdomf-edit-form.php');
require_once('admin'.DIRECTORY_SEPARATOR.'tdomf-log.php');
require_once('include'.DIRECTORY_SEPARATOR.'tdomf-form.php');
require_once('include'.DIRECTORY_SEPARATOR.'tdomf-notify.php');
require_once('admin'.DIRECTORY_SEPARATOR.'tdomf-moderation.php');
require_once('admin'.DIRECTORY_SEPARATOR.'tdomf-manage.php');
require_once('admin'.DIRECTORY_SEPARATOR.'tdomf-your-submissions.php');
require_once('admin'.DIRECTORY_SEPARATOR.'tdomf-uninstall.php');
require_once('include'.DIRECTORY_SEPARATOR.'tdomf-upload-functions.php');
require_once('include'.DIRECTORY_SEPARATOR.'tdomf-theme-widgets.php');

/////////////////////////
// Start/Init/Upgrade //
////////////////////////

function tdomf_init(){
  
  // Pre 0.7 or a fresh install!
  if(get_option(TDOMF_VERSION_CURRENT) == false)
  {
    add_option(TDOMF_VERSION_CURRENT,TDOMF_BUILD);

    // Some defaults for new options!
    add_option(TDOMF_OPTION_MODERATION,true);
    add_option(TDOMF_OPTION_PREVIEW,true);
    add_option(TDOMF_OPTION_TRUST_COUNT,-1);
    add_option(TDOMF_OPTION_YOUR_SUBMISSIONS,true);
  }

  // Pre 0.9.3 (beta)/16
  if(intval(get_option(TDOMF_VERSION_CURRENT)) < 16) {
    add_option(TDOMF_OPTION_YOUR_SUBMISSIONS,true);
  }
  
  // Update build number
  if(get_option(TDOMF_VERSION_CURRENT) != TDOMF_BUILD) {
    update_option(TDOMF_VERSION_CURRENT,TDOMF_BUILD);
  }
}

tdomf_load_widgets();

?>
