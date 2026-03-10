=== Firefighter Statistics ===
Contributors: sync667
Tags: firefighter, emergency, statistics, widget, fire department
Requires at least: 5.9
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track and display emergency statistics for fire departments. Includes widgets, a Gutenberg block, and a shortcode.

*Polska wersja tego pliku dostępna jest w `readme-pl_PL.txt` / Polish version available in `readme-pl_PL.txt`.*

== Description ==

**Firefighter Statistics** adds a custom post type and widgets to help fire departments publish their emergency response statistics on their website.

**Features:**

* **Emergency post type** — create detailed records for individual emergency incidents.
* **Emergency Categories & Tags** — organise incidents with hierarchical categories (each with a custom icon and colour) and flat tags.
* **Emergency Statistics Widget** — displays category counts (all time / this year / this month) and a configurable list of recent emergency posts.
* **Emergency Categories Widget** — simple category list with links.
* **Gutenberg block** — drag-and-drop "Emergency Statistics" block with live preview in the editor.
* **Shortcode** — `[firefighter_stats_emergency_list_widget]` for classic editor and page builders.
* **Admin Quick Counts** — log emergencies in bulk without creating individual posts. Perfect for routine callouts that don't need full documentation.
* **Admin Bar button** — one-click "+1 emergency" logging directly from the WordPress toolbar.
* **Customisable permalink slugs** — change the URL base for emergencies, categories, and tags under **Settings > Permalinks**.
* **Fully translatable** — Polish (pl_PL) translation included; all strings use the `firefighter-stats` text domain.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install directly through the WordPress Plugins screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Go to **Emergencies > Emergency Categories** and create your first categories (e.g. Fire, Medical, Rescue).
4. Optionally assign an icon and a colour to each category.
5. Add the **Firefighter Stats Emergencies** widget to a sidebar, insert the Gutenberg block, or use the shortcode.

== Frequently Asked Questions ==

= How do I add emergency categories? =

Go to **Emergencies > Emergency Categories** in the WordPress admin. Each category supports a custom icon (chosen from a predefined set) and a background colour used in the widget and on post lists.

= Can I log emergencies without creating a full post? =

Yes. Use the **🚨 Quick Emergency** button in the admin bar, or go to **Emergencies > Quick Counts** to add manual counts grouped by category and date.

= How do I display the statistics on my site? =

Three methods are available:

1. **Widget** — go to **Appearance > Widgets** and add *Firefighter Stats Emergencies* to any sidebar.
2. **Shortcode** — place `[firefighter_stats_emergency_list_widget]` in any post or page.
3. **Gutenberg block** — search for *Emergency Statistics* in the block inserter.

= What shortcode attributes are available? =

All widget settings are available as shortcode attributes. Main options:

* `title` — widget heading
* `show_category_summary` — `true`/`false`
* `category_time_period` — `all`, `year`, or `month`
* `show_posts_list` — `true`/`false`
* `limit` — number of posts to show (default `5`)
* `order` — `default`, `date_desc`, `date_asc`, `title_asc`, `title_desc`, `random`
* `show_date` — `true`/`false`
* `show_category` — `true`/`false`
* `more_label` — label for the "see all" link; leave empty to hide
* `selected_categories` — comma-separated category IDs to filter the summary

= Can I change the URL slugs? =

Yes. Go to **Settings > Permalinks** and find the **Firefighter Stats** section at the bottom of the page.

= Is the plugin translatable? =

Yes. A Polish (`pl_PL`) translation is included. All strings use the `firefighter-stats` text domain.

= The emergency URLs show 404 errors. How do I fix this? =

Go to **Emergencies > Quick Counts** and click **Fix Emergency URLs**. This flushes the permalink rewrite rules.

== Screenshots ==

1. Emergency Statistics widget displayed on the frontend.
2. Emergency category management with icon and colour picker.
3. Emergency Counts admin page for manual bulk entry.
4. Admin bar Quick Emergency button.
5. Gutenberg block with live server-side preview.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
