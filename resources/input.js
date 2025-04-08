(function ($) {
  const Field = acf.models.OembedField.extend({
    type: "vimeo_video",

    $control: function () {
      return this.$(".acf-vimeo-video");
    },

    search: function (url) {
      // ajax
      var ajaxData = {
        action: "acf/fields/vimeo-video/search",
        s: url,
        field_key: this.get("key"),
      };

      // clear existing timeout
      var xhr = this.get("xhr");
      if (xhr) {
        xhr.abort();
      }

      // loading
      this.showLoading();

      // query
      var xhr = $.ajax({
        url: acf.get("ajaxurl"),
        data: acf.prepareForAjax(ajaxData),
        type: "post",
        dataType: "json",
        context: this,
        success: function (json) {
          // error
          if (!json || !json.html || json.error) {
            json.url = false;
            json.html = "";
            this.val(false);
          } else {
            // update vars
            this.val(
              JSON.stringify({
                ID: json.ID,
                url: json.url,
                width: json.width,
                height: json.height,
                files: json.files,
                thumbnail: json.thumbnail,
                texttracks: json.texttracks,
              }),
            );
          }

          this.$(".canvas-media").html(json.html);
        },
        complete: function () {
          this.hideLoading();
        },
      });

      this.set("xhr", xhr);
    },
  });

  acf.registerFieldType(Field);
})(jQuery);
