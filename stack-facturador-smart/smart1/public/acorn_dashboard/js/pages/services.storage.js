/**
 *
 * ServicesStorage
 *
 * Services.Storage page content scripts. Initialized from scripts.js file.
 *
 *
 */

class ServicesStorage {
  constructor() {
    // Initialization of the page plugins
    if (typeof Checkall !== 'undefined') {
      this._initCheckAll();
    } else {
      console.error('[CS] Checkall is undefined.');
    }
    if (typeof Dropzone !== 'undefined' && typeof DropzoneTemplates !== 'undefined') {
      this._initDropzone();
    } else {
      console.error('[CS] Dropzone or DropzoneTemplates is undefined.');
    }
    // Initialization of the page plugins
    if (typeof baguetteBox !== 'undefined') {
      this._initLightbox();
    } else {
      console.error('[CS] baguetteBox is undefined.');
    }
  }
  // Check all button initialization
  _initCheckAll() {
    new Checkall(document.querySelector('.check-all-container .btn-custom-control'));
  }

  // Dropzone initialization
  _initDropzone() {
    if (document.querySelector('.dropzone-filled')) {
      new Dropzone('.dropzone-filled', {
        url: 'https://httpbin.org/post',
        init: function () {
          this.on('success', function (file, responseText) {
            console.log(responseText);
          });
        },
        thumbnailWidth: 160,
        previewTemplate: DropzoneTemplates.previewTemplate,
      });
    }
  }

  // Lightbox initialize
  _initLightbox() {
    baguetteBox.run('.lightbox');
  }
}
