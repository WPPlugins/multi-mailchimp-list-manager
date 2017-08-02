=== Plugin Name ===
Name: Multi MailChimp List Manager
Contributors: CreativeMinds (http://www.cminds.com/)
Donate link: http://www.cminds.com/plugins
Tags: MailChimp, newsletter, email
Requires at least: 3.2
Tested up to: 3.4
Stable tag: 1.0

Allows users to subscribe/unsubscribe from multiple MailChimp lists.

== Description ==

Allows users to subscribe/unsubscribe from multiple MailChimp lists with Twitter-like user interface.

Admin can specify which MailChimp lists should be available for subscription and assign custom descriptions for them.

Communication with MailChimp is based on MCAPI mini v1.3 downloaded from [here](http://apidocs.mailchimp.com/api/downloads/mailchimp-api-mini-class.zip)
The user interface is based on CSS3 stylesheet created by [Tim Hudson](https://github.com/timhudson/) [here](https://github.com/timhudson/Follow-Button).

Note: Plugin is compatible with most modern browsers: Chrome (all versions), Firefox >=3.5, Safari >=1.3, Opera >=6, Internet Explorer >=8

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set your MailChimp API Key in "Settings" page and fetch available lists.
4. Choose which MailChimp Lists you want to enable for user's subscription.
5. Add user interface to your site by either using MultiMailChimp widget or shortcode [mmc-display-lists]

Note: You must have a call to wp_head() in your template in order for the JS plugin files to work properly.  If your theme does not support this you will need to link to these files manually in your theme (not recommended).

== Frequently Asked Questions ==

= Where can I find my API Key ? =

http://kb.mailchimp.com/article/where-can-i-find-my-api-key.

= Can non logged-in users subscribe or see the lists ? =

Currently the plug supports only logged-in users. 

== Screenshots ==

1. User interface of MultiMailChimp.
2. The options available for MultiMailChimp in the administration area.

== Changelog ==

= 1.0 =
* Initial release

