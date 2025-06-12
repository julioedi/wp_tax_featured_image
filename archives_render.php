<?php
$types = $this->get_public_archives();

$post_key = "julioedi/adv_featured/archives";
$tax_key = "julioedi/adv_featured/taxonomies";
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
global $wp_taxonomies;
foreach ($wp_taxonomies as $key => $value) {
    $pre[$key] = $value->public && $value->show_ui ? $value : null;
}
$taxonomies = (array) apply_filters("julioedi_advance_featured_image/taxonomies/featured/edit", $pre);

$preClass = '<div class="display-grid grid-4 md:grid-2 sm:grid-1 gap-24 pb-48" >';
echo '<form class="p-24"action="' . admin_url(("options-general.php?page=adv_featured_image")) . '" method="POST">';
echo '<h2 class="archives_titles">'.  __("Archives") . '</h2>';
echo $preClass;
foreach ($types as $post_type) {
    echo "<div class='archive-item' id='archive_$post_type'>";
    $title = get_post_type_object($post_type);
    echo "<h3>{$title->labels->name}</h3>";
    $thumbnail_id = get_option("$post_key/$post_type", "0");
    julioedi_adv_featured_template_select_image($thumbnail_id, "archive_img", "thumbnails[post_types][$post_type]");
    echo "</div>";
}
echo "</div>";

echo '<h2 class="archives_titles">'.  __("Taxonomies") . '</h2>';
echo $preClass;
foreach ($taxonomies as $key => $tax) {
    if (!$tax) {
        continue;
    }
    $title = $tax->labels->name;
    echo "<div class='archive-item' id='tax_$key'>";
    echo "<h3>{$title}</h3>";
    $thumbnail_id = get_option("$tax_key/$key", "0");
    julioedi_adv_featured_template_select_image($thumbnail_id, "archive_img", "thumbnails[taxonomies][$key]");
    echo "</div>";
}
echo "</div>";



echo '<input type="submit" class="button button-primary" value="' . __("Save") . '">';
echo '</form>';


$txts = array(
    "title" => __('Select or Upload an Image', 'julioedi-advance-featured-image'),
    "text" => __('Use this image', 'julioedi-advance-featured-image')
);
echo "<script>window.__wp_adv_featured_image_msg = " . json_encode($txts) . " </script>";
