/**
 *
 * AccountSettings
 *
 * Account.Settings page content scripts. Initialized from scripts.js file.
 *
 *
 */

class AccountSettings {
  constructor() {
    // Initialization of the page plugins
    this._singleImageUploadExample = null;
    if (typeof SingleImageUpload !== 'undefined') {
      this._initSingleImageUpload();
    } else {
      console.error('[CS] SingleImageUpload is undefined.');
    }

    if (jQuery().select2) {
      this._initSelect2();
    } else {
      console.error('[CS] select2 is undefined.');
    }
  }
  // Check all button initialization
  _initSelect2() {
    jQuery('.select-single-no-search-filled').select2({minimumResultsForSearch: Infinity});
  }

  // Single Image Upload initialization
  _initSingleImageUpload() {
    this._singleImageUploadExample = document.getElementById('singleImageUploadExample');
    if (this._singleImageUploadExample) {
      const singleImageUpload = new SingleImageUpload(this._singleImageUploadExample, {
        fileSelectCallback: (image) => {
          console.log(image);
          // how to :
          // Upload the file with fetch method
          // let formData = new FormData();
          // formData.append("file", image.file);
          // fetch('/upload/image', { method: "POST", body: formData });
        },
      });
    }
  }
}
