<?php
$types = $this->get_public_archives();
$col = \julioEdi\AdvanceFeaturedImage\Tax::$column;
$post_key = "julioedi/adv_featured/archives";
$tax_key = "julioedi/adv_featured/taxonomies";

// $noValidate = !isset($_POST["{$col}_nonce"]) || !wp_verify_nonce($_POST["{$col}_nonce"], "save_archive{$col}");
if (isset($_POST["{$col}_nonce"]) && wp_verify_nonce($_POST["{$col}_nonce"], "save_archive{$col}")) {
    if (isset($_POST["thumbnails"]) && is_array($_POST["thumbnails"])) {
        if (is_array(($_POST["thumbnails"]["post_types"]))) {
            foreach ($_POST["thumbnails"]["post_types"] as $key => $value) {
                if (is_numeric(($value))) {
                    update_option("$post_key/$key", $value);
                }
            }
        }
        if (is_array(($_POST["thumbnails"]["taxonomies"]))) {
            foreach ($_POST["thumbnails"]["taxonomies"] as $key => $value) {
                if (is_numeric(($value))) {
                    update_option("$tax_key/$key", $value);
                }
            }
        }
    }
}
global $wp_taxonomies;
foreach ($wp_taxonomies as $key => $value) {
    $pre[$key] = $value->public && $value->show_ui ? $value : null;
}
$taxonomies = (array) apply_filters("julioedi_advance_featured_image/taxonomies/featured/edit", $pre);
$admin_url = admin_url("options-general.php?page=adv_featured_image");
?>
<form class="p-24" action="<?php echo esc_html($admin_url) ?>" method="POST">
    <?php
    wp_nonce_field("save_archive{$col}", "{$col}_nonce");
    ?>
    <h2 class="archives_titles"><?php esc_html_e("Archives", "julioedi-advance-featured-image") ?></h2>
    <div class="display-grid grid-4 md:grid-2 sm:grid-1 gap-24 pb-48">
        <?php foreach ($types as $post_type):
            $title = get_post_type_object($post_type);
            $thumbnail_id = get_option("$post_key/$post_type", "0");
        ?>
            <div class='archive-item' id="archive_<?php echo esc_html($post_type) ?>">
                <h3><?php echo esc_html($title->labels->name) ?></h3>
                <?php julioedi_adv_featured_template_select_image($thumbnail_id, "archive_img", "thumbnails[post_types][$post_type]"); ?>
            </div>
        <?php endforeach; ?>
    </div>
    <h2 class="archives_titles"><?php esc_html_e("Taxonomies", "julioedi-advance-featured-image") ?></h2>
    <div class="display-grid grid-4 md:grid-2 sm:grid-1 gap-24 pb-48">
        <?php foreach ($taxonomies as $key => $tax):
            if (!$tax) {
                continue;
            }
            $title = $tax->labels->name;
            $thumbnail_id = get_option("$tax_key/$key", "0");
        ?>
            <div class='archive-item' id='tax_<?php echo esc_html($key) ?>'>
                <h3><?php echo esc_html($title) ?></h3>
                <?php julioedi_adv_featured_template_select_image($thumbnail_id, "archive_img", "thumbnails[taxonomies][$key]"); ?>
            </div>
        <?php endforeach; ?>
    </div>
    <input type="submit" class="button button-primary" value="<?php esc_html_e("Save", "julioedi-advance-featured-image") ?>">
</form>
<?php

$txts = array(
    "title" => __('Select or Upload an Image', 'julioedi-advance-featured-image'),
    "text" => __('Use this image', 'julioedi-advance-featured-image')
);
echo "<script>window.__wp_adv_featured_image_msg = " . json_encode($txts) . " </script>";
