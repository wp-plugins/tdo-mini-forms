=== Plugin Name ===
Contributors: the_dead_one
Donate link: http://tinyurl.com/yvgcs9
Tags: anonymous, posting, users, post, form, admin, submit, submissions, unregistered users
Requires at least: 2.3
Tested up to: 2.3
Stable Tag: 0.71

This plugin allows you to add a form to your website that allows your readers (including non-registered) to submit posts.

== Description ==

This plugin allows you to add a form to your website that allows non-registered users and/or subscribers (configurable) to submit posts. The posts are kept in "draft" until an admin can publish them (also configurable).

**Version 0.7 is a major reworking of the code. Make sure to follow the upgrade instructions if you are using a version prior to this!**

**This version is specifically to address issues on Wordpress 2.3. It is not backwards compatible with previous versions.**

The plugin provides an extensive moderation view so administrators and editors can see posts awaiting approval and publish or delete them. Administrators can also ban specific users and IPs from using the form. Administrators can also "Trust" specific users. This means that when they use the form, their posts are automatically published. This does not give them any other rights or permissions using the Wordpress software, it only affects usage of the form. This applies to user and IP bans as well. There is even an option to automatically trust users after so many approved submissions. 

It should be noted that submissions from users that can already publish using the normal Wordpress UI, will be automatically published.

The big feature of 0.7 is that Administrators can configure the form using drag and drop "widgets". They are based on the same model as Wordpress' built-in Theme widgets and it is possible to write your own.

With 0.7, more options are available and it is now much easier to integrate with your theme. You don't even need to modify your theme any more to display submitter information!

Registered users now have access to a "Your Submissions" page which lists their current submissions awaiting approval and links to their approved submissions.

* [Demo Site]( http://thedeadone.net/tdomf/ )
* [Plugin News]( http://thedeadone.net/index.php?tag=tdomf ), [RSS Feed]( http://thedeadone.net/index.php?tag=tdomf&feed=rss2 )
* [Plugin Support Forum]( http://thedeadone.net/forum )

== Installation ==

Download the zip and extract the files to a subdirectory, called tdo-mini-forms, of your plugin directory. i.e. `/path_to_wordpress/wp-content/plugins/tdo-mini-forms`. Make sure the path and, at least, the files "tdomf-form-post.php" and "tdomf-style-form.css" can be seen from a web browser.

Once you've got it installed, active the plugin via the usual Wordpress plugin menu. Make sure you then configure it via the main TDOMF menu in the Wordpress Administration backend.

You must assign a user as the "Default Author". This user must not have rights to publish or edit posts, i.e. they should be of the subscriber role. When posts are submitted from unregistered users, this "Default Author" user is set as the author of the post to keep Wordpress happy. The TDOMF options menu can automatically create a dummy user to set as the Default Author. This is the recommended approach.

On the options menu, there is a button to automatically create a page with the necessary tag to display your form. There are also other options to help integrate with your theme on this page. For more information on Theme integration, please refer to the Frequently Asked Questions of this readme.

= Upgrade Instructions =

Before installing the new version of TDO Mini Forms, delete the TDOMiniForms from your `.../wp-content/plugins/` folder. Now simply follow the installation instructions above. You will need to re-configure the plugin again, however previously submitted posts and other user information will be retained from your previous installation of the plugin.

== Frequently Asked Questions ==

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

v0.8, I swear! With v0.7, the widget interface was added. This allows a simple and neat way to add custom fields. 

= I want to allow my readers to attach a image to a submission? =

v0.8, I swear! With v0.7, the widget interface was added. The new widget feature has been designed to specifically support file uploading. However the widget won't be available till v0.8.

= I want to allow only certain people to access the form =

The best way to do this is to use Wordpress roles. Create a role using the [Role Manager Plugin](http://redalt.com/Resources/Plugins/Role+Manager "Role Manager Plugin"). This plugin has nothing to do with me. Make sure it is not the default role and that it can't `edit_other_posts` or `publish_posts`. Then you can use the TDOMF options page to set that as the only role that can access the form.

If you don't want people to have to register, you might try looking at this plugin: [Wordpress OpenID Plugin](http://verselogic.net/projects/wordpress/wordpress-openid-plugin/ "Wordpress OpenID Plugin"). This plugin has nothing to do with me. This plugin allows people to use an OpenID identity to login to your Wordpress site. If the user has an account on Wordpress.com, LiveJournal, Yahoo and numerous other sites, they can log in using that account and once they have logged in, you can assign them to the right role.

Another suggestion, but much less secure and not recommended, is to have the page where you have the form, password protected and only send the page link and password to the people you want to access the form.

= I want submissions, even from unregistered users, be published automatically!! =

Why? It opens your site up to spammers and other nefarious uses. However, people keep asking for this feature. You can disable moderation in the options menu and all posts will be published. However such posts get passed through Wordpress' kses filters automatically to remove nasty scripts.

= When people submit posts with YouTube embedded code, it gets stripped! =

Enable moderation and it'll work. If you disable moderation, posts get passed through kses to remove nasty scripts before being published. This removes YouTube code. If you have to approve posts, you can make sure no-one has snuck in something tricky.

== Screenshots ==

1. The Form as displayed to non-registered users
2. The Moderation page (v0.6)
3. "Your Submissions" page for registered users
4. The Moderation page (v0.7) for approved submissions
5. The overview page
6. Constructing your form using "widgets"

== Known Bugs == 

* v0.6 had an incompatibility issue with the "Bad Behaviour" Wordpress plugin. This has not been confirmed with v0.7+.

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