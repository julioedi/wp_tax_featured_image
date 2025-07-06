<?php


namespace julioEdi\AdvanceFeaturedImage;

class Archives
{
    public function __construct()
    {
        add_action("admin_menu", [$this, "register_menu_page"]);
        add_action("delete_post", [$this, "on_delete_post"]);
    }

    public function on_delete_post($post_id)
    {
        // Fetch all options
        $options = get_option('alloptions');

        if ($options) {
            // Regex to match options starting with 'julioedi/adv_featured/archives/' or 'julioedi/adv_featured/taxonomies/category/'
            $reg = "/^julioedi\/adv_featured\/(archives\/|taxonomies\/category\/)/";

            foreach ($options as $option_name => $option_value) {
                // Check if the option name matches the regex pattern
                if (preg_match($reg, $option_name)) {
                    // If option value matches the post_id, delete the option
                    if ($option_value == $post_id) {
                        delete_option($option_name);
                    }
                }
            }
        }
    }


    public function register_menu_page()
    {
        $title = __("Covers", "julioedi-advance-featured-image");
        add_submenu_page(
            'options-general.php',
            $title,
            $title,
            'manage_options',
            'adv_featured_image',
            [$this, "callback"],
            8
        );
    }
    public function get_public_archives(): array
    {
        global $wp_post_types;
        $list = [];
        foreach ($wp_post_types as $key => $value) {
            if ($value->has_archive && $value->public && $value->show_ui) {
                $list[] = $key;
            }
        }
        return $list;
    }

    public function callback()
    {
        $path = julioedi_advanced_featured_image_path . "archives_render.php";
        if (file_exists($path)) {
            require_once $path;
        } else {
            echo esc_html('<div class="notice notice-error"><p>Error: ' . __("Archive render file not available", "julioedi-advance-featured-image") . '</p></div>');
        }
    }
}
