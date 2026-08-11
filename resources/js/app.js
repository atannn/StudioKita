import './bootstrap';

import Alpine from 'alpinejs';
import { Chart, registerables } from 'chart.js';

window.Alpine = Alpine;

Chart.register(...registerables);

Alpine.start();

const initOwnerCharts = (scope = document) => {
    const charts = scope.querySelectorAll('canvas.sk-bar-chart');
    if (!charts.length) {
        return;
    }

    charts.forEach((canvas) => {
        if (canvas.dataset.chartInit === '1') {
            return;
        }

        const labels = JSON.parse(canvas.dataset.labels || '[]');
        const values = JSON.parse(canvas.dataset.values || '[]');
        const color = canvas.dataset.color || '#6366f1';

        if (!labels.length) {
            canvas.dataset.chartInit = '1';
            return;
        }

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        data: values,
                        backgroundColor: color,
                        borderRadius: 8,
                        barThickness: 26,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: {
                                size: 11,
                            },
                        },
                    },
                    y: {
                        grid: {
                            color: 'rgba(148, 163, 184, 0.15)',
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: {
                                size: 11,
                            },
                        },
                    },
                },
            },
        });

        canvas.dataset.chartInit = '1';
    });
};

window.initOwnerCharts = initOwnerCharts;

document.addEventListener('DOMContentLoaded', () => {
    initOwnerCharts();
});
