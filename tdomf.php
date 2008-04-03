<?php
/*
Plugin Name: TDO Mini Forms
Plugin URI: http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/
Description: This plugin allows you to add custom posting forms to your website that allows your readers (including non-registered) to submit posts.
Version: 0.10.2
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
// v0.9.4: 7th Janary 2008
// - Added "getcat" widget
// - If moderation turned off, when post published, redirect to published post
//    page.
// - Fixed Custom Field widget javascript. Now works properly in Firefox.
// - Image Captcha Widget
// - Updated all text fields input (and output) to use htmlentities. Hopefully
//    this will cure foreign character input/output issues and weird
//    re-encoding issues with widget settings.
// - Word count or character limit on post content
// - Theme Widget that displays the form!
// - Add "credits" to readme.txt for various places I pull source and other
//    stuff from
// - Added a "Read More..." <!--more--> tag to the quick tags
// - Fixed Bug when multiple notifications to submitter when post is edited
//    after approval
//
// v0.10: 12th Feburary 2007
// - Suppressed errors for is_dir and other file functions just in case of
//    open_basedir settings!
// - Use "get_bloginfo('charset')" in htmlentities in widget control. Hopefully
//    this will finally resolve the issues with foreign lanaguage characters
// - Multiple Form Support
// - Widgets that validate know if it's for preview or post. Certain validation
//    should only occur at post like captcha and "who am I" info for example.
// - Option to specify the max number of instances of a multi-instant widget per
//    form
// - Can now set a form to submit pages instead of posts.
// - Fixed a bug where customfield textfield would submit empty values for the
//    custom field if you had magic quotes turned off.
// - Update the "Freecap" Image Captcha so that the files get included in
//    the release zip Wordpress creates.
//
// v0.10.1: 13th March 2007
// - Fixed a bug when if you inserted an upload as an attachment it would
//     overwrite the contents of the post.
// - Fix to categories widget where widget on other forms than the default
//     would forget it's settings at post time.
// - Custom Field widget was ignoring append format for multi-forms
//
// v0.10.2: 2nd April 2007
// - Fixed a bug if you reload the image capatcha, it would not longer verify
// - Added a flag TDOMF_HIDE_REGISTER_GLOBAL_ERROR in tdomf.php that can be set
//     to true to hide the register_global errors that get displayed.
// - WP2.5 only: Can now set a max width or height for widgets control on the 
//     Form Widgets screen.
// - Compatibily with Wordpress 2.5
//
////////////////////////////////////////////////////////////////////////////////

/*
////////////////////////////////////////////////////////////////////////////////
TODO for future versions

Known Bugs
- Invalid markup is used in several form elements

Spam Protection
- Integration with Akismet
- Spam Button on moderation page
- Throttle number of submissions per day (or hour/min) per ip (or user) (should probably be able to add several rules)

New Features
- Allow moderators append a message to the approved/rejected notification (allows communication between submitter and moderator)
- Widget Manager Menu
  * Info about loaded widgets
  * Disable loading of specific widgets
- File Editor (edit TDOMF files from within admin UI)
  * Widgets
  * TDOMF Core Files (with usual warnings)
  * Restore
- Style Sheet Editor
  * Preview
  * Select from pre-configured Styles
  * Submit new style to include in TDOMF
- Email verification of non-registered users
- AJAX support
- Edit Posts
  * Using same/similar form as what the post was submitted with
  * Create Edit-Post only forms
  * Allow various controls and access for forms: per category and by access roles
  * Editing Post implies adding/removing comments too (can replace comment submission form)
  * Unregistered user editing (requires some sort of magic code)
- Manage Downloads page
- Option to display the moderation menu like the "comment moderation" page (i.e. with little extracts of the posts/pages)
- Get input during validation of form (for capatchas)
- Option to use "Post ready for review" instead of draft for unapproved submitted posts
- On Options and Widgets Page, set the "title" of the Form links to the given title of the form
- Turn Forms into multiple steps

New Form Options
- Force Preview before submission
- Hide Form on Preview
- Forms can be used to submit links
- Select Form Style/include Custom CSS
- Control who can access form not just by role but also by user, ip and capability.
- Modify error messages for form
  * Submitted post awaiting moderation
  * Banned X and other insufficent priviliges messages
- Prevent plugin from being activate if register_globals is enabled (if we can do AJAX, this may not be an issue)

New Widgets
- Widget to allow users to enable/disable comments and trackbacks on their submission
- Widget to allow user to enter a date to post the submission (as in future post)
- Widget that inputs only title

Existing Widget Improvements
- Make Widget-Form menu independant of Wordpress code (the current code will break in Wordpress 2.5)
- Any widget with a size or length field should be customisable.
- Any static text used in widgets need to be customisable.
- Fixed sizes for Widget Control windows
- Copy Widget to another Form
- Upload Files
  * Multiple Instances
  * Thumbnail size
  * Limit size of image by w/h
  * Image cropping
  * Title field for file links/attachment pages
  * Nicer integration: background uploading using iframe
  * Prevent submission until files uploaded
  * Progress bar
- Content
  * TinyMCE Integration
  * Allow users to define their own quicktags
  * Mechanism to allow sumitter to select where the link/image for upload should go
  * Default Value
- Custom Field: Textarea
  * TinyMCE Integration
  * Allow users to define their own quicktags
  * Mechanism to allow sumitter to select where the link/image for upload should go
  * Default Value
- Custom Field
  * Radio Groups
  * Multiple Checkboxes (grid-layout)
- Custom Field: Textfield
  * Numeric
  * Date
- Custom Field: Select
  * Required support
- Tags
  * Select from existing tag list or tag cloud
  * Hide if form is for pages
- 1 Question Captcha
  * Random questions for Captcha
- Category
  * Include specific categories
  * Multiple default categories
  * Co-operate with "Set Category from GET variables" Widget
  * Hide if form is for pages
- Notify Me
  * Option to always notify submitter
- Image Captcha
  * Do not reload image on every preview (would be resolved by a seperate validation step)
- Text
  * Option to not use the form formatting (i.e. no "<fieldset>" tags before and after)
  * Option to have the text popup (would require a HTML space for the link)
- Set Category from GET variables
  * Add options (or at least information) for this widget
  * Co-operate with "Categories" Widget
  * Hide if form is for pages
- Who Am I
  * Integration with WP-OpenID

Template Tags
- Log
- Moderation Queue
- Approved Posts
- File Info
- Country codes on submitter's IP

Misc
- Documentation on creating your own widgets

////////////////////////////////////////////////////////////////////////////////
*/

