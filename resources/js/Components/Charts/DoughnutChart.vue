<template>
    <canvas ref="chartCanvas"></canvas>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue';
import {
    Chart,
    DoughnutController,
    ArcElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js';

// Register Chart.js components
Chart.register(
    DoughnutController,
    ArcElement,
    Title,
    Tooltip,
    Legend
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
            callbacks: {
                label: function(context) {
                    let label = context.label || '';
                    if (label) {
                        label += ': ';
                    }
                    label += context.parsed;

                    // Calculate percentage
                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                    label += ` (${percentage}%)`;

                    return label;
                }
            }
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
            type: 'doughnut',
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
