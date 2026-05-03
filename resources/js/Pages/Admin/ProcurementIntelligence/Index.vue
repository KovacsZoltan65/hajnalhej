<script setup>
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Button from 'primevue/button';
import Select from 'primevue/select';
import { trans } from 'laravel-vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ProcurementSummaryCard from '@/Components/Admin/ProcurementIntelligence/ProcurementSummaryCard.vue';
import SupplierPriceTrendTable from '@/Components/Admin/ProcurementIntelligence/SupplierPriceTrendTable.vue';
import IngredientCostTrendTable from '@/Components/Admin/ProcurementIntelligence/IngredientCostTrendTable.vue';
import MinimumStockRecommendationsTable from '@/Components/Admin/ProcurementIntelligence/MinimumStockRecommendationsTable.vue';
import WeeklyConsumptionForecastTable from '@/Components/Admin/ProcurementIntelligence/WeeklyConsumptionForecastTable.vue';
import ProcurementAlertsPanel from '@/Components/Admin/ProcurementIntelligence/ProcurementAlertsPanel.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    filters: {
        type: Object,
        required: true,
    },
    dashboard: {
        type: Object,
        required: true,
    },
    filter_options: {
        type: Object,
        required: true,
    },
});

const selectedRecommendationIds = ref([]);
const draftGenerationProcessing = ref(false);

const selectedCount = computed(() => selectedRecommendationIds.value.length);
const generatableCount = computed(() => props.dashboard.minimum_stock_recommendations.length);

const selectionSummary = computed(() => {
    if (selectedCount.value > 0) {
        return trans('admin_procurement_intelligence.selection.selected', {
            count: selectedCount.value,
        });
    }

    return trans('admin_procurement_intelligence.selection.generatable', {
        count: generatableCount.value,
    });
});

