<?php

/**
 * Plugin Name: Advance Featured Images
 * Plugin URI: https://wpplugins.julioedi.com/adv_featured_image
 * Description: A plugin that adds featured images to taxonomies and post archives
 * Version: 1.0
 * Author: Julioedi
 * Author URI: https://julioedi.com
 * License: GPL2
 * 
 * This plugin uses Font Awesome, available under the SIL Open Font License (OFL).
 * Font Awesome: https://fontawesome.com/
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Define plugin directory URI and path if not already defined
defined("julioedi_advance_featured_image_uri") || define("julioedi_advance_featured_image_uri", plugin_dir_url(__FILE__));
defined("julioedi_advance_featured_image_path") || define("julioedi_advance_featured_image_path", plugin_dir_path(__FILE__));



function julioedi_adv_featured_template_select_image(int $thumbnail_id, string $tag, string $input_name = '_thumbnail_id')
{
  $is_image = wp_get_attachment_url($thumbnail_id);
  $deletebtn = '<div class="delete_cover"><div class="tax_icon_button"><i class="fa-solid fa-trash"></i></div></div>';
  if (!empty($is_image)) {
    // If there's an image, show it with a delete button
    $is_image = sprintf('<img src="%s" data-id="%s">' . $deletebtn, $is_image, $thumbnail_id);
  } else {
    $thumbnail_id = "0";
  }
  $preview = sprintf('<div class="adv_custom_preview_cover">%s</div>', $is_image);
?>
  <div class="adv_custom_cover_image form-field term-thumbnail_id-wrap <?php echo $tag ?>">
    <div class="adv_custom_cover_image_input_wrap">
      <input type="text" name="<?php echo $input_name ?>" value="<?php echo $thumbnail_id  ?>">
    </div>
    <?php echo $preview ?>
    <div class="adv_custom_cover_no_image">
      <div class="tax_btn"><?php _e("Select featured image", "julioedi_advance_featured_image_path") ?></div>
    </div>
  </div>
<?php
}

// Include the core logic for the plugin
require_once julioedi_advance_featured_image_path . "tax.php";
require_once julioedi_advance_featured_image_path . "archives.php";

// Trigger a custom action before the core is loaded
do_action("julioedi_advance_featured_image_before_load");

// Instantiate the Core class
new julioEdi\AdvanceFeaturedImage\Tax();
new julioEdi\AdvanceFeaturedImage\Archives();



function julioedi_adv_featured_image_enqueues()
{
  $fontAwesome = "font_awesome_all";
  $fontAwesomeUri = julioedi_advance_featured_image_uri . "/assets/font_awesome/load.css";

  // Register the style only if it hasn't been registered yet
  if (!wp_style_is($fontAwesome, "registered")) {
    wp_register_style($fontAwesome, $fontAwesomeUri, [], "6.0.0", "all");
  }

  $generateCSS = "generate_css";
  $generateCSSUri = julioedi_advance_featured_image_uri . "/assets/js/generatecss.min.js";
  if (!wp_script_is('generate_css')) {
    wp_register_script($generateCSS, $generateCSSUri, [], "1.0.0", false);
  }
}
add_action('init', 'julioedi_adv_featured_image_enqueues'); // Register Font Awesome on init



function julioedi_adv_featured_image_admin_head()
{
  global $pagenow;
  if (in_array($pagenow, ["term.php", "edit-tags.php", 'options-general.php'])) {
    wp_scripts()->done[] = "generate_css";
    echo '<script src="' . julioedi_advance_featured_image_uri  . '/assets/js/generatecss.min.js"></script>';
    wp_enqueue_style("font_awesome_all");
    wp_enqueue_media();
    echo '<link rel="stylesheet" href="' . julioedi_advance_featured_image_uri . '/assets/css/edit_featured_image.css" media="all">';
    wp_enqueue_script("julioedi_adv_featured_image_edit", julioedi_advance_featured_image_uri . "/assets/js/edit_featured_image.js");
  }
}
add_action("admin_head", "julioedi_adv_featured_image_admin_head");

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

function get_post_archive_thumbnail(string|null $name = null): string
{
  if (is_archive() && !$name) {
    $name = get_queried_object()->name  ??  null;
    if ($name) {
      $thumbnail_id = get_option("julioedi/adv_featured/archives/$name", "0");
      return is_numeric($thumbnail_id) ? (string) $thumbnail_id : "0";
    }
    return "0";
  }
  if ($name) {
    $thumbnail_id = get_option("julioedi/adv_featured/archives/$name", "0");
    return is_numeric($thumbnail_id) ? (string) $thumbnail_id : "0";
  }
  return "0";
}


function get_taxonomy_archive_thumbnail(string|null $name = null): int
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
    return is_numeric($thumbnail_id) ? (string) $thumbnail_id : 0;
  }
  return 0;
}

// Trigger a custom action after the core is loaded
do_action("julioedi_advance_featured_image_load");
