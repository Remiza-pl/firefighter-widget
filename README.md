# Firefighter Statistics — WordPress Plugin

[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress](https://img.shields.io/badge/WordPress-5.9%2B-21759b)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4)](https://www.php.net/)
[![Tested up to](https://img.shields.io/badge/Tested%20up%20to-WP%206.7-0073aa)](https://wordpress.org/)

> 🇵🇱 [Czytaj po polsku → README.pl.md](README.pl.md)

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
| Tested up to WP | 6.7 |

---

## Installation

1. Download or clone this repository into your `/wp-content/plugins/` directory:
   ```bash
   git clone https://github.com/sync667/firefighter-widget.git firefighter-widget
   ```
2. Activate the plugin through **Plugins → Installed Plugins** in the WordPress admin.
3. On first activation, 13 default categories are seeded in the language of your site.
4. Go to **Settings → Permalinks** and click **Save Changes** to flush rewrite rules.
5. Add the **Firefighter Stats Emergencies** widget to a sidebar, insert the Gutenberg block, or use the shortcode.

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

## Changelog

### 1.0.0
- Initial release
- Custom Post Type, taxonomies, 13 default categories
- Emergency List and Emergency Categories widgets
- Quick Counts admin page with modal, year filter, time field
- Admin bar quick-add button
- Gutenberg block (no build step)
- Shortcode with full attribute support
- Admin quick-actions panel on the frontend widget (admins only)
- Getting Started guide page
- Category enforcement on publish (reverts to draft if no category)
- Polish translation included
- Bilingual admin UI (EN/PL) without requiring compiled MO

---

## License

[GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html)
