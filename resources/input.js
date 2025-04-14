// @ts-check

/**
 *
 * @typedef {import('jquery')} jQuery
 *
 * @typedef {Object} ErrorData
 * @property {string} message - Error message
 *
 * @typedef {Object} SuccessData
 * @property {Object} value - The actual value object
 * @property {string} html - HTML content
 *
 * @typedef {Object} ErrorResponse
 * @property {false} success - Indicates failure
 * @property {ErrorData} data - Contains error message
 *
 * @typedef {Object} SuccessResponse
 * @property {true} success - Indicates success
 * @property {SuccessData} data - Contains value and HTML
 *
 * @typedef {ErrorResponse | SuccessResponse} AjaxResponse
 *
 * @global {any} acf
 */

(function ($) {
  /**
   * window.acf is a black box :(
   * @type {any}
   */
  const acf = /** @type {any} */ (window).acf;

  const ACFVimeoField = acf.models.OembedField.extend({
    type: "vimeo_video",

    previousSearchValue: "",
    searchTimeout: 0,

    events: {
      'click [data-name="clear-button"]': "onClickClear",
      "input .input-search": "onChangeSearch",
      "keydown .input-search": "onKeyDownSearch",
    },

    $control: function () {
      return this.$(".acf-vimeo-video");
    },

    /**
     * Do a forced re-search when pressing Enter in the search input
     * @param {KeyboardEvent} e
     */
    onKeyDownSearch(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        e.stopPropagation();
        this.search(this.$search().val())
      }
    },

    // Initiate a search if needed
    maybeSearch() {
      const { previousSearchValue } = this;
      const searchValue = this.$search().val();

      if (searchValue === previousSearchValue) {
        return;
      }

      this.previousSearchValue = searchValue;

      if (!searchValue) {
        return this.clear();
      }

      clearTimeout(this.searchTimeout);
      setTimeout(() => this.search(searchValue), 300);
    },

    /**
     * @param {string} url
     */
    search: function (url) {
      // ajax
      const ajaxData = {
        action: "acf/fields/vimeo-video/search",
        url,
        field_key: this.get("key"),
      };

      // clear existing XHR request
      const ecxistingXHR = this.get("xhr");
      if (ecxistingXHR) {
        ecxistingXHR.abort();
      }

      // loading
      this.showLoading();

      // query
      const xhr = $.ajax({
        url: acf.get("ajaxurl"),
        data: acf.prepareForAjax(ajaxData),
        type: "post",
        dataType: "json",
        context: this,
        /**
         * @param {AjaxResponse} response
         */
        success: function (response) {
          const { success, data } = response;

          const html = success ? data.html : wrapErrorMessage(data.message);
          const value = success ? JSON.stringify(data.value) : "";

          this.val(value);
          this.$(".canvas-media").html(html);
        },
        complete: function () {
          this.hideLoading();
        },
      });

      this.set("xhr", xhr);
    },
  });

  acf.registerFieldType(ACFVimeoField);

  /**
   * @param {string} message
   * @return {string} wrapped error message
   */
  function wrapErrorMessage(message) {
    return `<div class="acf-notice -error acf-error-message" style="margin-top: 10px">
      <p><span class="dashicons dashicons-warning"></span> ${message}</p>
    </div>`;
  }
})(jQuery);
