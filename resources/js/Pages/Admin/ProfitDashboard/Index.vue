<script setup>
import { Head, router } from "@inertiajs/vue3";
import { computed } from "vue";
import Select from "primevue/select";
import AdminLayout from "@/Layouts/AdminLayout.vue";

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
});

const dayOptions = [
    { label: "7 nap", value: 7 },
    { label: "14 nap", value: 14 },
    { label: "30 nap", value: 30 },
    { label: "90 nap", value: 90 },
];

const updateDays = (value) => {
    router.get(
        "/admin/profit-dashboard",
        { days: value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const productMargins = computed(() => props.dashboard.product_margins ?? []);
const topProfitProducts = computed(() => props.dashboard.top_profit_products ?? []);
const trendPoints = computed(() => props.dashboard.order_profit_trend?.points ?? []);

const formatCurrency = (value) =>
    new Intl.NumberFormat("hu-HU", {
        style: "currency",
        currency: "HUF",
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));

const formatPercent = (value) => `${Number(value ?? 0).toFixed(2)}%`;
</script>

<template>
    <Head title="Profit irányítópult" />

    <section class="space-y-6">
        <header class="ui-card p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="font-heading text-3xl text-bakery-dark">
                        Profit irányítópult
                    </h1>
                    <p class="mt-2 text-sm text-bakery-dark/75">
                        Recept/BOM alapú becsült önköltség, termék margin és rendelési profit trend.
                    </p>
                </div>
                <div class="w-full sm:w-48">
                    <Select
                        :model-value="filters.days"
                        :options="dayOptions"
                        option-label="label"
                        option-value="value"
                        class="w-full"
                        @update:model-value="updateDays"
                    />
                </div>
            </div>
        </header>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">Becsült önköltség (katalógus)</p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">{{ formatCurrency(dashboard.summary.estimated_cost_total) }}</p>
            </article>
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">Katalógus érték</p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">{{ formatCurrency(dashboard.summary.catalog_value_total) }}</p>
            </article>
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">Becsült profit (időszak)</p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">{{ formatCurrency(dashboard.summary.period_estimated_profit) }}</p>
            </article>
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">Valós anyagköltség (időszak)</p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">{{ formatCurrency(dashboard.summary.period_actual_material_cost) }}</p>
            </article>
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">Selejt költség (időszak)</p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">{{ formatCurrency(dashboard.summary.period_waste_cost) }}</p>
            </article>
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">Bruttó profit (valós)</p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">{{ formatCurrency(dashboard.summary.period_gross_profit) }}</p>
            </article>
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">Margin % (valós)</p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">{{ formatPercent(dashboard.summary.period_margin_rate) }}</p>
            </article>
            <article class="ui-card p-4 md:col-span-2 xl:col-span-2">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">Estimated vs Actual delta</p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">{{ formatCurrency(dashboard.summary.estimated_vs_actual_delta) }}</p>
            </article>
        </div>

        <section class="ui-card p-4 sm:p-5">
            <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">Termék margin (BOM becslés)</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                        <tr>
                            <th class="px-2 py-2">Termék</th>
                            <th class="px-2 py-2 text-right">Ár</th>
                            <th class="px-2 py-2 text-right">Becsült önköltség</th>
                            <th class="px-2 py-2 text-right">Margin</th>
                            <th class="px-2 py-2 text-right">Margin %</th>
                            <th class="px-2 py-2 text-right">BOM tétel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in productMargins"
                            :key="`margin-${row.product_id}`"
                            class="border-b border-bakery-brown/10"
                        >
                            <td class="px-2 py-2 font-medium text-bakery-dark">{{ row.product_name }}</td>
                            <td class="px-2 py-2 text-right text-bakery-dark">{{ formatCurrency(row.product_price) }}</td>
                            <td class="px-2 py-2 text-right text-bakery-dark">{{ formatCurrency(row.estimated_unit_cost) }}</td>
                            <td class="px-2 py-2 text-right font-semibold text-bakery-dark">{{ formatCurrency(row.margin_amount) }}</td>
                            <td class="px-2 py-2 text-right text-bakery-dark">{{ formatPercent(row.margin_rate) }}</td>
                            <td class="px-2 py-2 text-right text-bakery-dark">{{ row.bom_items }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ui-card p-4 sm:p-5">
            <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">Top profit termékek (időszak)</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                        <tr>
                            <th class="px-2 py-2">Termék</th>
                            <th class="px-2 py-2 text-right">Bevétel</th>
                            <th class="px-2 py-2 text-right">Becsült költség</th>
                            <th class="px-2 py-2 text-right">Becsült profit</th>
                            <th class="px-2 py-2 text-right">Margin %</th>
                            <th class="px-2 py-2 text-right">Darab</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in topProfitProducts"
                            :key="`top-profit-${row.product_id}`"
                            class="border-b border-bakery-brown/10"
                        >
                            <td class="px-2 py-2 font-medium text-bakery-dark">{{ row.product_name }}</td>
                            <td class="px-2 py-2 text-right text-bakery-dark">{{ formatCurrency(row.revenue) }}</td>
                            <td class="px-2 py-2 text-right text-bakery-dark">{{ formatCurrency(row.estimated_cost) }}</td>
                            <td class="px-2 py-2 text-right font-semibold text-bakery-dark">{{ formatCurrency(row.estimated_profit) }}</td>
                            <td class="px-2 py-2 text-right text-bakery-dark">{{ formatPercent(row.margin_rate) }}</td>
                            <td class="px-2 py-2 text-right text-bakery-dark">{{ row.quantity }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ui-card p-4 sm:p-5">
            <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">Rendelési profit trend</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                        <tr>
                            <th class="px-2 py-2">Dátum</th>
                            <th class="px-2 py-2 text-right">Bevétel</th>
                            <th class="px-2 py-2 text-right">Becsült költség</th>
                            <th class="px-2 py-2 text-right">Valós anyagköltség</th>
                            <th class="px-2 py-2 text-right">Bruttó profit</th>
                            <th class="px-2 py-2 text-right">Becsült profit</th>
                            <th class="px-2 py-2 text-right">Margin %</th>
                            <th class="px-2 py-2 text-right">Rendelés</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in trendPoints"
                            :key="`profit-trend-${row.date}`"
                            class="border-b border-bakery-brown/10"
                        >
                            <td class="px-2 py-2 font-medium text-bakery-dark">{{ row.date }}</td>
                            <td class="px-2 py-2 text-right text-bakery-dark">{{ formatCurrency(row.revenue) }}</td>
                            <td class="px-2 py-2 text-right text-bakery-dark">{{ formatCurrency(row.estimated_cost) }}</td>
                            <td class="px-2 py-2 text-right text-bakery-dark">{{ formatCurrency(row.actual_material_cost) }}</td>
                            <td class="px-2 py-2 text-right text-bakery-dark">{{ formatCurrency(row.gross_profit) }}</td>
                            <td class="px-2 py-2 text-right font-semibold text-bakery-dark">{{ formatCurrency(row.estimated_profit) }}</td>
                            <td class="px-2 py-2 text-right text-bakery-dark">{{ formatPercent(row.margin_rate) }}</td>
                            <td class="px-2 py-2 text-right text-bakery-dark">{{ row.orders_count }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</template>
