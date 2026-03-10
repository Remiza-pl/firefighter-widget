# Firefighter Stats Plugin Translations

This directory contains translation files for the Firefighter Stats plugin.

## Available Translations

- **Polish (pl_PL)** - Complete translation
- **English (default)** - Source language

## Files

- `firefighter-stats.pot` - Template file for creating new translations
- `firefighter-stats-pl_PL.po` - Polish translation source
- `firefighter-stats-pl_PL.mo` - Polish compiled translation (binary)

## How to Compile Translations

### Method 1: Using gettext tools (Recommended)

If you have gettext tools installed:

```bash
msgfmt firefighter-stats-pl_PL.po -o firefighter-stats-pl_PL.mo
```

### Method 2: Using Poedit

1. Download and install [Poedit](https://poedit.net/)
2. Open the `.po` file in Poedit
3. Save the file (this automatically generates the `.mo` file)

### Method 3: Using WordPress CLI

If you have WP-CLI installed:

```bash
wp i18n make-mo languages/
```

## Creating New Translations

1. Copy `firefighter-stats.pot` to `firefighter-stats-{locale}.po`
2. Translate all strings in the new `.po` file
3. Compile to `.mo` using one of the methods above
4. Place both files in the `languages/` directory

## Translation Strings

The plugin includes translations for:

- Widget settings and labels
- Category icons and names
- Time periods and date formats
- Error messages and notifications
- Admin interface elements

## Polish Translation Details

The Polish translation includes:

- **Widget Title**: "🚨 Statystyki Wyjazdów"
- **Recent Emergencies**: "📝 Ostatnie Wyjazdy"
- **Category Names**: Proper Polish firefighting terminology
- **Time Periods**: Polish month names and time expressions
- **Plural Forms**: Correct Polish plural forms for emergency counts

## Testing Translations

1. Set WordPress language to Polish in Settings > General
2. Clear any caching plugins
3. Check that the widget displays in Polish
4. Verify all admin interface elements are translated

## Contributing Translations

To contribute a new translation:

1. Create a new `.po` file based on the `.pot` template
2. Translate all strings
3. Test the translation thoroughly
4. Submit the `.po` and `.mo` files

## Notes

- The plugin text domain is `firefighter-stats`
- All strings use proper WordPress internationalization functions
- Emojis are preserved in translations
- Polish firefighting terminology is used for accuracy