const updateFilter = (key, value) => {
    selectedRecommendationIds.value = [];

    router.get(
        '/admin/procurement-intelligence',
        {
            ...props.filters,
            [key]: value ?? '',
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const resetFilters = () => {
    selectedRecommendationIds.value = [];

    router.get('/admin/procurement-intelligence', { days: 30 }, { preserveScroll: true, replace: true });
};

const generatePurchaseDrafts = () => {
    draftGenerationProcessing.value = true;

    router.post(
        '/admin/procurement-intelligence/purchase-drafts',
        {
            days: props.filters.days,
            ingredient_id: props.filters.ingredient_id,
            supplier_id: props.filters.supplier_id,
            urgency: props.filters.urgency,
            ingredient_ids: selectedRecommendationIds.value,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                draftGenerationProcessing.value = false;
            },
        },
    );
};
</script>

<template>
    <Head :title="trans('admin_procurement_intelligence.meta_title')" />

    <section class="space-y-6">
        <header class="ui-card p-5 sm:p-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-bakery-brown/70">
                        {{ trans('admin_procurement_intelligence.eyebrow') }}
                    </p>
                    <h1 class="mt-2 font-heading text-3xl text-bakery-dark">
                        {{ trans('admin_procurement_intelligence.title') }}
                    </h1>
                    <p class="mt-2 text-sm text-bakery-dark/75">
                        {{ trans('admin_procurement_intelligence.description') }}
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:w-[680px] xl:grid-cols-5">
                    <Select
                        :model-value="filters.days"
                        :options="filter_options.days"
                        option-label="label"
                        option-value="value"
                        class="min-h-11 w-full"
                        @update:model-value="updateFilter('days', $event)"
                    />
                    <Select
                        :model-value="filters.ingredient_id"
                        :options="filter_options.ingredients"
                        option-label="label"
                        option-value="value"
                        :placeholder="trans('admin_procurement_intelligence.filters.ingredient')"
                        show-clear
                        filter
                        class="min-h-11 w-full"
                        @update:model-value="updateFilter('ingredient_id', $event)"
                    />
                    <Select
                        :model-value="filters.supplier_id"
                        :options="filter_options.suppliers"
                        option-label="label"
                        option-value="value"
                        :placeholder="trans('admin_procurement_intelligence.filters.supplier')"
                        show-clear
                        filter
                        class="min-h-11 w-full"
                        @update:model-value="updateFilter('supplier_id', $event)"
                    />
                    <Select
                        :model-value="filters.urgency"
                        :options="filter_options.urgencies"
                        option-label="label"
                        option-value="value"
                        :placeholder="trans('admin_procurement_intelligence.filters.urgency')"
                        show-clear
                        class="min-h-11 w-full"
                        @update:model-value="updateFilter('urgency', $event)"
                    />
                    <button
                        type="button"
                        class="min-h-11 rounded-lg border border-bakery-brown/25 px-4 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown/10"
                        @click="resetFilters"
                    >
                        {{ trans('common.clear_filters') }}
                    </button>
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <Select
                    :model-value="filters.alert_type"
                    :options="filter_options.alert_types"
                    option-label="label"
                    option-value="value"
                    :placeholder="trans('admin_procurement_intelligence.filters.alert_type')"
                    show-clear
                    class="min-h-11 w-full lg:max-w-md"
                    @update:model-value="updateFilter('alert_type', $event)"
                />
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <span class="text-xs text-bakery-dark/60">
                        {{ selectionSummary }}
                    </span>
                    <Button
                        icon="pi pi-file-plus"
                        :label="trans('admin_procurement_intelligence.actions.generate_purchase_drafts')"
                        class="!min-h-11"
                        :disabled="generatableCount === 0 || draftGenerationProcessing"
                        :loading="draftGenerationProcessing"
                        @click="generatePurchaseDrafts"
                    />
                </div>
            </div>
        </header>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <ProcurementSummaryCard
                :label="trans('admin_procurement_intelligence.summary.active_alerts')"
                :value="dashboard.summary.alerts_count"
                :hint="trans('admin_procurement_intelligence.summary.filtered_hint')"
                icon="pi pi-bell"
            />
            <ProcurementSummaryCard
                :label="trans('admin_procurement_intelligence.summary.critical_reorders')"
                :value="dashboard.summary.critical_minimum_stock_count"
                :hint="trans('admin_procurement_intelligence.summary.critical_hint')"
                icon="pi pi-exclamation-triangle"
            />
            <ProcurementSummaryCard
                :label="trans('admin_procurement_intelligence.summary.price_increase')"
                :value="dashboard.summary.price_increase_count"
                :hint="trans('admin_procurement_intelligence.summary.price_increase_hint')"
                icon="pi pi-arrow-up-right"
            />
            <ProcurementSummaryCard
                :label="trans('admin_procurement_intelligence.summary.stockout_risk')"
                :value="dashboard.summary.stockout_risk_count"
                :hint="trans('admin_procurement_intelligence.summary.stockout_hint')"
                icon="pi pi-hourglass"
            />
        </div>

        <ProcurementAlertsPanel :alerts="dashboard.alerts" />
        <MinimumStockRecommendationsTable
            :rows="dashboard.minimum_stock_recommendations"
            v-model:selected-ids="selectedRecommendationIds"
        />
        <SupplierPriceTrendTable :rows="dashboard.supplier_price_trends" />
        <IngredientCostTrendTable :rows="dashboard.ingredient_cost_trends" :recent-purchases="dashboard.recent_purchases" />
        <WeeklyConsumptionForecastTable :rows="dashboard.weekly_consumption_forecast" />

        <section class="ui-card p-4 text-sm text-bakery-dark/70 sm:p-5">
            <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">
                {{ trans('admin_procurement_intelligence.calculation.title') }}
            </h2>
            <div class="mt-3 grid gap-2 md:grid-cols-2 xl:grid-cols-4">
                <p>{{ trans('admin_procurement_intelligence.calculation.consumption_window', { days: dashboard.defaults.consumption_window_days }) }}</p>
                <p>{{ trans('admin_procurement_intelligence.calculation.price_increase', { percent: dashboard.defaults.price_increase_alert_percent }) }}</p>
                <p>{{ trans('admin_procurement_intelligence.calculation.stockout_risk', { days: dashboard.defaults.stockout_warning_days }) }}</p>
                <p>{{ trans('admin_procurement_intelligence.calculation.reorder_target', { days: dashboard.defaults.safety_stock_days }) }}</p>
            </div>
        </section>
    </section>
</template>
