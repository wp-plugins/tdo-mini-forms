=== Plugin Name ===
Contributors: the_dead_one
Donate link: http://tinyurl.com/yvgcs9
Tags: anonymous, posting, users, post, form, admin, submit, submissions, unregistered users, uploads, downloads, categories, tags, custom fields, captcha, custom posting interface,
Requires at least: 2.3
Tested up to: 2.3.1
Stable Tag: 0.10

This plugin allows you to add custom posting forms to your website that allows your readers (including non-registered) to submit posts.

== Description ==

This plugin allows you to add highly customisable forms to your website that allows non-registered users and/or subscribers (also configurable) to submit posts. The posts are kept in "draft" until an admin can publish them (also configurable).

**Version 0.10, adds multiple form support, ability to submit pages and increase the number of form widgets!**

The plugin provides an extensive moderation view so administrators and editors can see posts awaiting approval and publish or delete them. Administrators can also ban specific users and IPs from using the form. Administrators can also "Trust" specific users. This means that when they use the form, their posts are automatically published. This does not give them any other rights or permissions using the Wordpress software, it only affects usage of the form. This applies to user and IP bans as well. There is even an option to automatically trust users after so many approved submissions. (It should be noted that submissions from users that can already publish using the normal Wordpress UI, will be automatically published.)

Administrators can configure the forms using drag and drop "widgets". They are based on the same model as Wordpress' built-in Theme widgets and it is possible to write your own. With 0.7, more options are available and it is now much easier to integrate with your theme. You don't even need to modify your theme any more to display submitter information and you can even add a form as a sidebar widget to your form.

Registered users have access to a "Your Submissions" page which lists their current submissions awaiting approval and links to their approved submissions.

