import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const brand = '#0e385f';
const brandLight = 'rgba(14, 56, 95, 0.12)';
const slate = '#64748b';
const grid = 'rgba(148, 163, 184, 0.25)';

Chart.defaults.font.family =
    "'Instrument Sans', ui-sans-serif, system-ui, sans-serif";
Chart.defaults.color = slate;
Chart.defaults.scale.grid.color = grid;

function readPayload() {
    const el = document.getElementById('dashboard-chart-payload');
    if (!el?.textContent) {
        return null;
    }
    try {
        return JSON.parse(el.textContent);
    } catch {
        return null;
    }
}

function doughnut(canvasId, labels, values, colors) {
    const canvas = document.getElementById(canvasId);
    if (!canvas || !labels?.length) {
        return;
    }
    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [
                {
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 0,
                    hoverOffset: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, padding: 16, usePointStyle: true },
                },
            },
        },
    });
}

function barHorizontal(canvasId, labels, values) {
    const canvas = document.getElementById(canvasId);
    if (!canvas || !labels?.length) {
        return;
    }
    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Postulantes',
                    data: values,
                    backgroundColor: brandLight,
                    borderColor: brand,
                    borderWidth: 1,
                    borderRadius: 4,
                    maxBarThickness: 22,
                },
            ],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { drawTicks: false },
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } },
                },
            },
        },
    });
}

function lineChart(canvasId, labels, values) {
    const canvas = document.getElementById(canvasId);
    if (!canvas || !labels?.length) {
        return;
    }
    new Chart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Inscripciones',
                    data: values,
                    borderColor: brand,
                    backgroundColor: 'rgba(14, 56, 95, 0.08)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: brand,
                    pointBorderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: grid },
                },
                x: { grid: { display: false } },
            },
        },
    });
}

function stackedCampus(canvasId, labels, enrolled, available) {
    const canvas = document.getElementById(canvasId);
    if (!canvas || !labels?.length) {
        return;
    }
    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Matriculados',
                    data: enrolled,
                    backgroundColor: brand,
                    borderRadius: { topLeft: 0, topRight: 0, bottomLeft: 6, bottomRight: 6 },
                    stack: 'a',
                },
                {
                    label: 'Cupos libres',
                    data: available,
                    backgroundColor: 'rgba(14, 56, 95, 0.22)',
                    borderRadius: { topLeft: 6, topRight: 6, bottomLeft: 0, bottomRight: 0 },
                    stack: 'a',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, padding: 14, usePointStyle: true },
                },
            },
            scales: {
                x: { stacked: true, grid: { display: false } },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: grid },
                },
            },
        },
    });
}

function init() {
    const data = readPayload();
    if (!data) {
        return;
    }

    if (data.student_status) {
        doughnut(
            'chart-student-status',
            data.student_status.labels,
            data.student_status.values,
            ['#ca8a04', brand, '#b91c1c'],
        );
    }

    if (data.careers) {
        barHorizontal('chart-careers', data.careers.labels, data.careers.values);
    }

    if (data.registrations) {
        lineChart(
            'chart-registrations',
            data.registrations.labels,
            data.registrations.values,
        );
    }

    if (data.occupancy) {
        doughnut(
            'chart-occupancy',
            data.occupancy.labels,
            data.occupancy.values,
            [brand, 'rgba(14, 56, 95, 0.2)'],
        );
    }

    if (data.campus_load) {
        stackedCampus(
            'chart-campus-load',
            data.campus_load.labels,
            data.campus_load.enrolled,
            data.campus_load.available,
        );
    }
}

document.addEventListener('DOMContentLoaded', init);
