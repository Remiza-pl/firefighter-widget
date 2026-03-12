# Firefighter Statistics — WordPress Plugin

[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress](https://img.shields.io/badge/WordPress-5.9%2B-21759b)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4)](https://www.php.net/)
[![Tested up to](https://img.shields.io/badge/Tested%20up%20to-WP%206.9-0073aa)](https://wordpress.org/)

> 🇵🇱 [Czytaj po polsku → README.pl.md](README.pl.md)

---

<p align="center">
  <a href="https://remiza.pl" target="_blank">
    <img src="https://remiza.pl/wp-content/uploads/2026/01/logoR-bez-tla.png" alt="Remiza.pl" height="52">
  </a>
  <br>
  <strong>✅ Officially supported by <a href="https://remiza.pl">Remiza.pl</a></strong>
  <br>
  <sub>Poland's largest firefighter portal — news, resources, and community support</sub>
</p>

---

A WordPress plugin for fire departments to track and display emergency response statistics. Log incidents by category, show live counters in widgets or Gutenberg blocks, and let admins record new entries directly from the admin bar or from the widget on the frontend.

---

## Features

- **Custom Post Type** — dedicated `firefighter_stats` (Emergencies) post type with category and tag taxonomies
- **13 pre-seeded categories** — Fire, Medical, Rescue, Accident, Threat, Hazmat, Water Rescue, Technical, Vehicle, Structure, False Alarm, Training Exercise, Other — each with a default colour and emoji icon; fully customisable
- **Quick Counts admin page** — log counts per category and date without creating full posts; supports an optional time field and yearly/all-time filtering
- **Admin bar button** — one-click +1 count logging from any page in the WordPress admin or frontend
- **Widget** — Emergency List widget with category summary, recent posts list, configurable time period and sorting; includes an admin-only quick-actions panel on the frontend
- **Emergency Categories widget** — shows category links with counts
- **Gutenberg block** — native block (no build step required) powered by the same rendering engine as the widget
- **Shortcode** — `[firefighter_stats_emergency_list_widget]` with full attribute support
- **Bilingual (EN/PL)** — all admin UI and default content available in English and Polish without requiring a compiled MO file; full Polish `.po`/`.mo` included

---

## Requirements

| Requirement | Minimum |
|-------------|---------|
| WordPress | 5.9 |
| PHP | 7.4 |
| Tested up to WP | 6.9 |

---

## Installation

1. Download the latest release `.zip` file from the [Releases page](https://github.com/Remiza-pl/firefighter-widget/releases/latest).
2. In the WordPress admin, go to **Plugins → Add New Plugin → Upload Plugin**, choose the downloaded `.zip` file, and click **Install Now**.
3. Activate the plugin through **Plugins → Installed Plugins** in the WordPress admin.
4. On first activation, 13 default categories are seeded in the language of your site.
5. Go to **Settings → Permalinks** and click **Save Changes** to flush rewrite rules.
6. Add the **Firefighter Stats Emergencies** widget to a sidebar, insert the Gutenberg block, or use the shortcode.

---

## Usage

### Adding the Widget

Go to **Appearance → Widgets** (or use the Site Editor for block themes) and add **Firefighter Stats Emergencies**. The widget settings panel lets you configure:

- Which time period to count (all time / this year / this month)
- Which categories to include and how to sort them
- Whether to show the recent posts list and how many posts

**Admin quick-actions panel** — when you are logged in as an administrator and viewing the frontend, a small ⚡ Quick Actions bar appears at the bottom of each widget. From there you can log a count or open the new-post screen without going to the admin.

### Shortcode

```
[firefighter_stats_emergency_list_widget]
```

All available attributes:

| Attribute | Default | Description |
|-----------|---------|-------------|
| `title` | `🚨 Emergency Statistics` | Widget heading |
| `show_category_summary` | `true` | Show category count grid |
| `category_time_period` | `all` | `all` / `year` / `month` |
| `selected_categories` | *(all)* | Comma-separated term IDs to include |
| `category_sort` | `alphabet` | `alphabet` / `count_desc` / `count_asc` |
| `show_zero_categories` | `true` | Include categories with 0 count |
| `show_posts_list` | `true` | Show recent emergency posts |
| `category` | *(all)* | Filter posts to one category (term ID) |
| `limit` | `5` | Max number of posts to display |
| `order` | `default` | `default` / `date_desc` / `date_asc` / `title_asc` / `title_desc` / `random` |
| `show_date` | `true` | Display post date |
| `show_category` | `true` | Display post category badge |
| `recent_emergencies_title` | `📝 Recent Emergencies` | Heading for the posts section |
| `more_label` | *(hidden)* | "See all" link label — leave empty to hide |
| `id` | *(none)* | Custom HTML `id` attribute on the wrapper |
| `className` | *(none)* | Extra CSS class on the wrapper |

**Example:**
```
[firefighter_stats_emergency_list_widget category_time_period="year" limit="10" show_posts_list="false"]
```

### Quick Counts Admin Page

Navigate to **Emergencies → Quick Counts** to:
- View category cards with current year totals and last-entry dates
- Log counts by clicking a card → entering count, date, and optional time in the modal
- Filter entries by year using the tab bar
- Delete individual entries

### Gutenberg Block

In the block editor, search for **Emergency Statistics** (category: Widgets). The block shares all settings with the shortcode and renders identically on the frontend.

---

## Page Builder Support

| Builder | Integration type | How |
|---------|-----------------|-----|
| **Elementor** | Native widget | Search "Emergency Statistics" in the Elementor widget panel → drag & drop |
| **WPBakery** | Native element | Available in the Classic Backend Editor element list |
| **Beaver Builder** | Native module | Available in the Beaver Builder module panel under "Emergency Statistics" |
| **Bricks** | Native element | Available in the Bricks element panel under "Emergency Statistics" |
| **Divi** | Shortcode module | Insert a Text / Code module and paste `[firefighter_stats_emergency_list_widget]` |
| **Oxygen** | Shortcode widget | Add a Shortcode widget and paste `[firefighter_stats_emergency_list_widget]` |
| **Any other builder** | Shortcode | Use `[firefighter_stats_emergency_list_widget]` in any shortcode-capable field |

All native integrations expose the same 13 controls as the shortcode (except `selected_categories` — use the shortcode directly for multiselect filtering).

---

## Customisation

### Custom Template

Override the widget/shortcode template by copying:

```
templates/widgets/emergency-list.php → yourtheme/firefighter-stats/widgets/emergency-list.php
```

Use the filter to point to your file:

```php
add_filter( 'firefighter_stats_widget_emergency_list_template_path', function( $path ) {
    $custom = get_template_directory() . '/firefighter-stats/widgets/emergency-list.php';
    return file_exists( $custom ) ? $custom : $path;
} );
```

### Category Icons and Colours

Go to **Emergencies → Emergency Categories**, edit any category, and set a custom emoji icon and hex colour. These are used throughout the widget, block, and admin pages.

### Default Categories

The 13 default categories are seeded on activation. You can rename, delete, or add your own — they are ordinary taxonomy terms. The seeder is idempotent (safe to re-run) and uses the site locale, so a Polish site gets Polish names.

Filter the seed list before it is created:

```php
add_filter( 'firefighter_stats_default_categories', function( $categories ) {
    $categories[] = array(
        'name'        => 'Flood',
        'slug'        => 'flood',
        'description' => 'Flood response',
        'icon'        => '🌧️',
        'color'       => '#5dade2',
    );
    return $categories;
} );
```

### Shortcode Attributes

Extend accepted shortcode attributes without touching plugin code:

```php
add_filter( 'firefighter_stats_emergency_list_widget_shortcode_atts', function( $extra ) {
    $extra['my_param'] = 'default_value';
    return $extra;
} );
```

### Custom Permalink Slugs

Go to **Settings → Permalinks** — a **Firefighter Stats** section at the bottom lets you change the URL slugs for posts, categories, and tags.

---

## Hooks Reference

| Hook | Type | Description |
|------|------|-------------|
| `firefighter_stats_widget_emergency_list_template_path` | filter | Override the emergency list widget template |
| `firefighter_stats_widget_emergency_categories_template_path` | filter | Override the emergency categories widget template |
| `firefighter_stats_default_categories` | filter | Modify default categories before seeding |
| `firefighter_stats_emergency_list_widget_shortcode_atts` | filter | Add extra shortcode attributes |
| `firefighter_stats_cpt_wp_args` | filter | Override CPT registration arguments |
| `firefighter_stats_cat_tax_wp_args` | filter | Override category taxonomy registration arguments |
| `firefighter_stats_tag_tax_wp_args` | filter | Override tag taxonomy registration arguments |

---

## File Structure

```
firefighter-widget/
├── firefighter-stats.php              # Plugin entry point
├── uninstall.php                      # Cleanup on uninstall
├── readme.txt                         # WordPress.org readme
├── assets/
│   ├── css/
│   │   ├── admin.css                  # Admin + quick-actions styles
│   │   └── firefighter-stats-widget.css  # Frontend widget styles
│   └── js/
│       ├── admin-quick-add.js         # Admin bar quick-add
│       ├── admin-counts.js            # Quick Counts page + widget panel
│       └── block-editor.js            # Gutenberg block editor (no build step)
├── blocks/
│   └── emergency-list-widget/
│       └── block.json                 # Block schema and attributes
├── inc/
│   ├── core-functions.php             # Shared helper functions
│   ├── blocks-config.php              # Block registration
│   ├── integrations/
│   │   ├── load.php                   # Integration hook registrations
│   │   ├── elementor.php              # Elementor widget
│   │   ├── wpbakery.php               # WPBakery vc_map element
│   │   ├── beaver-builder.php         # Beaver Builder module
│   │   ├── beaver-builder/
│   │   │   └── includes/
│   │   │       └── frontend.php       # BB module template
│   │   └── bricks.php                 # Bricks element
│   └── classes/
│       ├── firefighter-stats-cpt.php
│       ├── firefighter-stats-cpt-notice.php
│       ├── firefighter-stats-widget.php
│       ├── firefighter-stats-category-meta.php
│       ├── firefighter-stats-admin-counts.php
│       ├── firefighter-stats-admin-guide.php
│       ├── firefighter-stats-category-seeder.php
│       ├── firefighter-stats-permalink-settings.php
│       ├── shortcodes/
│       │   └── firefighter-stats-shortcode-emergency-list-widget.php
│       └── widgets/
│           ├── firefighter-stats-widget-emergency-list.php
│           └── firefighter-stats-widget-emergency-categories.php
├── languages/
│   ├── firefighter-stats.pot
│   ├── firefighter-stats-pl_PL.po
│   └── firefighter-stats-pl_PL.mo
└── templates/
    └── widgets/
        ├── emergency-list.php
        └── emergency-categories.php
```

---

## Contributing

Pull requests and issues are welcome.

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Commit your changes: `git commit -m "Add my feature"`
4. Push: `git push origin feature/my-feature`
5. Open a Pull Request

Please keep PHP compatibility at **7.4+** and avoid introducing build tools — the block editor script is intentionally plain ES5.

---

## Remiza.pl API — Direct Integration

Any website — WordPress or not — can submit emergency reports directly to the [Remiza.pl](https://remiza.pl) statistics receiver without installing this plugin.

**Base URL:** `https://remiza.pl/wp-json/remiza-stats/v1`
No WordPress authentication required. Authorization is handled via a site token issued at registration.

### 1. Register your site

```bash
curl -X POST https://remiza.pl/wp-json/remiza-stats/v1/register \
  -H "Content-Type: application/json" \
  -d '{"site_url":"https://your-osp.pl","site_name":"OSP Your Unit"}'
```

Response `201 Created`:
```json
{
  "token": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
  "domain_label": "your-osp.pl"
}
```

Store the token securely — it authorises all future `/report` calls. If the domain was already registered, a suffix is appended to `domain_label` (e.g. `your-osp.pl-2`) and historical data is preserved.

**Rate limit:** 5 registrations per IP per hour.

### 2. Submit a report

```bash
curl -X POST https://remiza.pl/wp-json/remiza-stats/v1/report \
  -H "Content-Type: application/json" \
  -d '{
    "token": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "event": "new_emergency",
    "post_title": "Fire at residential building in Łagisz",
    "post_url": "https://your-osp.pl/news/fire-2026-03-11",
    "post_excerpt": "Unit was dispatched to a residential fire...",
    "category_slug": "fire",
    "category_name": "Fire",
    "category_icon": "🔥",
    "emergency_date": "2026-03-11",
    "reported_at": "2026-03-11T17:45:00+01:00"
  }'
```

Response `200 OK`:
```json
{ "status": "ok" }
```

### Report fields

| Field | Required | Description |
|-------|----------|-------------|
| `token` | **Yes** | Token from `/register` |
| `post_title` | **Yes** | Title of the emergency post |
| `post_url` | **Yes** | Full URL — shown as a clickable link in the receiver admin |
| `emergency_date` | **Yes** | Date in `YYYY-MM-DD` or ISO 8601 |
| `event` | No | Event type slug (default: `new_emergency`) |
| `post_excerpt` | No | Short excerpt from the post body |
| `category_slug` | No | Category slug (e.g. `fire`) |
| `category_name` | No | Human-readable category name |
| `category_icon` | No | Emoji icon (e.g. `🔥`) |
| `reported_at` | No | ISO 8601 timestamp of the event |

### Error codes

| Status | Code | Meaning |
|--------|------|---------|
| `400` | `missing_site_url` | `/register`: `site_url` missing |
| `400` | `missing_token` | `/report`: `token` missing |
| `401` | `invalid_token` | Token not recognised or site inactive |
| `429` | `rate_limit_exceeded` | Too many registrations (5 / IP / hour) |
| `500` | `registration_failed` / `storage_failed` | Server-side error |

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for the full version history.

---

## License

[GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html)
