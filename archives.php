<?php


namespace julioEdi\AdvanceFeaturedImage;

class Archives
{
    public function __construct()
    {
        add_action("admin_menu", [$this, "register_menu_page"]);
    }

    public function register_menu_page()
    {
        $title = __("Covers", "julioedi_advance_featured_image_lang");
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
    public function get_public_archives():array
    {
        global $wp_post_types;
        $list = [];
        foreach ($wp_post_types as $key => $value) {
           if ($value->has_archive) {
            $list[] = $key;
           }
        }
        return $list;
    }

    public function callback()
    {
        require_once julioedi_advance_featured_image_path . "archives_render.php";
    }
}
