<?php

namespace julioEdi\AdvanceFeaturedImage;

class Tax
{
  public $taxonomy_table_col_id = "cover_image_column";
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
    add_action("admin_init", function () {
      self::taxonomies_featured_image();
    });
    // add_action("admin_head", [$this, "enqueue_admin"]);
    add_action("delete_post", [$this, "on_delete_post"]);
  }


  public function on_delete_post($id)
  {
    global $wpdb;
    $table_name = $wpdb->terms;
    $column = self::$column;
    $wpdb->query(
      $wpdb->prepare("UPDATE `$table_name` SET `$column` = '' WHERE `$column` = %d", $id)
    );
  }

  public function on_activate()
  {
    // register_activation_hook(julioedi_advance_featured_image_path, );
  }



  /**
   * Registers hooks for editing and saving term featured images
   */
  public function edit(): void
  {
    $pre = $this->featured_media_taxonomies;

    add_action('quick_edit_custom_box', [$this, "add_quick_edit_field"], 10);
    global $wp_taxonomies;
    foreach ($wp_taxonomies as $key => $value) {
      $pre[$key] = $value->public && $value->show_ui;
    }
    $tax = (array) apply_filters("julioedi_advance_featured_image/taxonomies/featured/edit", $pre);

    foreach ($tax as $key => $value) {
      $value = (bool) apply_filters("julioedi_advance_featured_image/taxonomies/featured/edit/$key", $value);
      if (!$value) {
        continue;
      }
      add_action("{$key}_edit_form_fields", [$this, "taxonomies_include_cover_image"]);
      add_action("{$key}_term_new_form_tag", [$this, "taxonomies_include_cover_image"]);

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
   * Ensures the database is ready by adding the _thumbnail_id column if it doesn't exist
   */
  public static function taxonomies_featured_image(bool $default = false)
  {
    $activated = false; //get_option("julioedi_advance_featured_image_inited",$default);
    if ($activated) {
      return;
    }
    global $wpdb;

    $table_name = $wpdb->terms;
    $column = self::$column;

    // Check if the column already exists
    $column_exists = $wpdb->get_results(
      $wpdb->prepare("SHOW COLUMNS FROM `$table_name` LIKE %s", $column)
    );

    // If the column doesn't exist, create it
    if (empty($column_exists)) {
      $wpdb->query(
        "ALTER TABLE `$table_name` ADD {$column} BIGINT UNSIGNED DEFAULT 0"
      );
      update_option("julioedi_advance_featured_image_inited", true);
    }
  }




  /**
   * Adds a custom column for the featured image in the term management table
   */
  public function taxonomies_table_head($columns)
  {
    foreach ($columns as $key => $value) {
      $new[$key] = $value;
      if ($key == "cb" && !isset($columns[$this->taxonomy_table_col_id])) {
        $new[$this->taxonomy_table_col_id] = "&nbsp;";
      }
    }
    return $new;
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

  function add_quick_edit_field($taxonomy)
  {
    global $pagenow;

    // Verificar si estamos en la página de edición de categorías
    if ($pagenow === 'edit-tags.php') {
?>
      <div class="inline-edit-col">
        <label>
          <span class="title">Campo Personalizado</span>
          <input type="text" name="mi_campo_personalizado" value="">
        </label>
      </div>
    <?php
    }
    return $taxonomy;
  }

  /**
   * Saves the selected thumbnail ID when a term is created or edited
   */
  public function save_thumbnail_id($term_id)
  {
    // Sanitize and validate the thumbnail ID
    $_thumbnail_id = sanitize_text_field($_POST['_thumbnail_id'] ?? "0");
    if (!is_numeric($_thumbnail_id)) {
      return;
    }

    // Check if the ID corresponds to a valid image
    $is_image = wp_get_attachment_metadata($_thumbnail_id);
    $_thumbnail_id = (int) $_thumbnail_id;
    if (!$is_image) {
      $_thumbnail_id = "0"; // Reset if not a valid image
    }

    global $wpdb;
    // Update the term taxonomy table with the valid thumbnail ID
    $wpdb->update(
      $wpdb->terms,
      array(
        '_thumbnail_id' => $_thumbnail_id,
      ),
      array('term_id' => $term_id),
      array("%d"),
      array("%d"),
    );
    return $term_id;
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

    $list = (array) $tag;
    $thumbnail_id = $list["_thumbnail_id"] ?? "0";
    $is_image = wp_get_attachment_url($thumbnail_id);
    $deletebtn = '<div class="delete_cover"><div class="tax_icon_button"><i class="fa-solid fa-trash"></i></div></div>';

    if (!empty($is_image)) {
      // If there's an image, show it with a delete button
      $is_image = sprintf('<img src="%s" data-id="%s">' . $deletebtn, $is_image, $thumbnail_id);
    }
    
    julioedi_adv_featured_template_select_image($thumbnail_id,$is_new ? "new_tag" : "edit_tag");
    $txts = array(
      "title" => __('Select or Upload an Image','julioedi_advance_featured_image_path'),
      "text" => __('Use this image','julioedi_advance_featured_image_path')
    );
    echo "<script>window.__wp_adv_featured_image_msg = " . json_encode($txts) ." </script>";

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
    if (!isset($term->{self::$column})) {
      # code...
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
    $size = apply_filters('term_thumbnail_size', $size, $id);
    $html = wp_get_attachment_image($term_thumbnail_id, $size, false, $attr);
    return apply_filters('term_thumbnail_html', $html, $id, $term_thumbnail_id, $size, $attr);
  }
}
