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
        global $wpdb;
        $archives = "%julioedi/adv_featured/archives/%";
        $tax = "%julioedi/adv_featured/taxonomies/category/%";

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} 
             WHERE (option_name LIKE %s OR option_name LIKE %s) 
             AND option_value = %d",
                $archives,
                $tax,
                $post_id
            )
        );
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
        $path = julioedi_advance_featured_image_path . "archives_render.php";
        if (file_exists($path)) {
            require_once $path;
        } else {
            echo '<div class="notice notice-error"><p>Error: ' . __("Archive render file not available","julioedi-advance-featured-image") . '</p></div>';
        }
    }
}