///////////////////////////////////////////////////
// Defines and other global vars for this Plugin //
///////////////////////////////////////////////////

// Older versions of PHP may not define DIRECTORY_SEPARATOR so define it here,
// just in case.
if(!defined('DIRECTORY_SEPARATOR')) {
  define('DIRECTORY_SEPARATOR','/');
}

// Build Number (must be a integer)
define("TDOMF_BUILD", "26");
// Version Number (can be text)
define("TDOMF_VERSION", "0.10.1");

///////////////////////////////////////
// 0.1 to 0.5 Settings (no longer used)
//
define("TDOMF_ACCESS_LEVEL", "tdomf_access_level");
define("TDOMF_NOTIFY_LEVEL", "tdomf_notify_level");

///////////////////////////////////////
// 0.6 Settings (no longer used)
//
define('TDOMF_NOTIFY','tdomf_notify');
define("TDOMF_ITEMS_PER_PAGE", 30);
define("TDOMF_POSTS_INDEX",    0);
define("TDOMF_USERS_INDEX",    1);
define("TDOMF_IPS_INDEX",      2);
define("TDOMF_OPTIONS_INDEX",  3);
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

////////////////
// 0.10 Settings
//

define('TDOMF_OPTION_NAME',"tdomf_form_name");
define('TDOMF_OPTION_DESCRIPTION',"tdomf_form_description");
define('TDOMF_OPTION_CREATEDPAGES',"tdomf_form_created_pages");
define('TDOMF_OPTION_INCLUDED_YOUR_SUBMISSIONS',"tdomf_form_inc_user_page");
define('TDOMF_OPTION_CREATEDUSERS',"tdomf_form_created_users");
define('TDOMF_OPTION_WIDGET_INSTANCES',"tdomf_form_widget_instances");
define('TDOMF_OPTION_SUBMIT_PAGE',"tdomf_form_submit_page");
define('TDOMF_KEY_FORM_ID',"_tdomf_form_id");

