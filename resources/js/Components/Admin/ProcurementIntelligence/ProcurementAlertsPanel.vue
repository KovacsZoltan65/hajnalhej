<script setup>
import { trans } from 'laravel-vue-i18n';
import ProcurementUrgencyBadge from './ProcurementUrgencyBadge.vue';

defineProps({
    alerts: {
        type: Array,
        required: true,
    },
});

const alertLabel = (type) => trans(`admin_procurement_intelligence.alert_types.${type}`);
</script>

<template>
    <section class="ui-card p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">
                {{ trans('admin_procurement_intelligence.alerts.title') }}
            </h2>
            <span class="text-xs text-bakery-dark/60">
                {{ trans('admin_procurement_intelligence.counts.alerts', { count: alerts.length }) }}
            </span>
        </div>
        <div v-if="alerts.length" class="mt-4 grid gap-3">
            <article v-for="(alert, index) in alerts" :key="`${alert.type}-${alert.ingredient_id}-${index}`" class="rounded-lg border border-bakery-brown/15 p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-bakery-dark/55">{{ alertLabel(alert.type) }}</p>
                        <h3 class="mt-1 font-medium text-bakery-dark">{{ alert.ingredient_name }}</h3>
                        <p class="mt-1 text-sm text-bakery-dark/70">{{ alert.message }}</p>
                    </div>
                    <ProcurementUrgencyBadge :value="alert.severity" />
                </div>
            </article>
        </div>
        <div v-else class="mt-4 rounded-lg border border-dashed border-bakery-brown/25 p-6 text-sm text-bakery-dark/65">
            {{ trans('admin_procurement_intelligence.alerts.empty') }}
        </div>
    </section>
</template>
