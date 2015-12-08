=== Fullworks Slack ===
Contributors: fullworks
Tags: Slack
Requires at least: 3.4
Tested up to: 4.3.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin that makes integrating Slack with WordPress easier. This version has a join slack widget and shortcode.

== Description ==

Fullworks Slack is a suite of features to increase integration of Slack to WordPress.
This release contains a widget and shortcode to enable joining a public Slack team.
Settings allow hiding of the widget or shortcode except for logged in user with specified roles, this means you can easily create
a membership site where only members can use the auto join Slack forms.
 
== Installation ==

1. Upload the `fullworks-slack` folder to the `/wp-content/plugins/` directory
1. Activate the Fullworks Slack plugin through the 'Plugins' menu in WordPress
1. Configure the plugin by going to the `Fullworks Slack` sub-menu that appears in your admin settings menu

== Frequently asked questions ==

= Are there any settings? =

Yes, you have to specify the slack team and provide a Slack API token at a minimum.

The 'Join Slack' widget/shortcode has options to restrict visibility to logged in users and user roles.

= Will it work on Multisite? =

Yes this has been built and tested on multisite.

= Where do I get my API token? =

From Slack.  Login to your Slack team and go here https://api.slack.com/web you will be able to create or change API Tokens.

= How do I customise the style? =

The Join Slack form has some minimal styling and inherits the rest from your theme, if you want to adjust the styling, just overide the css in your themes style.css

= What is the Shortcode? =

The shortcode is there if you want to use the Join Slack form on a page, rather than a sidebar.

In it's simplest form it is [join-slack]

The full options are
* title
* text
* after_text
* button_text
* image

= What other Slack features are available? =

These are under development, but please let us know if you hav esome specifics in mind.

== Changelog ==

= 1.0 =
*  first release

== Upgrade notice ==
