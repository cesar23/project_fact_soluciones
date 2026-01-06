/**
 *
 * ServicesDatabase
 *
 * Services.Database page content scripts. Initialized from scripts.js file.
 *
 *
 */

class ServicesDatabase {
  constructor() {
    // References to page items that might require an update
    this._smallDoughnutChart1 = null;
    this._smallDoughnutChart2 = null;
    this._smallDoughnutChart3 = null;
    this._smallDoughnutChart4 = null;

    // Initialization of the page plugins
    if (typeof Chart !== 'undefined' && typeof ChartsExtend !== 'undefined') {
      this._initSmallDoughnutCharts();
    } else {
      console.error('[CS] ChartsExtend is undefined.');
    }

    if (typeof Checkall !== 'undefined') {
      this._initCheckAll();
    } else {
      console.error('[CS] Checkall is undefined.');
    }

    this._initEvents();
  }
  // Check all button initialization
  _initCheckAll() {
    new Checkall(document.querySelector('.check-all-container .btn-custom-control'));
  }

  // Progress doughnut charts
  _initSmallDoughnutCharts() {
    if (document.getElementById('smallDoughnutChart1')) {
      this._smallDoughnutChart1 = ChartsExtend.SmallDoughnutChart('smallDoughnutChart1', [14, 0], 'PURCHASING');
    }
    if (document.getElementById('smallDoughnutChart2')) {
      this._smallDoughnutChart2 = ChartsExtend.SmallDoughnutChart('smallDoughnutChart2', [12, 6], 'PRODUCTION');
    }
    if (document.getElementById('smallDoughnutChart3')) {
      this._smallDoughnutChart3 = ChartsExtend.SmallDoughnutChart('smallDoughnutChart3', [22, 8], 'PACKAGING');
    }
    if (document.getElementById('smallDoughnutChart4')) {
      this._smallDoughnutChart4 = ChartsExtend.SmallDoughnutChart('smallDoughnutChart4', [1, 5], 'DELIVERY');
    }
  }

  // Listening for color change events to update charts
  _initEvents() {
    document.documentElement.addEventListener(Globals.colorAttributeChange, (event) => {
      this._smallDoughnutChart1 && this._smallDoughnutChart1.destroy();
      this._smallDoughnutChart2 && this._smallDoughnutChart2.destroy();
      this._smallDoughnutChart3 && this._smallDoughnutChart3.destroy();
      this._smallDoughnutChart4 && this._smallDoughnutChart4.destroy();
      this._initSmallDoughnutCharts();
    });
  }
}
