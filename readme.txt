=== Firefighter Statistics ===
Contributors: sync667
Tags: firefighter, emergency, statistics, widget, fire department
Requires at least: 5.9
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.6
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track and display emergency statistics for fire departments. Widgets, Gutenberg block, shortcode included.

== Description ==

*Polska wersja: `readme-pl_PL.txt`.*



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

Yes. A Polish (`pl_PL`) translation is included. All strings use the `firefighter-widget` text domain.

= The emergency URLs show 404 errors. How do I fix this? =

Go to **Emergencies > Quick Counts** and click **Fix Emergency URLs**. This flushes the permalink rewrite rules.

== Screenshots ==

1. Emergency Statistics widget displayed on the frontend.
2. Emergency category management with icon and colour picker.
3. Emergency Counts admin page for manual bulk entry.
4. Admin bar Quick Emergency button.
5. Gutenberg block with live server-side preview.

== Changelog ==

See [CHANGELOG.md](https://github.com/OSP-Lagisza/firefighter-stats/blob/main/CHANGELOG.md) for the full version history.

== Remiza.pl API — Direct Integration ==

Any website — WordPress or not — can submit emergency reports directly to the Remiza.pl statistics receiver without installing this plugin. No WordPress authentication is required.

**Base URL:** `https://remiza.pl/wp-json/remiza-stats/v1`

= Step 1: Register your site =

Send a POST request with your site URL and name to receive a unique token:

    POST /register
    {"site_url":"https://your-osp.pl","site_name":"OSP Your Unit"}

    Response: {"token":"a1b2c3d4-...","domain_label":"your-osp.pl"}

Store the token securely — it authorises all future report submissions. Rate limit: 5 registrations per IP per hour.

= Step 2: Submit reports =

    POST /report
    {
        "token": "a1b2c3d4-...",
        "post_title": "Fire at residential building",
        "post_url": "https://your-osp.pl/news/fire-2026-03-11",
        "category_slug": "fire",
        "category_name": "Fire",
        "category_icon": "🔥",
        "emergency_date": "2026-03-11"
    }

Required fields: `token`, `post_title`, `post_url`, `emergency_date`. All other fields are optional.

== External Services ==

This plugin can optionally send anonymised emergency statistics to **Remiza.pl** (https://remiza.pl), Poland's largest firefighter portal, so the portal can display aggregated national emergency activity.

**What is sent:** site name, site URL, post title, a 30-word excerpt of the post content, emergency category (slug, name, icon), emergency date, and plugin version. No personal data, user IDs, or IP addresses are ever transmitted.

**When it is sent:** only when a `firefighter_stats` post is first published, and only if reporting has not been disabled by the site administrator.

**How to disable:** A consent notice is shown to administrators on first use. Reporting can be disabled at any time via **Emergencies → Settings**.

Remiza.pl service: https://remiza.pl
Remiza.pl privacy policy: https://remiza.pl/polityka-prywatnosci/

== Upgrade Notice ==

= 1.0.0 =
Initial release.
