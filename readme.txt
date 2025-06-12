=== Advanced Featured Image for Taxonomies ===  
Contributors: julioedi  
Tags: taxonomy, featured image, thumbnails, term image, archives  
Requires at least: 5.0  
Tested up to: 6.5  
Requires PHP: 7.4  
Stable tag: 1.0  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight plugin to add featured images (thumbnails) to taxonomy terms and post type archives.

== Description ==

Advanced Featured Image for Taxonomies allows you to:

* Add featured images to taxonomy terms like categories and tags.  
* Display thumbnails in term listing tables in the WordPress admin.  
* Use featured images from taxonomies and post archives in your theme templates.  
* Access images via helper functions or directly through the term object.  
* Optimize performance by loading image references with custom filters on `get_term()` and `get_terms()`.

== Features ==

* Media upload/select field on taxonomy term edit screens.  
* Thumbnail column in taxonomy term tables.  
* `_thumbnail_id` property automatically loaded on `WP_Term`.  
* Settings submenu under **Settings > Covers**.  
* Works with default taxonomies and custom ones.  
* Clean, optimized performance.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/advance-featured-image` directory, or install it through the WordPress plugins screen directly.  
2. Activate the plugin through the 'Plugins' screen in WordPress.  
3. Go to **Settings > Covers** to manage archive images.  
4. Edit any taxonomy term to assign a featured image.

== Usage ==

To retrieve a term's image:

$term = get_term($term_id);  
$image_id = $term->_thumbnail_id;  
$image_html = wp_get_attachment_image($image_id, 'thumbnail');

== Helper functions ==

get_term_thumbnail_id($term_id);  
get_term_thumbnail($term_id);  
get_post_archive_thumbnail_id($post_type_name);

== Frequently Asked Questions ==

= Can I use this with custom taxonomies? =  
Yes. Any taxonomy that is public and has show_ui enabled will be automatically included.

= Will this affect site performance? =  
No. The plugin uses lightweight term meta and filters to avoid extra queries.

== Screenshots ==

* Featured image field in the taxonomy edit screen.  
* Thumbnails shown in term listing table.  
* Settings page for archive covers.

== Changelog ==

= 1.0 =

* Initial release.

== Upgrade Notice ==

= 1.0 =  
First stable release of the plugin.

== License ==

This plugin is licensed under the GPLv2 or later.

== Credits ==

Includes Font Awesome under the SIL Open Font License: https://fontawesome.com/license/free
