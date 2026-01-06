/**
 *
 * ServicesDatabaseAdd
 *
 * Services.DatabaseAdd page content scripts. Initialized from scripts.js file.
 *
 *
 */

class ServicesDatabaseAdd {
  constructor() {
    // Initialization of the page plugins
    if (jQuery().select2) {
      this._initSelect2();
    } else {
      console.error('[CS] select2 is undefined.');
    }
  }
  // Select2 button initialization
  _initSelect2() {
    jQuery('.select-single-no-search-filled').select2({minimumResultsForSearch: Infinity});
  }
}
