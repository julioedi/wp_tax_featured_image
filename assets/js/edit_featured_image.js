jQuery(document).ready(function ($) {
  const uploaders = [];
  
  const deletebtn = '<div class="delete_cover"><div class="tax_icon_button"><i class="fa-solid fa-trash"></i></div></div>';
  function setUploader(e) {
    const $parent = this;
    const $el = $(this);
    if (e.target.closest(".delete_cover")) {
      return;
    }
    if ($parent?.__mediaUploader && typeof $parent.__mediaUploader?.open == "function") {
      $parent.__mediaUploader.open();
      return;
    };
    $parent.__mediaUploader = wp.media.frames.file_frame = wp.media({
      title: window.__wp_adv_featured_image_msg.title,
      button: {
        text: 'Use this image'
      },
      multiple: false
    });
    const mediaUploader = $parent.__mediaUploader;

    const $preview = $el.find(".adv_custom_preview_cover");
    const $inputVal = $el.find('input');
    mediaUploader.on('select', function () {
      // Get the selected image data
      var attachment = mediaUploader.state().get('selection').first().toJSON();
      $preview.html(`<img src="${attachment.url}" data-id="${attachment.id}">${deletebtn}`);

      $inputVal.attr("value", attachment.id);
      $inputVal.trigger("change")
    });
     mediaUploader.open();
  }

  function cleanPreview(e){
    const $el = $(this);
    const $preview = $el.find(".adv_custom_preview_cover");
    const $inputVal = $el.find('input');
    $preview.html("");
    $inputVal.attr("value","0");
  }

  $(document).on("click", '.adv_custom_cover_image', '.adv_custom_cover_no_image .tax_btn,.adv_custom_preview_cover img', setUploader);
  
  $(document).on("click",'.adv_custom_cover_image',".adv_custom_preview_cover .delete_cover",cleanPreview);
});