* [Demo Site]( http://thedeadone.net/tdomf/ )

= Features =

* Highly customisable: Create your forms using a Widget interface.
* Create as many forms as you like.
* Put a form in your sidebar using a widget for your Theme.
* Submit pages instead of posts.
* Simple Question and/or Image Captcha.
* Add Custom Fields to your Forms.
* QuickTags support for Forms.
* Upload Files and can be attached to posts. Uses Wordpress' core to create thumbnails if applicable.
* Submitters can be notified if post approved or rejected.
* Allow users to select category and tags.
* Ban users and IPs.
* Control what roles can access the form.
* Can automatically create a page with form for you.
* Can automatically modified author template tag with info about submitter.
* Can, optionally, automatically allow submissions to be published.
* And many more...

**Version 0.10 is a big upgrade. You will not be able to use previous versions about this install**

**Version 0.7 is a major reworking of the code. Make sure to follow the upgrade instructions if you are using a version prior to this!**

== Installation ==

Download the zip and extract the files to a subdirectory, called tdo-mini-forms, of your plugin directory. i.e. `/path_to_wordpress/wp-content/plugins/tdo-mini-forms`. 

Once you've got it installed, active the plugin via the usual Wordpress plugin menu. Make sure you then configure it via the main TDOMF menu in the Wordpress Administration backend.

You must assign a user as the "Default Author". This user must not have rights to publish or edit posts, i.e. they should be of the subscriber role. When posts are submitted from unregistered users, this "Default Author" user is set as the author of the post to keep Wordpress happy. The TDOMF options menu can automatically create a dummy user to set as the Default Author. This is the recommended approach.

On the options menu, there is a button to automatically create a page with the necessary tag to display your form. There are also other options to help integrate with your theme on this page. For more information on Theme integration, please refer to the Frequently Asked Questions of this readme.

= Upgrade Instructions from versions previous to 0.7 =

Before installing the new version of TDO Mini Forms, delete the TDOMiniForms from your `.../wp-content/plugins/` folder. Now simply follow the installation instructions above. You will need to re-configure the plugin again, however previously submitted posts and other user information will be retained from your previous installation of the plugin.

== Frequently Asked Questions ==

= Where do I get the latest updates on TDO Mini Forms? =

[TDO Mini Forms News]( http://thedeadone.net/index.php?tag=tdomf ) and here is the [RSS Feed]( http://thedeadone.net/index.php?tag=tdomf&feed=rss2 ).

= Where is the best place to get support for this plugin? =

You can use the [TDOMF Support Forum]( http://thedeadone.net/forum ) or you can post on [Wordpress.org's Support Form]( http://wordpress.org/tags/tdo-mini-forms#postform ).

= How do I add a form to a page or post? =

You can use the button in the options menu to create a page or instead you can add:

`[tdomf_form1]` 

to any post or page. The plugin will replace this with your Form 1. If you have multiple forms, each form has an ID. Just replace the '1' with the correct form ID.

You can add it to your template directly using this template tag:

`<?php tdomf_the_form(1); ?>`

= How do I display the submitter info? =

There are options to automatically modify the the_author tag with submitter information if available and also to append submitter information to the end of the post. If thats not good enough for you, you can use the template tag:

`<?php tdomf_the_submitter(); ?>`

= What template tags are available? =

`<?php if(tdomf_can_current_user_see_form()) { ?> Link to form <?php } ?>`

`<?php echo tdomf_get_the_form(); ?>`

`<?php tdomf_the_form(); ?>`

These tags must be used within the loop:

`<?php echo tdomf_get_the_submitter(); ?>`

`<?php tdomf_the_submitter(); ?>`

= I want to add custom fields! =

With v0.9, you can! There is now a Custom Field widget avaliable to add to your form. Currenly only text fields and text areas are supported but future versions will support check boxes, drop down lists, radio groups, etc.

= I want to allow my readers to attach a image to a submission? =

With v0.8, you can allow users to upload files. You can specify what files can be uploaded and how big. You can also optionally have the upload files automatically added to the post as an image, link or a Wordpress attachment. 

To add the option to upload files, as admin, go into the TDOMF menu and then the widgets menu. On that page you can drap and drop widgets. Just drag and drop the "Upload Files" widget.

= I want to allow only certain people to access the form =

The best way to do this is to use Wordpress roles. Create a role using the [Role Manager Plugin](http://redalt.com/Resources/Plugins/Role+Manager "Role Manager Plugin"). This plugin has nothing to do with me. Make sure it is not the default role and that it can't `edit_other_posts` or `publish_posts`. Then you can use the TDOMF options page to set that as the only role that can access the form.

If you don't want people to have to register, you might try looking at this plugin: [Wordpress OpenID Plugin](http://verselogic.net/projects/wordpress/wordpress-openid-plugin/ "Wordpress OpenID Plugin"). This plugin has nothing to do with me. This plugin allows people to use an OpenID identity to login to your Wordpress site. If the user has an account on Wordpress.com, LiveJournal, Yahoo and numerous other sites, they can log in using that account and once they have logged in, you can assign them to the right role.

Another suggestion, but much less secure and not recommended, is to have the page where you have the form, password protected and only send the page link and password to the people you want to access the form.

= I want submissions, even from unregistered users, be published automatically!! =

You can disable moderation in the options menu for a specific form and all posts will be published. However such posts get passed through Wordpress' kses filters automatically to remove nasty scripts.

= When people submit posts with YouTube embedded code, it gets stripped! =

Enable moderation and it'll work. If you disable moderation, posts get passed through kses to remove nasty scripts before being published. This removes YouTube code. If you have to approve posts, you can make sure no-one has snuck in something tricky.

Alternativily you can use a custom field. Add the Custom Field widget to your form, set it as a URL and ask your submitters to add the URL of the YouTube video they want to include. Then in your theme, you can use the Custom Fields template tags to automatically display the YouTube video underneath the submitted post. Or you can use another plugin that gives you tags to support YouTube and have the Custom Field append the YouTube link with the tags to your post.

= Can we use TinyMCE or FckEditor for writing posts? =

I have spent some time exploring the use of TinyMCE (and to a lesser degree FckEditor) for TDO Mini Forms. Both libraries provide a WYSIWYG or "Rich Text" editors in place of your bog-standard text area. Wordpress' write screen using a heavily modified version of TinyMCE. I haven't settled on the right method to do this yet. However you can easily integrate TinyMCE without modifying any of TDO Mini Forms. Grab the latest copy and installed it somewhere on your website and then follow the directions on how to replace a text area with TinyMCE. This can be used to even change your comment input field.

= I get "ERROR: register_globals is enabled" and/or "ERROR: register_globals is enabled in your PHP environment! =

[register_globals](http://ie2.php.net/register_globals) is a PHP setting. Having it enabled is considered a security risk and Wordpress takes steps to plug the hole when it detects the setting. However these steps delete information used by the TDOMF form and will prevent it from operating correctly. 

To resolve this, you have a number of options.

* If you can access and modify your .htaccess you can disable `register_globals` by adding this line:
`php_flag register_globals off`
* Ask your host to turn off `register_globals'.
* Modify Wordpress (ask on the [forums]( http://thedeadone.net/forum ) how to do that)

= I want to add add tags to QuickTags such as embed video, etc.? =

Right now there is no user interface for adding your own tags to quicktags. Feel free, however, to modify `tdomf-quicktags.js.php` to add any tags you want.

= I want to display some information about the upload files? =

In later versions, proper template tag support will be added. However, for the moment you can use:

`// Gets the name of the first uploaded file for post $post_ID
get_post_meta($post_ID, "_tdomf_download_name_0"); 

// Gets the type of the first uploaded file for post $post_ID
get_post_meta($post_ID, "_tdomf_download_type_0"); 

// Gets the download count of the first uploaded file for post $post_ID
get_post_meta($post_ID, "_tdomf_download_count_0"); 

// Gets the path to the first uploaded file for post $post_ID
get_post_meta($post_ID, "_tdomf_download_path_0"); 

// Gets the command output for the first uploaded file for post $post_ID (if avaliable)
get_post_meta($post_ID, "_tdomf_download_cmd_output_0"); 

// Gets the name of the second uploaded file for post $post_ID
get_post_meta($post_ID, "_tdomf_download_name_1");

// And so on...`

= Uploading a bmp file causes errors! =

If you have the options for attachments and thumbnail generation turned on for Upload Files and you try to upload a *.bmp (bitmap) image, you'll get an error like this:

`Warning: imageantialias(): supplied argument is not a valid Image resource...`

Wordpress does not support bitmaps for thumbnails so you cannot use bitmaps for thumbnail generation.

= I can't upload files! : safe_mode and open_basedir issues =

First step, make sure you can upload with the normal Wordpress admin UI. If you can't then your not going to be able to upload with TDOMF until that is sorted. 

If you do and you get an error with something like this

`Warning: mkdir() [function.mkdir]: open_basedir restriction in effect`

or

`Warning: mkdir(): [function.mkdir]: SAFE MODE Restriction in effect` 

Then you host has restricted where you can create and upload files. Safe mode is particularly bad because it'll fail in unexpected ways. Ultimately the best solution is not to use safe mode or open_basedir but you may not have the option to do that.

The best solution is to use a folder to store uploads that does not break safe mode. If you can upload with the normal wordpress interface then you can use something like <path to your wordpress install>/wp-content/uploads. Remember also that you cannot use symbolic links in your path to get around open_basedir restrictions.

You can enable extra log messages from the options screen to see more detailed messages about file uploading. You can also check your "phpinfo()" from the main TDOMF page.

= Having submitted posts not included on your main page =

This is outside the scope of TDOMF as TDOMF only enables people to submit posts. However you can use a plugin like [Advanced Category Excluder Plugin](http://wordpress.org/extend/plugins/advanced-category-excluder/ "Advanced Category Excluder Plugin"). This plugin has nothing to do with me. You could have posts submitted to a specific category that is excluded from your main blog.

= Credits =

I've used code in TDOMF that I've found in the wild so some credit is due to these authors for making their source code avaliable for re-use. 

PHP Function to create a random string based on (http://www.tutorialized.com/view/tutorial/PHP-Random-String-Generator/13903)

PHP Function to validate an email address based on (http://www.ilovejackdaniels.com/php/email-address-validation/ )

PHP Function to turn a file size in bytes to an intelligable format based on (http://www.phpriot.com/d/code/strings/filesize-format/index.html)

Quicktags Javascript script taken from (http://www.alexking.org/)

Freecap (PHP Image capatcha) taken from (http://puremango.co.uk/)

Customfield Select Box javascript based on (http://www.mredkj.com/tutorials/tutorial006.html)

== Screenshots ==

1. The Form as displayed to non-registered users
2. The Moderation page (v0.6)
3. "Your Submissions" page for registered users
4. The Moderation page (v0.7) for approved submissions
5. The overview page
6. Constructing your form using "widgets"

== Known Bugs == 

* v0.6 had an incompatibility issue with the "Bad Behaviour" Wordpress plugin. This has not been confirmed with v0.7+.
* It has been found that there is some incompatibility with v0.8 and the WP-Email plugin. I haven't tracked it down yet, but on my recent tests, it seemed to be playing nice with v0.9. Any info on this issue would be greated appreciated. 
* If you deactivate the plugin at a later date, links to uploaded files will break (as they use a wrapper in the plugin). However with v0.9.3, you can set an option in the "Upload Files" widget to use direct links instead of the wrapper.
* Uploading a bmp image with attachment and thumbnail options turns on causes an error. Wordpress does not support bitmaps for thumbnail generation.
* Form does not validate as XHTML. I'll fix this soon I swear! :)

== Version History ==

= Preview: 21 November 2006 =

* Preview Release, only on wordpress.org/support forums.

= v0.1: 22 November 2006 = 

* Initial Release with basic features

= v0.2: 29 November 2006 =

* Fixed bug: If default author had rights to post, anon posts would be automatically published.
* Replaced the word "download" used in messages to the user.
* Added a "webpage" field when posting anonymously.

= v0.3: 6 March 2007 =

* Ported to Wordpress 2.1.2.

= v0.4: 9 March 2007 =

* New template tags: tdomf_get_submitter and tdomf_the_submitter.
* The plugin should work on Windows based servers
* A TDOMF panel on the edit post page
* Posts can now be very long (no 250 word limit)

= v0.5: 15 March 2007 =

* Tested on Windows based host
* Chinese text does not get mangled
* Post Edit Panel now works properly on Firefox (and does not prevent posting).

= v0.6: 20 March 2007 =

* Options Menu: Control access to form based on roles
* Options Menu: Control who gets notified to approve posts by role.
* Options Menu: Default author is now chosen by login name instead of username
* Javascript code only included as necessary (i.e. not in every header)

= v0.7: 26 September 2007 =

* New "Overview" page
* Move the various admin pages to it's own submenu
* Updated Edit Post Panel (uses built in AJAX-SACK)
* Updated options menu
* Code refactored and renamed files and restructured directories
* Logging feature
* Can uninstall the plugin completely. Also removes v0.6 unused options too.
* "Create Dummy User" link on options page
* "Create Page with Form" from options page
* Properly implemented form POST and dropped AJAX support
* Can now automatically updates "the_author" template tag with submitter info
* Can now automatically add "This post submitted by..." to end of post content
* Bulk moderation of submitted posts, users and IPs
* "Nonce" support for admin backend pages
* "Your Submissions" page for all users. Form is included on this page.
* Form should be XHTML valid (unless a new widget breaks it!)
* Handle magic quotes properly
* Allow YouTube embedded code to be posted, though this option is only allowable if moderation is enabled! Otherwise Wordpress' kses filters will pull it out.
* Reject Notifications as well as Approved Notifications
* Can now restrict html tags on posted content
* New Template Tag: tdomf_can_current_user_see_form() returns true if current user can access the form
* Simple question-captcha widget: user must answer a simple question before post will be accepted.
* "I agree" widget: user must check a checkbox before post will be accepted.

= v0.71: 28 September 2007 =

* Two small mistakes seemed to have wiggled into the files before 0.7 was released. Still getting the hang of SVN I guess.

= v0.72: 2 October 2007 = 

* Date is not set when post is published. This was okay in WP2.2.
* Comments are getting automatically closed (even if default is open). This was okay in WP2.2.
* widget.css in admin menu has moved in WP2.3. This is no longer compatible with WP2.2.
* Can now again select a default category for submissions and new submissions will pick that category up. With WP2.3, tags and categories have changed to a new "taxonomy" structure, which messed up how TDOMF works.
* Added a "tdomf_widget_page" action hook
* Fixed Widget page to work in WP2.3. WP2.3 now uses jQuery for a lot of its javascript needs
* If you happen to use as your database prefix "tdomf_", and then if you uninstall on WP2.3, it would delete critical options and bugger up your wordpress install.

= v0.8: 12th October 2007 =

* Upload Feature added
* Widgets can now append information to the email sent to moderators
* Tag Widget: allow submitters to add tags to their submissions
* Categories Widget: First run of the categories widget.

= v0.9: 2nd November 2007 =

* Updated Upload Files: if a file is added as attachment, Wordpress will generate a thumbnail if the file is an image.
* New Upload File Options: You can now automatically have a link added to your post that goes to the attachment page (can even use the thumbnail if it exists). Additionally, if the thumbnail exists, can insert a direct link to file using the thumbnail).
* Uploads added as attachments will inherit the categories of the post (but remember the order of widgets is important so if the categories get modified after the upload widget has done it's biz, these changes won't be affected to the attachments)
* More info on error checking!
* "Notified" instead of "notify" in Notify Me widget
* Added quicktags to the post "Content" widget (restrict tags option hides restricted tags from toolbar)
* Uninstall was broken! Was not deleting option settings.
* Removed "About" menu and reorgainsed the overview page a bit. 
* Added first draft of custom fields (only textfield and textarea supported)
* Updated "1 Question Captcha" and "Categories widgets" to support multiple instances
* Added a "Text" widget
* Fixed a bug when deleting a post with uploaded files on PHP4 or less

= v0.9.1: 5th November 2007 =

* Fixed a javascript error in Quicktags that blocked it from working on Mozilla
* Fixed the admin notification email as the Wordpress cache for the custom fields for posts was being forgotten so the admin email did not contain information about IP and uploaded files.
* A define was missing from tdomf v0.9: TDOMF_KEY_DOWNLOAD_THUMB
* Spelling mistake fixed in "Your Submissions"

= v0.9.2: 9th November 2007 =

* Potential fix for the never-ending "session_start" problem. Using template_redirect instead of get_header. 
* New Suppress Error Messages (works to a point)
* Warnings about register_globals added
* Fix for file uploads mkdir for windows included. Thansk to "feelexit" on the TDOMF forums for the patch
* "Latest Submissions" added to main Dashboard
* Two widgets for your theme!
* Fixed 1-q captcha widget not accepting quotes (")

= v0.9.3: 1st December 2007 = 

* Fixed customfield textfield control radio group in Firefox
* Fixed customfield textfield ignoring size option
* Fixed customfield textarea putting magic quotes on HTML
* Fixed customfield textfield not handling HTML and quotes well.
* Fixed customfield textfield not handling foreign characters well.
* Fixed customfield textarea quicktag's extra button only working on post content's quicktag's toolbar
* Updated customfield to optionally can automatically add value to post with a user defined format
* Removed any "short tag" versions (i.e. use "<?php" instead of "<?")
* Add link to view post from moderator notification email
* Auto add buttons to post content to "approve" or "reject" submission on the spot
* Enable/disable preview of customfield value
* Added option to Upload Files widget to use direct links
* Get phpinfo page
* Conf dump page
* Updated stylesheet to look nice in IE
* Fixed borked thumbnails from v0.9
* Fixed some issues with file uploading and safe_mode
* New Option: Enable/Disable "Your Submissions" page
* New Option: Enable extra debug log messages
* Make the tags widget conditional on the existance of 'wp_set_post_tags'. This will improve backwards compatibility with Wordpress < 2.3 (officially unsupported)
* Category widget: Multiple category selection
* Category widget: Display as list
* Customfield now supports select and checkbox options
* Added po file for translation

= v0.9.4: 7th January 2007 = 

* Added "Set Category from get variables" widget
* If moderation turned off, when post published, redirect to published post page.
* Fixed Custom Field widget javascript. Now works properly in Firefox (why does Firefox break on code that works in Opera and IE all the time?)
* Image Captcha Widget
* Updated all text fields input (and output) to use htmlentities. Hopefully this will cure foreign character input/output issues and weird re-encoding issues with widget settings.
* Word count or character limit on post content
* Theme Widget that displays the form!
* Add "credits" to readme.txt for various places I pull source and other stuff from
* Added a "Read More..." `<!--more-->` tag to the quick tags
* Fixed Bug when multiple notifications to submitter when post is edited after approval

= v0.10: XXX =

* Suppressed errors for is_dir and other file functions just in case of open_basedir settings!
* Use "get_bloginfo('charset')" in htmlentities in widget control. Hopefully this will finally resolve the issues with foreign lanaguage characters
* Multiple Form Support
* Widgets that validate know if it's for preview or post. Certain validation should only occur at post like captcha and "who am I" info for example.
* Option to specify the max number of instances of a multi-instant widget per form
* Can now set a form to submit pages instead of posts.
* Fixed a bug where customfield textfield would submit empty values for the custom field if you had magic quotes turned off.
* Update the "Freecap" Image Captcha so that the files get included in the release zip Wordpress creates.