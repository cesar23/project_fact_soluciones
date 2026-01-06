/**
 *
 * Analysis
 *
 * Analysis page content scripts. Initialized from scripts.js file.
 *
 *
 */

class Analysis {
  constructor() {
    // References to page items that might require an update
    this._streamingLineChart = null;
    this._streamingBarChart = null;
    this._progressBars = [];

    // Initialization of the page plugins
    if (typeof Chart !== 'undefined' && typeof ChartsExtend !== 'undefined') {
      this._initStreamingLineChart();
      this._initStreamingBarChart();
    } else {
      console.error('[CS] Chart or ChartsExtend is undefined.');
    }

    if (typeof ProgressBar !== 'undefined') {
      this._initProgressCircle();
    } else {
      console.error('[CS] ProgressBar is undefined.');
    }

    this._initEvents();
  }
  _onRefresh(chart) {
    chart.config.data.datasets.forEach(function (dataset) {
      dataset.data.push({
        x: moment(),
        y: Math.round(Math.random() * 50) + 25,
      });
    });
  }

  // Streaming Line Chart initialization
  _initStreamingLineChart() {
    if (document.getElementById('streamingLineChart')) {
      const ctx = document.getElementById('streamingLineChart').getContext('2d');
      this._streamingLineChart = new Chart(ctx, {
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

  // Streaming Bar Chart initialization
  _initStreamingBarChart() {
    if (document.getElementById('streamingBarChart')) {
      const ctx = document.getElementById('streamingBarChart').getContext('2d');
      this._streamingBarChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: [],
          datasets: [
            {
              label: 'Breads',
              data: [],
              borderColor: Globals.primary,
              backgroundColor: 'rgba(' + Globals.primaryrgb + ',0.1)',
              borderWidth: 2,
            },
          ],
        },
        options: {
          cornerRadius: parseInt(Globals.borderRadiusMd),
          plugins: {
            crosshair: ChartsExtend.Crosshair(),
            datalabels: {display: false},
            streaming: {
              frameRate: 30,
            },
          },
          responsive: true,
          maintainAspectRatio: false,
          title: {
            display: false,
          },
          scales: {
            xAxes: [
              {
                ticks: {display: false},
                type: 'realtime',
                realtime: {
                  duration: 20000,
                  refresh: 1000,
                  delay: 3000,
                  onRefresh: this._onRefresh,
                },
                gridLines: {display: false},
              },
            ],
            yAxes: [
              {
                gridLines: {
                  display: true,
                  lineWidth: 1,
                  color: Globals.separator,
                  drawBorder: false,
                },
                ticks: {
                  beginAtZero: true,
                  stepSize: 25,
                  min: 0,
                  max: 100,
                  padding: 20,
                },
              },
            ],
          },
          tooltips: ChartsExtend.ChartTooltip(),
          legend: {
            display: false,
          },
        },
      });
    }
  }

  // Circle ProgressBar implementation
  _progressCircleDestroy() {
    for (let i = 0; i < this._progressBars.length; i++) {
      this._progressBars[i].destroy();
    }
    this._progressBars = [];
  }

  _progressCircleUpdate() {
    this._progressCircleDestroy();
    this._initProgressCircle();
  }

  _initProgressCircle() {
    document.querySelectorAll('.progress-bar-circle').forEach((el, i) => {
      const val = el.getAttribute('aria-valuenow');
      const color = Globals[el.getAttribute('data-color')] || Globals.primary;
      const trailColor = Globals[el.getAttribute('data-trail-color')] || Globals.separator;
      const max = el.getAttribute('aria-valuemax') || 100;
      const showPercent = el.getAttribute('data-show-percent');
      const hideAll = el.getAttribute('data-hide-all-text');
      const strokeWidth = el.getAttribute('data-stroke-width') || 1;
      const trailWidth = el.getAttribute('data-trail-width') || 1;
      const duration = parseInt(el.getAttribute('data-duration')) || 20;
      const easing = el.getAttribute('data-easing') || 'easeInOut';
      this._progressBars.push(
        new ProgressBar.Circle(el, {
          color: color,
          duration: duration,
          easing: easing,
          strokeWidth: strokeWidth,
          trailColor: trailColor,
          trailWidth: trailWidth,
          val: val,
          max: max,
          text: {
            autoStyleContainer: false,
          },
          step: function (state, bar) {
            if (hideAll === 'false') {
              if (showPercent === 'true') {
                bar.setText(Math.round(bar.value() * 100) + '%');
              } else {
                bar.setText(val + '/' + max);
              }
            }
          },
        }),
      );
    });

    // Animate
    for (let i = 0; i < this._progressBars.length; i++) {
      this._progressBars[i].animate(this._progressBars[i]._opts.val / this._progressBars[i]._opts.max);
    }
  }

  // Listening for color change events to update charts
  _initEvents() {
    document.documentElement.addEventListener(Globals.colorAttributeChange, (event) => {
      this._streamingLineChart && this._streamingLineChart.destroy();
      this._initStreamingLineChart();

      this._streamingBarChart && this._streamingBarChart.destroy();
      this._initStreamingBarChart();

      this._progressCircleUpdate();
    });
  }
}
