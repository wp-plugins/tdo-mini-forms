=== Plugin Name ===
Contributors: the_dead_one
Donate link: http://tinyurl.com/yvgcs9
Tags: anonymous, posting, users, post, form, admin, submit, submissions, unregistered users, uploads, downloads, categories, tags, custom fields
Requires at least: 2.3
Tested up to: 2.3
Stable Tag: 0.8

This plugin allows you to add a form to your website that allows your readers (including non-registered) to submit posts.

== Description ==

This plugin allows you to add a form to your website that allows non-registered users and/or subscribers (configurable) to submit posts. The posts are kept in "draft" until an admin can publish them (also configurable).

**Version 0.7 is a major reworking of the code. Make sure to follow the upgrade instructions if you are using a version prior to this!**

**This version is specifically to address issues on Wordpress 2.3. It is not backwards compatible with previous versions.**

The plugin provides an extensive moderation view so administrators and editors can see posts awaiting approval and publish or delete them. Administrators can also ban specific users and IPs from using the form. Administrators can also "Trust" specific users. This means that when they use the form, their posts are automatically published. This does not give them any other rights or permissions using the Wordpress software, it only affects usage of the form. This applies to user and IP bans as well. There is even an option to automatically trust users after so many approved submissions. (It should be noted that submissions from users that can already publish using the normal Wordpress UI, will be automatically published.)

Administrators can configure the form using drag and drop "widgets". They are based on the same model as Wordpress' built-in Theme widgets and it is possible to write your own. With 0.7, more options are available and it is now much easier to integrate with your theme. You don't even need to modify your theme any more to display submitter information!

Registered users have access to a "Your Submissions" page which lists their current submissions awaiting approval and links to their approved submissions.

**Version 0.9 introduces the ability to add custom fields and use multiple instances of some widgets**

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

v0.9, I swear! With v0.7, the widget interface was added. This allows a simple and neat way to add custom fields. 

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

= Can we use TinyMCE or FckEditor for writing posts? =

I have spent some time exploring the use of TinyMCE (and to a lesser degree FckEditor) for TDO Mini Forms. Both libraries provide a WYSIWYG or "Rich Text" editors in place of your bog-standard text area. Wordpress' write screen using a heavily modified version of TinyMCE. I haven't settled on the right method to do this yet. However you can easily integrate TinyMCE without modifying any of TDO Mini Forms. Grab the latest copy and installed it somewhere on your website and then follow the directions on how to replace a text area with TinyMCE. This can be used to even change your comment input field.

= I get an error: "TDOMF ERROR: Headers have already been sent in file..." =

TDO Mini Forms tries to call the PHP function session_start() by adding an action to the "get_header" template tag. The session variable is used to hold security information and to confirm a submission comes from an actual form (and not some bot). But, if you see this error, it means that some where the headers have already been sent and so the session cannot start. If you try to submit a form with this error, you'll only get another error "TDOMF: Bad data submitted".

The error message gives you details of the file that has already sent the headers. This could be something as simple as an empty blank line before everything else in the file or another plugin prints some HTML out using a "get_header" tag (if this is the case, it should really use "wp_head"). You can alternativily call session_start before anything else to remove this error. Just insert  `<?php session_start(); ?>` at the top of the offending file or before any HTML is printed out.

= In my form I get an error saying: "ERROR: session_start() has not been called yet!" = 

TDO Mini Forms tries to call the PHP function session_start() by adding an action to the "get_header" template tag. The session variable is used to hold security information and to confirm a submission comes from an actual form (and not some bot). But, if you see this error (and you do not see the "TDOMF ERROR: Headers have already been sent..."), it probably means that your theme does not use the "get_header" template tag. If you try to submit a form with this error, you'll only get another error "TDOMF: Bad data submitted". You can confirm this by temporarily switching your theme to the classic or default Wordpress theme. The errors should disappear.

To resolve this, you need to insert a call to session_start at the top of template file. Normally the form is added to a page and your theme should have a "page.php" template file. At the very top, first line, add this line:

`<?php session_start(); ?>`

= I get "TDOMF: Bad data submitted..." error when I submit a post! =

I assuming you don't get the "TDOMF ERROR: Headers have already been sent..." and/or "ERROR: session_start() has not been called yet!..." errors, this error means you've tried to submit your post from an invalid form. Try returning to the submission form, reloading it and then reenter/submit it.

== Screenshots ==

1. The Form as displayed to non-registered users
2. The Moderation page (v0.6)
3. "Your Submissions" page for registered users
4. The Moderation page (v0.7) for approved submissions
5. The overview page
6. Constructing your form using "widgets"

== Known Bugs == 

* v0.6 had an incompatibility issue with the "Bad Behaviour" Wordpress plugin. This has not been confirmed with v0.7+.
* It has been found that theire is some incompatibility with v0.8 and the WP-Email plugin. I haven't tracked it down yet, but on my recent tests, it seemed to be playing nice with v0.9. Any info on this issue would be greated appreciated. 

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

= v0.9: TBD =

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
