<?php

namespace julioEdi\AdvanceFeaturedImage;

class Tax
{
  public $taxonomy_table_col_id = "featured_image";
  public $featured_media_taxonomies = [
    "category" => true,
    "post_tag" => true,
    // Add any custom taxonomies to this array
  ];
  private static $terms = array();

  public static $column = "_thumbnail_id";

  public function __construct()
  {
    $this->edit();
    $this->save();
    add_action("delete_post", [$this, "on_delete_post"]);
    add_filter("get_term", [$this, "single_term"], 1);
    add_filter("get_terms", [$this, "all_terms"], 1);
  }

  public function single_term($term)
  {
    if (is_object($term) && isset($term->term_id)) {
      $thumbnail_id = (int) get_term_meta($term->term_id, self::$column, true);
      $term->{self::$column} = $thumbnail_id;
    }
    return $term;
  }


  public function all_terms($terms)
  {
    // Bail early if empty or not array
    if (empty($terms) || !is_array($terms)) {
      return $terms;
    }

    $column = self::$column;

    // Assign retrieved meta values to term objects
    foreach ($terms as &$term) {
      if (is_object($term)) {
        $meta = get_term_meta($term->term_id, $column, true);
        $term->$column = $meta && is_numeric($meta) ? (int) $meta : 0;
      }
    }

    return $terms;
  }


  public function on_delete_post($post_id)
  {
    global $wpdb;
    $meta_key = self::$column;

    // Delete all term meta entries where meta_key = '_thumbnail_id' and meta_value = $post_id
    $wpdb->query(
      $wpdb->prepare(
        "DELETE FROM {$wpdb->termmeta} WHERE meta_key = %s AND meta_value = %d",
        $meta_key,
        $post_id
      )
    );
  }


  /**
   * Registers hooks for editing and saving term featured images
   */
  public function edit(): void
  {
    $pre = $this->featured_media_taxonomies;

    global $wp_taxonomies;
    foreach ($wp_taxonomies as $key => $value) {
      $pre[$key] = $value->public && $value->show_ui;
    }
    $tax = (array) apply_filters("julioedi/adv_featured/taxonomies/featured/edit", $pre);

    foreach ($tax as $key => $value) {
      $value = (bool) apply_filters("julioedi/adv_featured/taxonomies/featured/edit/$key", $value);
      if (!$value) {
        continue;
      }
      add_action("{$key}_edit_form_fields", [$this, "taxonomies_include_cover_image"]);
      add_action("{$key}_add_form_fields", [$this, "taxonomies_include_cover_image"]);

      add_action("manage_edit-{$key}_columns", [$this, "taxonomies_table_head"]);
      add_action("manage_{$key}_custom_column", [$this, "taxonomies_table_column"], 10, 3);
    }
  }



  public static function enqueue_font_awesome()
  {
    wp_enqueue_style("font_awesome_all");
  }

  /**
   * Registers save actions for thumbnail ID when terms are created or edited
   */
  public function save(): void
  {
    add_action('create_term', [$this, 'save_thumbnail_id']);
    add_action('edited_term', [$this, 'save_thumbnail_id']);
  }

