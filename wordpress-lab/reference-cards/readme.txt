=== Reference Cards ===
Contributors: albadawidev
Tags: shortcode, posts, portfolio, cards
Requires at least: 6.6
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An assisted personal portfolio demo. This is not code from a client engagement.

== Description ==
Use [reference_cards count="3" category="guides"] to display the latest published,
unprotected posts in a category. Omit category for all published posts.

The card count is bounded from 1 to 12. Invalid non-integer input defaults to 3.
There are no admin settings, external services, analytics, or JavaScript.
The plugin enqueues a small namespaced stylesheet on frontend pages.

== Installation ==
Upload the reference-cards directory into wp-content/plugins and activate it.
Place the shortcode in a Shortcode block or classic editor content.

== Changelog ==
= 1.0.0 =
Initial portfolio lab: bounded query, category filtering, escaped output,
accessible links, responsive stylesheet, and actual WordPress/MySQL tests.
