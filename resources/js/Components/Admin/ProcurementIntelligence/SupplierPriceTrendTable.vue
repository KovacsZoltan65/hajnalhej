<script setup>
import { trans } from 'laravel-vue-i18n';

defineProps({
    rows: {
        type: Array,
        required: true,
    },
});

const formatCurrency = (value) =>
    value === null || value === undefined
        ? '-'
        : new Intl.NumberFormat(trans('common.locale'), { style: 'currency', currency: trans('common.currency'), maximumFractionDigits: 0 }).format(Number(value));

const formatPercent = (value) => `${Number(value ?? 0).toFixed(2)}%`;
</script>

<template>
    <section class="ui-card p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">
                {{ trans('admin_procurement_intelligence.supplier_price_trends.title') }}
            </h2>
            <span class="text-xs text-bakery-dark/60">
                {{ trans('admin_procurement_intelligence.counts.rows', { count: rows.length }) }}
            </span>
        </div>
        <div v-if="rows.length" class="mt-4 overflow-x-auto">
            <table class="min-w-[980px] w-full text-sm">
                <thead class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                    <tr>
                        <th class="px-2 py-2">{{ trans('admin_procurement_intelligence.columns.ingredient') }}</th>
                        <th class="px-2 py-2">{{ trans('admin_procurement_intelligence.columns.supplier') }}</th>
                        <th class="px-2 py-2 text-right">{{ trans('admin_procurement_intelligence.columns.last_price') }}</th>
                        <th class="px-2 py-2 text-right">{{ trans('admin_procurement_intelligence.columns.previous_price') }}</th>
                        <th class="px-2 py-2 text-right">{{ trans('admin_procurement_intelligence.columns.change') }}</th>
                        <th class="px-2 py-2 text-right">{{ trans('admin_procurement_intelligence.columns.change_percent') }}</th>
                        <th class="px-2 py-2">{{ trans('admin_procurement_intelligence.columns.cheapest') }}</th>
                        <th class="px-2 py-2">{{ trans('admin_procurement_intelligence.columns.most_expensive') }}</th>
                        <th class="px-2 py-2">{{ trans('admin_procurement_intelligence.columns.trend') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="`${row.ingredient_id}-${row.supplier_id ?? 'none'}`" class="border-b border-bakery-brown/10">
                        <td class="px-2 py-3 font-medium text-bakery-dark">{{ row.ingredient_name }}</td>
                        <td class="px-2 py-3 text-bakery-dark/80">{{ row.supplier_name }}</td>
                        <td class="px-2 py-3 text-right text-bakery-dark">{{ formatCurrency(row.last_unit_cost) }}</td>
                        <td class="px-2 py-3 text-right text-bakery-dark">{{ formatCurrency(row.previous_unit_cost) }}</td>
                        <td class="px-2 py-3 text-right text-bakery-dark">{{ formatCurrency(row.change_amount) }}</td>
                        <td class="px-2 py-3 text-right font-semibold" :class="row.change_percent >= 10 ? 'text-red-700' : 'text-bakery-dark'">
                            {{ formatPercent(row.change_percent) }}
                        </td>
                        <td class="px-2 py-3 text-bakery-dark/80">{{ row.cheapest_supplier?.supplier_name ?? '-' }}</td>
                        <td class="px-2 py-3 text-bakery-dark/80">{{ row.most_expensive_supplier?.supplier_name ?? '-' }}</td>
                        <td class="px-2 py-3 text-bakery-dark">{{ row.trend }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else class="mt-4 rounded-lg border border-dashed border-bakery-brown/25 p-6 text-sm text-bakery-dark/65">
            {{ trans('admin_procurement_intelligence.supplier_price_trends.empty') }}
        </div>
    </section>
</template>
