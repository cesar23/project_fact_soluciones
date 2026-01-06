/**
 *
 * SupportDocs
 *
 * Support.Docs page content scripts. Initialized from scripts.js file.
 *
 *
 */

class SupportDocs {
  constructor() {
    // Initialization of the page plugins
    if (typeof Plyr !== 'undefined') {
      this._initPlayer();
    } else {
      console.error('[CS] Plyr is undefined.');
    }
    if (typeof Masonry !== 'undefined') {
      this._initMasonry();
    } else {
      console.error('[CS] Masonry is undefined.');
    }
  }

  // Plyr - Video and Audio Player Start
  _initPlayer() {
    document.querySelectorAll('.player').forEach((el) => {
      new Plyr(el);
    });
  }

  // Api reference masonry
  _initMasonry() {
    if (document.getElementById('apiReferenceMasonry')) {
      var msnry = new Masonry('#apiReferenceMasonry', {
        itemSelector: '.col',
        percentPosition: true,
        transitionDuration: 0,
      });
    }
  }
}
