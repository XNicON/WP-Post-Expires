=== WP Post Expires ===
Contributors: X-NicON
Donate link:
Tags: expired post, posts expiring, expiration, expire, wordpress post expiry
Requires at least: 3.0
Tested up to: 4.9.4
Stable tag: 1.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin adds post expires time after which will be performed actions: add prefix to title, move to drafts or trash.

== Description ==
A simple plugin that allows to set the date for the posts, after which will be performed one of 3 actions: "Add prefix to title", "Move to drafts", "Move to trash".

= Features: =
*   Set post expire date and time
*   Support custom post type (post and pages by default)
*   Action for expired posts: move to drafts, move to trash or add custom prefix for title
*   Adds class "post-expired" to post that expired on the site and in admin interface (for custom styles in theme)

= Use in theme: =

`xnpostexpires::isExpired($post_id)`

`xnpostexpires::dateExpiration($post_id, $format)`

== Screenshots ==

1. Select date and time in post
2. Add personal prefix for any post
3. Default settings

== Installation ==

1. Copy to plugins folder (/wp-content/plugins)

2. Activate plugin from plugins page in admin interface

3. Use the Settings->Reading Name screen to configure the plugin

== Changelog ==

= 1.1 =

conflicts resolved
fix datetime picker
update datetime picker

= 1.0.3 =

small fixes
add Italian translation (tnx, Paolo Centomani)

= 1.0.2 =

fix notice and accessing static property

= 1.0.1 =

fixed translation

= 1.0 =

init1 - Mr. Robot

== Upgrade Notice ==

= 1.0 =

HELLO FRIEND
