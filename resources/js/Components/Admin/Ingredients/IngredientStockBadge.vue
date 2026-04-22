<script setup>
import { computed } from 'vue';

const props = defineProps({
    currentStock: {
        type: Number,
        required: true,
    },
    minimumStock: {
        type: Number,
        required: true,
    },
    unit: {
        type: String,
        required: true,
    },
});

const isLowStock = computed(() => props.currentStock <= props.minimumStock);

const formattedCurrent = computed(() =>
    new Intl.NumberFormat('hu-HU', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 3,
    }).format(props.currentStock),
);

const formattedMinimum = computed(() =>
    new Intl.NumberFormat('hu-HU', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 3,
    }).format(props.minimumStock),
);
</script>

<template>
    <div class="space-y-1">
        <span
            class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
            :class="isLowStock ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
        >
            {{ isLowStock ? 'Alacsony készlet' : 'Rendben' }}
        </span>
        <p class="text-xs text-bakery-dark/70">{{ formattedCurrent }} {{ unit }} / min {{ formattedMinimum }} {{ unit }}</p>
    </div>
</template>

