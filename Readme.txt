=== Plugin Name ===
Contributors: the_dead_one
Donate link:
Tags: anonymous, posting, users, post, form, ajax
Requires at least: 2.1.2
Tested up to: 2.1.2
Stable Tag: 0.6

This plugin allows you to add a form to your website that allows your readers (including non-registered) to submit posts.

== Description ==

This plugin allows you to add a form to your website that allows non-registered users and/or subscribers (configurable) to submit posts. The posts are then in "draft" until an admin can publish them.

The plugin provides a form which has fields for submitters to give an email address so that they will be notified when their post is published. Information about the submitter is stored as custom fields on the post. If the submitter is a registered user who can publish posts themselves, then when they use the form, the posts are automatically published.

The plugin has a moderation view so you can see all the posts awaiting approval and have already been published. You can also ban specific users and IPs from using the form. Additionally you can also "Trust" specific users. This means that when they use the form, their posts are automatically published. This does not give them any other rights or permissions using the Wordpress software, it only affects usage of the form. This applies to user and IP bans as well.

However users that can already publish, when they use the form, their posts are also automatically published.

Info about the submitter (IP and also name and email if not-registered and login name if registered) is contained within the custom fields of the post. The admin email also contains this info and gives links to directly approve or ban the user. You can modify information about submitter using a sidebar panel on the edit post menu.

Posts submitted by registered users normally have the author as the submitter. However if you have multiple users who can publish then when you publish this post via the normal Wordpress UI and the submitter was only a subscriber, Wordpress automatically changes the author to you as subscribers can't publish. The plugin automatically corrects this for posts submitted via the form. (you can turn this option off).

* [Demo Site]( http://thedeadone.net/tdomf/ )
* [Plugin News]( http://thedeadone.net/index.php?tag=tdomf ), [RSS Feed]( http://thedeadone.net/index.php?tag=tdomf&feed=rss2 )
* [Plugin Homepage]( http://thedeadone.net/software/tdo-mini-forms-wordpress-plugin/ )
* [Plugin Support Forum]( http://thedeadone.net/forum )

== Installation ==

Download the zip and extract the files to a subdirectory, called TDOMiniForms, of your plugin directory. i.e. /path_to_wordpress/wp-content/plugins/TDOMiniForms. Do not place the files in a subidirectory of TDOMiniForms. Make sure the path and, at least, the file "ajax-loader.gif" can be seen from a web browser.

Once you've got it installed, active the plugin via the usual Wordpress plugin menu. You can then configure it via the options admin menu.

You should initially create a "dummy user" account to use as the "default author" if you plan on allowing unregistered users use the form. Posts from unregistered users are assigned this default author as the author of the post. This user must *not* have rights to publish posts. The user should have only have the subscriber role. 

You have two ways to add the actual form to your website. You can create a page (or a post) and add:

`<!--tdomf_form1-->`

The plugin will replace this with the form.

Or you can also add it to your template using this function:

`<?php tdomf_show_form(); ?>`

Once you've got it setup and installed, please test it!

Try it with an admin account, a subscriber account and, if you use the "Anyone" option, when not logged in. Make sure the emails to the admins and the submitter notifications are all good. You should note that the appearance of the form changes depending on user you are. Some fields are not applicable if your logged in as the plugin knows who you are.

You can also display the submitter of a post on your template by using this template function inside the loop:

`<?php tdomf_the_submitter(); ?>`

or

`<?php echo tdomf_the_submitter(); ?>`

== Frequently Asked Questions ==

= It doesn't work on my Windows Host! =

It should. I've tested this on a windows host. Please make sure you are using the latest version of PHP and that you're PHP.ini is correctly setup.

= I want to add custom fields! =

v0.7 is nearly complete. This provides a widget-like interface that will support optionally adding custom fields. A custom field widget won't be avaliable till v0.8 however.

= I want to allow my readers to attach a image to a submission? =

v0.7 is nearly complete. The new widget feature has been designed to specifically support file uploading. However such a feature won't be avaliable till v0.8 however.

= I want to allow only certain people to access the form =

The best way to do this is to use Wordpress roles. Create a role using the [Role Manager Plugin](http://redalt.com/Resources/Plugins/Role+Manager "Role Manager Plugin"). This plugin has nothing to do with me. Make sure it is not the default role and that it can't `edit_other_posts` or `publish_posts`. Then you can use the TDOMF Options Menu to set that as the only role that can access the form.

If you don't want people to have to register, you might try looking at this plugin: [Wordpress OpenID Plugin](http://verselogic.net/projects/wordpress/wordpress-openid-plugin/ "Wordpress OpenID Plugin"). This plugin has nothing to do with me. This plugin allows people to use an OpenID identity to login to your Wordpress site. If the user has an account on Wordpress.com, LiveJournal, Yahoo and numerous other sites, they can log in using that account and once they have logged in, you can assign them to the right role.

Another suggestion, but much less secure and not recommended, is to have the page where you have the form, password protected and only send the page link and password to the people you want to access the form.

== Screenshots ==

1. The Form as displayed to non-registered users
2. The Moderation page

== Known Bugs == 

* Incompatibility with the "Bad Behaviour" Wordpress plugin.
* CSS code being erased from published posts when submitter is used as author. This should be fixed in v0.7 coming soon.

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

