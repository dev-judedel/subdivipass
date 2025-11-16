<template>
    <canvas ref="chartCanvas"></canvas>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue';
import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';

// Register Chart.js components
Chart.register(
    LineController,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Title,
    Tooltip,
    Legend,
    Filler
);

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        default: () => ({}),
    },
    height: {
        type: Number,
        default: 300,
    },
});

const chartCanvas = ref(null);
let chartInstance = null;

const defaultOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'bottom',
        },
        tooltip: {
            mode: 'index',
            intersect: false,
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                precision: 0,
            },
        },
    },
};

const createChart = () => {
    if (chartInstance) {
        chartInstance.destroy();
    }

    if (chartCanvas.value) {
        const ctx = chartCanvas.value.getContext('2d');
        chartInstance = new Chart(ctx, {
            type: 'line',
            data: props.data,
            options: { ...defaultOptions, ...props.options },
        });
    }
};

onMounted(() => {
    createChart();
});

watch(() => props.data, () => {
    createChart();
}, { deep: true });

onUnmounted(() => {
    if (chartInstance) {
        chartInstance.destroy();
    }
});
</script>
