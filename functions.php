<?php
/**
 * Plugin Name: Taxonomies Featured
 * Plugin URI: https://wpplugins.julioedi.com/tax_featured_image
 * Description: A plugin that adds featured images to taxonomies
 * Version: 1.0
 * Author: Julioedi
 * Author URI: https://julioedi.com
 * License: GPL2
 * 
 * This plugin uses Font Awesome, available under the SIL Open Font License (OFL).
 * Font Awesome: https://fontawesome.com/
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Define plugin directory URI and path if not already defined
defined( "julioedi_featured_taxonomy_uri" ) || define("julioedi_featured_taxonomy_uri", plugin_dir_url( __FILE__ ));
defined( "julioedi_featured_taxonomy_path" ) || define("julioedi_featured_taxonomy_path", plugin_dir_path( __FILE__ ));

// Include the core logic for the plugin
require_once julioedi_featured_taxonomy_path . "core.php";

// Trigger a custom action before the core is loaded
do_action("julioedi_featured_taxonomy_before_load");

// Instantiate the Core class
new julioEdi\featuredTaxonomy\Core();

function get_term_thumbnail_id(int $id): int{
  return julioEdi\featuredTaxonomy\Core::get_term_thumbnail_id($id);
}

function get_term_thumbnail(int $id, $size = 'thumbnail', string|array $attr = ''): string{
  return julioEdi\featuredTaxonomy\Core::get_term_thumbnail($id,$size,$attr );
}

// Trigger a custom action after the core is loaded
do_action("julioedi_featured_taxonomy_load");
