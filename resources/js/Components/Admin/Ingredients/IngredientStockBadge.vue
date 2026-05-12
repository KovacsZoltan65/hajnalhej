<script setup>
import { computed } from 'vue';
import { useLocaleFormat } from '@/composables/useLocaleFormat';

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
const { formatNumber } = useLocaleFormat();

const formattedCurrent = computed(() =>
    formatNumber(props.currentStock, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 3,
    }),
);

const formattedMinimum = computed(() =>
    formatNumber(props.minimumStock, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 3,
    }),
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
