=== WP Post Expires ===
Contributors: X-NicON
Donate link: https://xnicon.ru/donate
Tags: expired post, posts expiring, expiration, expire, wordpress post expiry
Requires at least: 5.0
Tested up to: 5.2.1
Stable tag: 1.2.4
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

`XNPostExpires::isExpired($post_id)`

`XNPostExpires::dateExpiration($post_id, $date_format)`

== Screenshots ==

1. Select date and time in post
2. Add personal prefix for any post
3. Default settings

== Installation ==

1. Copy to plugins folder (/wp-content/plugins)

2. Activate plugin from plugins page in admin interface

3. Use the Settings->Reading Name screen to configure the plugin

== Changelog ==

= 1.2.4 =

fix bugs classic editor/gutenberg js, tnx @micahjsharp

= 1.2.3 =

add status in post state
change logic for draft and trash, tnx @micahjsharp
use local wp timezone

= 1.2.2 =

support classic editor on wp 5.0+

= 1.2.1 =

Small fixes:
https://wordpress.org/support/topic/fatal-error-3074/ ( tnx @marknopfler )
https://wordpress.org/support/topic/fix-js-and-php-error/ ( tnx @lastant )


= 1.2 =

use DateTime Picker jQuery-ui
refactoring

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