  /**
   * Adds a custom column for the featured image in the term management table
   */
  public function taxonomies_table_head($columns)
  {
    if (!isset($columns[$this->taxonomy_table_col_id])) {
      // Insertar la columna después del checkbox (cb)
      $new_columns = [];
      foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'cb') {
          $new_columns[$this->taxonomy_table_col_id] = "&nbsp;";
        }
      }
      return $new_columns;
    }
    return $columns;
  }


  /**
   * Populates the custom column with the term's featured image
   */
  public function taxonomies_table_column($content, $column_name, $term_id)
  {
    if ($column_name === $this->taxonomy_table_col_id) {
      $term = (array) get_term($term_id);
      // Check if the term has a thumbnail ID and display it
      $content = wp_get_attachment_image($term["_thumbnail_id"] ?? "0");
    }
    return $content !== "" ? $content :  '<div class="empty"><i class="fa-solid fa-trash"></i></div>';
  }


  public function get_sanitized_thumbnail_id(): int
  {
    $column = self::$column;
    return isset($_POST[$column]) && wp_attachment_is_image($_POST[$column]) ? absint($_POST[$column]) : 0;
  }

  /**
   * Saves the selected thumbnail ID when a term is created or edited
   */
  public function save_thumbnail_id(int $term_id): void
  {
    if (!current_user_can('manage_categories')) {
      return;
    }

    $col = self::$column;
    if (!isset($_POST["{$col}_nonce"]) || !wp_verify_nonce($_POST["{$col}_nonce"], "save_term{$col}")) {
      return;
    }

    if (!isset($_POST[$col])) {
      return;
    }

    $thumbnail_id = absint($_POST[$col]);

    if ($thumbnail_id > 0 && !wp_attachment_is_image($thumbnail_id)) {
      $thumbnail_id = 0;
    }

    update_term_meta($term_id, self::$column, $thumbnail_id);
  }




  /**
   * Displays the UI for selecting a featured image in the term edit form
   */
  public function taxonomies_include_cover_image($tag)
  {
    global $pagenow;
    $is_new = $pagenow == "edit-tags.php";



    if ($is_new) {
      // Close the form tag early to prevent open tags
      echo ">";
    }
    $column = self::$column;
    $list = (array) $tag;
    $thumbnail_id = $list[$column] ?? "0";
    $is_image = wp_get_attachment_url($thumbnail_id);


    wp_nonce_field("save_term{$column}", "{$column}_nonce");
    $deletebtn = '<div class="delete_cover"><div class="tax_icon_button"><i class="fa-solid fa-trash"></i></div></div>';

    if (!empty($is_image)) {
      // If there's an image, show it with a delete button
      $is_image = sprintf('<img src="%s" data-id="%s">' . $deletebtn, $is_image, $thumbnail_id);
    }

    julioedi_adv_featured_template_select_image($thumbnail_id, $is_new ? "new_tag" : "edit_tag");
    $txts = array(
      "title" => __('Select or Upload an Image', 'julioedi-advance-featured-image'),
      "text" => __('Use this image', 'julioedi-advance-featured-image')
    );
    echo "<script>window.__wp_adv_featured_image_msg = " . wp_json_encode($txts) . " </script>";

    if ($is_new) {
      // Close the form tag
      echo "<div></div";
    }
    return $tag;
  }

  /**
   * Retrieves a term by ID from cache, or fetches it from the database if not cached
   */
  public static function get_term(int $id): ?object
  {
    // Build the cache key
    $key = "term_$id";

    // If the term is cached, return it
    if (isset(self::$terms[$key])) {
      return self::$terms[$key];
    }

    // If not cached, fetch it and cache it
    self::$terms[$key] = get_term($id);

    return self::$terms[$key];
  }

  /**
   * Clears the cache for a specific term by ID
   */
  public static function clear_cache(int $id)
  {
    $key = "term_$id";
    unset(self::$terms[$key]);
  }

  /**
   * Retrieves the thumbnail ID for a term, given its ID
   */
  public static function get_term_thumbnail_id(int $id): int
  {
    $key = "term_$id";
    $term = self::get_term($id);
    if (!$term) {
      return 0; // Return 0 if no term is found
    }

    return $term->{self::$column} ?? 0; // Return the thumbnail ID
  }

  public static function get_term_thumbnail(int $id, $size = 'thumbnail', string|array $attr = ''): string
  {
    $term_thumbnail_id = self::get_term_thumbnail_id($id);
    if ($term_thumbnail_id === 0) {
      return "";
    }
    /**
     * Filters the post thumbnail size.
     *
     * @since 1.0.0
     *
     * @param string|int[] $size    Requested image size. Can be any registered image size name, or
     *                              an array of width and height values in pixels (in that order).
     * @param int          $id The term ID.
     */
    $size = apply_filters('julioedi/adv_featured/term/size', $size, $id);
    $html = wp_get_attachment_image($term_thumbnail_id, $size, false, $attr);
    return apply_filters('julioedi/adv_featured/term/html', $html, $id, $term_thumbnail_id, $size, $attr);
  }
}