// DB Table Names
//
define("TDOMF_DB_TABLE_FORMS", "tdomf_table_forms");
define("TDOMF_DB_TABLE_WIDGETS", "tdomf_table_widgets");

//////////////////
// 0.10.2 Settings

// Set to true if you want to hide the register_global errors. Do this only if
// you know what you are doing!
define("TDOMF_HIDE_REGISTER_GLOBAL_ERROR", false);

define('TDOMF_OPTION_WIDGET_MAX_WIDTH',"tdomf_form_widget_max_width");
define('TDOMF_OPTION_WIDGET_MAX_LENGTH',"tdomf_form_widget_max_length");

//////////////////////////////////////////////////
// loading text domain for language translation
//
load_plugin_textdomain('tdomf',PLUGINDIR.DIRECTORY_SEPARATOR.TDOMF_FOLDER);

//////////////////////////////////////////////////////////////////////////
// A potential fix for WordpressMU (WordpressMU is officially unsupported)
//
require_once(ABSPATH . 'wp-includes/pluggable.php');

// Is this a Wordpress < 2.5 install?
//
function tdomf_wp23() {
  global $wp_db_version;
  #if($wp_db_verison <= 6124)
  #  return true;
  return !tdomf_wp25();
}

// Is this a Wordpress >= 2.5 install?
//
function tdomf_wp25() {
  global $wp_db_version;
  if($wp_db_version >= 7558) {
    return true;
  }
  return false;
}


///////////////////////////////////
// Configure Backend Admin Menus //
///////////////////////////////////

add_action('admin_menu', 'tdomf_add_menus');
function tdomf_add_menus()
{
    add_menu_page(__('TDO Mini Forms', 'tdomf'), __('TDO Mini Forms', 'tdomf'), 'edit_others_posts', TDOMF_FOLDER, 'tdomf_overview_menu');

    // Options
    add_submenu_page( TDOMF_FOLDER , __('Form Manager and Options', 'tdomf'), __('Form Manager and Options', 'tdomf'), 'manage_options', 'tdomf_show_options_menu', 'tdomf_show_options_menu');
    //
    // Generate Form
    add_submenu_page( TDOMF_FOLDER , __('Form Widgets', 'tdomf'), __('Form Widgets', 'tdomf'), 'manage_options', 'tdomf_show_form_menu', 'tdomf_show_form_menu');
    //
    // Moderation Queue
    if(tdomf_is_moderation_in_use()) {
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
require_once('include'.DIRECTORY_SEPARATOR.'tdomf-db.php');

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
    add_option(TDOMF_OPTION_WIDGET_MAX_WIDTH,500);
    add_option(TDOMF_OPTION_WIDGET_MAX_LENGTH,400);
  }

  // Pre 0.9.3 (beta)/16
  if(intval(get_option(TDOMF_VERSION_CURRENT)) < 16) {
    add_option(TDOMF_OPTION_YOUR_SUBMISSIONS,true);
  }

  // Pre WP 2.5/0.10.2
  if(intval(get_option(TDOMF_VERSION_CURRENT)) < 26) {
    add_option(TDOMF_OPTION_WIDGET_MAX_WIDTH,500);
    add_option(TDOMF_OPTION_WIDGET_MAX_LENGTH,400);
  }

  // Update build number
  if(get_option(TDOMF_VERSION_CURRENT) != TDOMF_BUILD) {
    update_option(TDOMF_VERSION_CURRENT,TDOMF_BUILD);
  }
}

tdomf_db_create_tables();
tdomf_load_widgets();

?>
