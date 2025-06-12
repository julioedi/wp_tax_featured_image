# Advanced Featured Image for Taxonomies

**A lightweight WordPress plugin to add featured images (thumbnails) to taxonomy terms.**
Stores image references in a custom `_thumbnail_id` column directly in the `wp_terms` table to minimize extra queries and optimize performance.

## 🚀 Features
- Adds media upload/select field to term edit screens.
- Displays thumbnail column in taxonomy term tables.
- loads `_thumbnail_id` directly in `WP_Term` element.
- Integrates into `get_term()` and `get_terms()` with filters for automatic image availability.
- FontAwesome support for empty-state icons.
- Admin submenu under **Settings > Covers**.

## 📂 Installation
1. Download the plugin ZIP.
2. Extract to your `/wp-content/plugins/` directory.
3. Activate the plugin via **Plugins > Installed Plugins** in WordPress admin.

## 🧠 Developer Notes
```php
// To retrieve a term’s thumbnail:
$term = get_term($term_id);
$image_id = $term->_thumbnail_id;
$image_html = wp_get_attachment_image($image_id, 'thumbnail');
```

```php
/**
 * @return {int}
 * @example 19
 */
get_term_thumbnail_id($term_id);

/**
 * @return {string} 
 * @example <img src...>
 */
get_term_thumbnail($term_id);

/**
 * @return {int}
 */
get_post_archive_thumbnail_id($name)
```

## 🛠️ Requirements
- WordPress 5.0+
- PHP 7.4+

## 📄 License
GPLv2 or later
