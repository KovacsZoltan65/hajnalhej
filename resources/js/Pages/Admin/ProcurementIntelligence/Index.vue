<script setup>
import { Head, router } from '@inertiajs/vue3';
import Select from 'primevue/select';
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

const updateFilter = (key, value) => {
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
    router.get('/admin/procurement-intelligence', { days: 30 }, { preserveScroll: true, replace: true });
};
</script>

<template>
    <Head title="Beszerzési intelligencia" />

    <section class="space-y-6">
        <header class="ui-card p-5 sm:p-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-bakery-brown/70">Beszerzés</p>
                    <h1 class="mt-2 font-heading text-3xl text-bakery-dark">Beszerzési intelligencia</h1>
                    <p class="mt-2 text-sm text-bakery-dark/75">
                        Valós beszerzési tételek, készletmozgások és BOM használat alapján számolt ártrendek, fogyási előrejelzés és minimum készlet alapú utánrendelési jelzések.
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
                        placeholder="Alapanyag"
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
                        placeholder="Beszállító"
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
                        placeholder="Sürgősség"
                        show-clear
                        class="min-h-11 w-full"
                        @update:model-value="updateFilter('urgency', $event)"
                    />
                    <button
                        type="button"
                        class="min-h-11 rounded-lg border border-bakery-brown/25 px-4 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown/10"
                        @click="resetFilters"
                    >
                        Szűrők törlése
                    </button>
                </div>
            </div>

            <div class="mt-4 max-w-md">
                <Select
                    :model-value="filters.alert_type"
                    :options="filter_options.alert_types"
                    option-label="label"
                    option-value="value"
                    placeholder="Figyelmeztetés típusa"
                    show-clear
                    class="min-h-11 w-full"
                    @update:model-value="updateFilter('alert_type', $event)"
                />
            </div>
        </header>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <ProcurementSummaryCard
                label="Aktív figyelmeztetés"
                :value="dashboard.summary.alerts_count"
                hint="Szűrők után számolva"
                icon="pi pi-bell"
            />
            <ProcurementSummaryCard
                label="Kritikus utánrendelés"
                :value="dashboard.summary.critical_minimum_stock_count"
                hint="Azonnali beszerzési figyelem"
                icon="pi pi-exclamation-triangle"
            />
            <ProcurementSummaryCard
                label="Áremelkedés"
                :value="dashboard.summary.price_increase_count"
                hint="10% vagy nagyobb növekedés"
                icon="pi pi-arrow-up-right"
            />
            <ProcurementSummaryCard
                label="Elfogyási kockázat"
                :value="dashboard.summary.stockout_risk_count"
                hint="7 napon belüli fedezet"
                icon="pi pi-hourglass"
            />
        </div>

        <ProcurementAlertsPanel :alerts="dashboard.alerts" />
        <MinimumStockRecommendationsTable :rows="dashboard.minimum_stock_recommendations" />
        <SupplierPriceTrendTable :rows="dashboard.supplier_price_trends" />
        <IngredientCostTrendTable :rows="dashboard.ingredient_cost_trends" :recent-purchases="dashboard.recent_purchases" />
        <WeeklyConsumptionForecastTable :rows="dashboard.weekly_consumption_forecast" />

        <section class="ui-card p-4 text-sm text-bakery-dark/70 sm:p-5">
            <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">Számítási alapok</h2>
            <div class="mt-3 grid gap-2 md:grid-cols-2 xl:grid-cols-4">
                <p>Fogyási ablak: {{ dashboard.defaults.consumption_window_days }} nap production_out átlag.</p>
                <p>Áremelkedés jelzés: {{ dashboard.defaults.price_increase_alert_percent }}% felett.</p>
                <p>Elfogyási kockázat: {{ dashboard.defaults.stockout_warning_days }} napon belül.</p>
                <p>Utánrendelési cél: legalább {{ dashboard.defaults.minimum_stock_target_days }} napnyi várható fogyás.</p>
            </div>
        </section>
    </section>
</template>
