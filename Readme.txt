=== Plugin Name ===
Contributors: the_dead_one
Donate link: http://tinyurl.com/yvgcs9
Tags: anonymous, posting, users, post, form, admin, submit, submissions, unregistered users, uploads, downloads, categories, tags, custom fields
Requires at least: 2.3
Tested up to: 2.3
Stable Tag: 0.9.1

This plugin allows you to add a form to your website that allows your readers (including non-registered) to submit posts.

== Description ==

This plugin allows you to add a form to your website that allows non-registered users and/or subscribers (configurable) to submit posts. The posts are kept in "draft" until an admin can publish them (also configurable).

**Version 0.9 introduces the ability to add custom fields and use multiple instances of some widgets**

The plugin provides an extensive moderation view so administrators and editors can see posts awaiting approval and publish or delete them. Administrators can also ban specific users and IPs from using the form. Administrators can also "Trust" specific users. This means that when they use the form, their posts are automatically published. This does not give them any other rights or permissions using the Wordpress software, it only affects usage of the form. This applies to user and IP bans as well. There is even an option to automatically trust users after so many approved submissions. (It should be noted that submissions from users that can already publish using the normal Wordpress UI, will be automatically published.)

Administrators can configure the form using drag and drop "widgets". They are based on the same model as Wordpress' built-in Theme widgets and it is possible to write your own. With 0.7, more options are available and it is now much easier to integrate with your theme. You don't even need to modify your theme any more to display submitter information!

Registered users have access to a "Your Submissions" page which lists their current submissions awaiting approval and links to their approved submissions.

* [Demo Site]( http://thedeadone.net/tdomf/ )

= Features =

* Highly customisable: Create your form using a Widget interface.
* Simple Captcha Widget.
* Posting Policy Widget.
* Add Custom Fields to your widget.
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

to any post or page. The plugin will replace this with the form.

You can add it to your template directly using this template tag:

`<?php tdomf_the_form(); ?>`

= How do I display the submitter info? =

There are options to automatically modify the the_author tag with submitter information if available and also to append submitter information to the end of the post. 

If thats not good enough for you, you can use the template tag:

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

Why? It opens your site up to spammers and other nefarious uses. However, people keep asking for this feature. You can disable moderation in the options menu and all posts will be published. However such posts get passed through Wordpress' kses filters automatically to remove nasty scripts.

= When people submit posts with YouTube embedded code, it gets stripped! =

Enable moderation and it'll work. If you disable moderation, posts get passed through kses to remove nasty scripts before being published. This removes YouTube code. If you have to approve posts, you can make sure no-one has snuck in something tricky.

Alternativily you can use a custom field. Add the Custom Field widget to your form, set it as a URL and ask your submitters to add the URL of the YouTube video they want to include. Then in your theme, you can use the Custom Fields template tags to automatically display the YouTube video underneath the submitted post! 

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
* Sometimes, on first login, the "Your Submissions" page displays an error about headers already sent, however the form still works and subsequent loads of the page do not reveal this error. I have not reproduced this locally yet so I haven't got to the core of the issue yet.
* If you deactivate the plugin at a later date, links to uploaded files will break (as they use a wrapper in the plugin). 

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

= v0.X: TBD =

* Potential fix for the never-ending "session_start" problem. Using template_redirect instead of get_header. 
* New Suppress Error Messages (works to a point)
* Warnings about register_globals added
* Fix for file uploads mkdir for windows included. Thansk to "feelexit" on the TDOMF forums for the patch
* "Latest Submissions" added to main Dashboard
* Two widgets for your theme!
* Fixed 1-q captcha widget not accepting quotes (")
