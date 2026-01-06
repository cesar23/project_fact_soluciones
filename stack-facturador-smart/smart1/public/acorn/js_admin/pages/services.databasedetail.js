/**
 *
 * ServicesDatabaseDetail
 *
 * ServicesDatabaseDetail page content scripts. Initialized from scripts.js file.
 *
 *
 */

class ServicesDatabaseDetail {
  constructor() {
    // Initialization of the page plugins
    if (typeof ResponsiveTab !== 'undefined') {
      this._initTitleTabs();
    } else {
      console.error('[CS] ResponsiveTab is undefined.');
    }

    // Streaming charts
    this._streamingDbChartFirst = null;
    this._streamingDbChartSecond = null;
    this._streamingDbChartThird = null;

    if (typeof Chart !== 'undefined' && typeof ChartsExtend !== 'undefined') {
      this._initStreamingDbChartFirst();
      this._initStreamingDbChartSecond();
      this._initStreamingDbChartThird();
    } else {
      console.error('[CS] Chart or ChartsExtend is undefined.');
    }

    this._initEvents();
  }

  // Responsive Tabs initialization
  _initTitleTabs() {
    document.querySelector('.responsive-tabs') !== null && new ResponsiveTab(document.querySelector('.responsive-tabs'));
  }

  _onRefresh(chart) {
    if (typeof moment !== 'undefined') {
      chart.config.data.datasets.forEach(function (dataset) {
        dataset.data.push({
          x: moment(),
          y: Math.round(Math.random() * 50) + 25,
        });
      });
    } else {
      console.error('[CS] moment is undefined.');
    }
  }

  // Streaming charts initialization
  _initStreamingDbChartFirst() {
    if (document.getElementById('streamingDbChartFirst')) {
      const ctx = document.getElementById('streamingDbChartFirst').getContext('2d');
      this._streamingDbChartFirst = new Chart(ctx, {
        type: 'line',
        options: {
          plugins: {
            crosshair: ChartsExtend.Crosshair(),
            datalabels: {display: false},
            streaming: {
              frameRate: 30,
            },
          },
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            yAxes: [
              {
                gridLines: {display: true, lineWidth: 1, color: Globals.separator, drawBorder: false},
                ticks: {beginAtZero: true, padding: 20, fontColor: Globals.alternate, min: 0, max: 100, stepSize: 25},
              },
            ],
            xAxes: [
              {
                gridLines: {display: false},
                ticks: {display: false},
                type: 'realtime',
                realtime: {
                  duration: 20000,
                  refresh: 1000,
                  delay: 3000,
                  onRefresh: this._onRefresh,
                },
              },
            ],
          },
          legend: {display: false},
          tooltips: ChartsExtend.ChartTooltipForCrosshair(),
        },
        data: {
          labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          datasets: [
            {
              label: '',
              borderColor: Globals.primary,
              pointBackgroundColor: Globals.primary,
              pointBorderColor: Globals.primary,
              pointHoverBackgroundColor: Globals.primary,
              pointHoverBorderColor: Globals.primary,
              borderWidth: 2,
              pointRadius: 2,
              pointBorderWidth: 2,
              pointHoverRadius: 3,
              fill: false,
            },
          ],
        },
      });
    }
  }

  _initStreamingDbChartSecond() {
    if (document.getElementById('streamingDbChartSecond')) {
      const ctx = document.getElementById('streamingDbChartSecond').getContext('2d');
      this._streamingDbChartSecond = new Chart(ctx, {
        type: 'line',
        options: {
          plugins: {
            crosshair: ChartsExtend.Crosshair(),
            datalabels: {display: false},
            streaming: {
              frameRate: 30,
            },
          },
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            yAxes: [
              {
                gridLines: {display: true, lineWidth: 1, color: Globals.separator, drawBorder: false},
                ticks: {beginAtZero: true, padding: 20, fontColor: Globals.alternate, min: 0, max: 100, stepSize: 25},
              },
            ],
            xAxes: [
              {
                gridLines: {display: false},
                ticks: {display: false},
                type: 'realtime',
                realtime: {
                  duration: 20000,
                  refresh: 1000,
                  delay: 3000,
                  onRefresh: this._onRefresh,
                },
              },
            ],
          },
          legend: {display: false},
          tooltips: ChartsExtend.ChartTooltipForCrosshair(),
        },
        data: {
          labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          datasets: [
            {
              label: '',
              borderColor: Globals.secondary,
              pointBackgroundColor: Globals.secondary,
              pointBorderColor: Globals.secondary,
              pointHoverBackgroundColor: Globals.secondary,
              pointHoverBorderColor: Globals.secondary,
              borderWidth: 2,
              pointRadius: 2,
              pointBorderWidth: 2,
              pointHoverRadius: 3,
              fill: false,
            },
          ],
        },
      });
    }
  }

  _initStreamingDbChartThird() {
    if (document.getElementById('streamingDbChartThird')) {
      const ctx = document.getElementById('streamingDbChartThird').getContext('2d');
      this._streamingDbChartThird = new Chart(ctx, {
        type: 'line',
        options: {
          plugins: {
            crosshair: ChartsExtend.Crosshair(),
            datalabels: {display: false},
            streaming: {
              frameRate: 30,
            },
          },
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            yAxes: [
              {
                gridLines: {display: true, lineWidth: 1, color: Globals.separator, drawBorder: false},
                ticks: {beginAtZero: true, padding: 20, fontColor: Globals.alternate, min: 0, max: 100, stepSize: 25},
              },
            ],
            xAxes: [
              {
                gridLines: {display: false},
                ticks: {display: false},
                type: 'realtime',
                realtime: {
                  duration: 20000,
                  refresh: 1000,
                  delay: 3000,
                  onRefresh: this._onRefresh,
                },
              },
            ],
          },
          legend: {display: false},
          tooltips: ChartsExtend.ChartTooltipForCrosshair(),
        },
        data: {
          labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          datasets: [
            {
              label: '',
              borderColor: Globals.tertiary,
              pointBackgroundColor: Globals.tertiary,
              pointBorderColor: Globals.tertiary,
              pointHoverBackgroundColor: Globals.tertiary,
              pointHoverBorderColor: Globals.tertiary,
              borderWidth: 2,
              pointRadius: 2,
              pointBorderWidth: 2,
              pointHoverRadius: 3,
              fill: false,
            },
          ],
        },
      });
    }
  }

  // Listening for color change events to update charts
  _initEvents() {
    document.documentElement.addEventListener(Globals.colorAttributeChange, (event) => {
      this._streamingDbChartFirst && this._streamingDbChartFirst.destroy();
      this._initStreamingDbChartFirst();

      this._streamingDbChartSecond && this._streamingDbChartSecond.destroy();
      this._initStreamingDbChartSecond();

      this._streamingDbChartThird && this._streamingDbChartThird.destroy();
      this._initStreamingDbChartThird();
    });
  }
}
