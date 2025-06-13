<?php

/**
 * Plugin Name: Advanced Featured Images
 * Plugin URI: https://wpplugins.julioedi.com/adv_featured_image
 * Description: A plugin that adds featured images to taxonomies and post archives
 * Version: 1.0
 * Author: Julioedi
 * Author URI: https://julioedi.com
 * License: GPL2
 * Text Domain: julioedi-advance-featured-image
 *
 * This plugin uses Font Awesome, available under the SIL Open Font License (OFL).
 * Font Awesome: https://fontawesome.com/
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Define plugin directory URI and path if not already defined
defined("julioedi_advanced_featured_image_uri") || define("julioedi_advanced_featured_image_uri", plugin_dir_url(__FILE__));
defined("julioedi_advanced_featured_image_path") || define("julioedi_advanced_featured_image_path", plugin_dir_path(__FILE__));

function afi_load_plugin_textdomain() {
    load_plugin_textdomain( 'julioedi-advance-featured-image', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'afi_load_plugin_textdomain' );


function julioedi_adv_featured_template_select_image(int $thumbnail_id, string $tag, string $input_name = '_thumbnail_id')
{
  $is_image = get_post($thumbnail_id);
  $deletebtn = '<div class="delete_cover"><div class="tax_icon_button"><i class="fa-solid fa-trash"></i></div></div>';
  if (empty($is_image)) {
    // If there's an image, show it with a delete button
    $thumbnail_id = "0";
  }
?>
  <div class="adv_custom_cover_image form-field term-thumbnail_id-wrap <?php echo esc_html($tag )?>">
    <div class="adv_custom_cover_image_input_wrap">
      <input type="text" name="<?php echo esc_html($input_name) ?>" value="<?php echo esc_html($thumbnail_id)  ?>" hidden>
    </div>
    <div class="adv_custom_preview_cover"><?php if ( !empty($is_image) ): ?>
        <?php echo wp_get_attachment_image($thumbnail_id,false,array("data-id"=> $thumbnail_id)) ?>
        <div class="delete_cover"><div class="tax_icon_button"><i class="fa-solid fa-trash"></i></div></div>
    <?php endif; ?></div>
    <div class="adv_custom_cover_no_image">
      <div class="tax_btn"><?php esc_html_e("Select featured image", "julioedi-advance-featured-image") ?></div>
    </div>
  </div>
<?php
}

// Include the core logic for the plugin
require_once julioedi_advanced_featured_image_path . "tax.php";
require_once julioedi_advanced_featured_image_path . "archives.php";

// Trigger a custom action before the core is loaded
do_action("julioedi/adv_featured/before_load");

// Instantiate the Core class
new julioEdi\AdvanceFeaturedImage\Tax();
new julioEdi\AdvanceFeaturedImage\Archives();



function julioedi_adv_featured_image_enqueues()
{
  $fontAwesome = "font_awesome_all";
  $fontAwesomeUri = julioedi_advanced_featured_image_uri . "/assets/font_awesome/load.css";

  // Register the style only if it hasn't been registered yet
  if (!wp_style_is($fontAwesome, "registered")) {
    wp_register_style($fontAwesome, $fontAwesomeUri, [], "6.0.0", "all");
  }

  $generateCSS = "generate_css";
  $generateCSSUri = julioedi_advanced_featured_image_uri . "/assets/js/generatecss.min.js";
  if (!wp_script_is('generate_css')) {
    wp_register_script($generateCSS, $generateCSSUri, [], "1.0.0", false);
  }
}
add_action('init', 'julioedi_adv_featured_image_enqueues'); // Register Font Awesome on init



function julioedi_adv_featured_image_admin_assets($hook)
{
  // Asegúrate que solo cargue donde lo necesitas
  if (in_array($hook, ["edit-tags.php", "term.php", 'settings_page_adv_featured_image'])) {
    wp_enqueue_style("font_awesome_all");
    wp_enqueue_style("julioedi_featured_image_css", julioedi_advanced_featured_image_uri . "/assets/css/edit_featured_image.css",[],"1.0.0");
    wp_enqueue_script("generate_css");
    wp_enqueue_script("julioedi_adv_featured_image_edit", julioedi_advanced_featured_image_uri . "/assets/js/edit_featured_image.js", ['jquery'], "1.0.0", true);

    wp_enqueue_media(); // Solo si necesitas el uploader
  }
}
add_action("admin_enqueue_scripts", "julioedi_adv_featured_image_admin_assets");


function get_term_thumbnail_id(int $id): int
{
  return julioEdi\AdvanceFeaturedImage\Tax::get_term_thumbnail_id($id);
}

function get_term_thumbnail(int $id = -1, $size = 'thumbnail', string|array $attr = ''): string
{
  if ($id < 0) {
    $id = get_queried_object()->term_id ?? 0;
  }
  return julioEdi\AdvanceFeaturedImage\Tax::get_term_thumbnail($id, $size, $attr);
}

function get_post_archive_thumbnail_id(string|null $name = null): int
{
  if (is_archive() && !$name) {
    $name = get_queried_object()->name  ??  null;
    if ($name) {
      $thumbnail_id = get_option("julioedi/adv_featured/archives/$name", "0");
      return is_numeric($thumbnail_id) ? (int) $thumbnail_id : 0;
    }
    return "0";
  }
  if ($name) {
    $thumbnail_id = get_option("julioedi/adv_featured/archives/$name", "0");
    return is_numeric($thumbnail_id) ? (int) $thumbnail_id : 0;
  }
  return 0;
}

function get_post_archive_thumbnail(int $post_type): string
{
  $thumbnail_id = get_post_archive_thumbnail_id($post_type);
  return apply_filters("julioedi/adv_featured/post_archive_thumbnail", wp_get_attachment_image($thumbnail_id), $post_type);
}



function get_taxonomy_archive_thumbnail_id(string|null $name = null): int
{
  if ((is_tax() || is_category() || is_tag())  && !$name) {
    $name = get_queried_object()->name  ??  null;
    if ($name) {
      $thumbnail_id = get_option("julioedi/adv_featured/archives/$name", "0");
      return is_numeric($thumbnail_id) ? (int) $thumbnail_id : 0;
    }
    return 0;
  }
  if ($name) {
    $thumbnail_id = get_option("julioedi/adv_featured/archives/$name", "0");
    return is_numeric($thumbnail_id) ? (int) $thumbnail_id : 0;
  }
  return 0;
}

function get_taxonomy_archive_thumbnail(string|null $name = null, $size = 'thumbnail', string|array $attr = ''): string
{
  $id = get_taxonomy_archive_thumbnail_id($name);
  $size = apply_filters('julioedi/adv_featured/taxonomies/thumbnail_id', $size, $id);
  $html = wp_get_attachment_image($id, $size, false, $attr);
  return apply_filters('julioedi/adv_featured/taxonomies/thumbnail_html', $html, $id, $id, $size, $attr);
}


// Trigger a custom action after the core is loaded
do_action("julioedi/adv_featured/loaded");

// add_action("frontend/content/before",function(){
//     $meta_key = '_thumbnail_id';
//     $keys = [];
//     global $wp_taxonomies;
//     foreach ($wp_taxonomies as $key => $value) {
//       $keys[] = $key;
//     }
//     // Get all term IDs associated with the post
//     $term_ids = get_term_meta([75,"75"], $keys, array(
//       'fields' => 'ids',
//     )); // Adjust taxonomy as needed
//     echo "<pre>";
//     $terms = get_terms(array(
//       'meta_query' => array(
//         array(
//             'key'     => $meta_key, // The custom field key
//             'value'   => 75,         // The value you are looking for
//             'compare' => '=',          // Comparison operator
//         ),
//     ),
//     'fields' => 'ids', // Optional: if you only want the term IDs
//     ));
//     echo json_encode($terms,JSON_PRETTY_PRINT);
//     echo "</pre>";
// });