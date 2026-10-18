/* Initialises Reporting charts, print controls and report-row visibility. */

(function () {
    'use strict';

    function initialiseCharts() {
        if (typeof window.c3 === 'undefined') {
            return;
        }

        document.querySelectorAll('[data-reporting-chart]').forEach(function (chartElement) {
            let columns;
            const page = chartElement.closest('.reporting-page');
            const styles = window.getComputedStyle(page || chartElement);
            const budgetColor = styles.getPropertyValue('--reporting-chart-budget').trim() || '#2F72C4';
            const usedColor = styles.getPropertyValue('--reporting-chart-used').trim() || '#2A7D4F';

            try {
                columns = JSON.parse(chartElement.getAttribute('data-columns'));
            } catch {
                return;
            }

            window.c3.generate({
                bindto: chartElement,
                data: {
                    columns: columns,
                    type: 'bar',
                    x: 'x'
                },
                axis: {
                    x: {
                        type: 'category'
                    }
                },
                bar: {
                    width: {
                        ratio: 0.65
                    }
                },
                color: {
                    pattern: [budgetColor, usedColor]
                },
                grid: {
                    y: {
                        show: true
                    }
                },
                size: {
                    height: 280
                }
            });
        });
    }

    function initialiseReportControls() {
        document.querySelectorAll('[data-reporting-hide]').forEach(function (button) {
            button.addEventListener('click', function () {
                const projectId = button.getAttribute('data-reporting-hide');
                const row = document.querySelector('[data-reporting-row="' + projectId + '"]');

                if (row) {
                    row.classList.add('reporting-row-hidden');
                }
            });
        });

        document.querySelectorAll('[data-reporting-print]').forEach(function (button) {
            button.addEventListener('click', function () {
                window.print();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initialiseCharts();
        initialiseReportControls();
    });
}());
