<script setup>
import { trans } from 'laravel-vue-i18n';
import ProcurementUrgencyBadge from './ProcurementUrgencyBadge.vue';

const props = defineProps({
    rows: {
        type: Array,
        required: true,
    },
    selectedIds: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:selectedIds']);

const toggleRow = (ingredientId) => {
    const selected = new Set(props.selectedIds);
    if (selected.has(ingredientId)) {
        selected.delete(ingredientId);
    } else {
        selected.add(ingredientId);
    }

    emit('update:selectedIds', Array.from(selected));
};

const toggleAll = () => {
    if (props.selectedIds.length === props.rows.length) {
        emit('update:selectedIds', []);
        return;
    }

    emit('update:selectedIds', props.rows.map((row) => row.ingredient_id));
};

const sourceLabel = (source) => trans(`admin_procurement_intelligence.supplier_sources.${source || 'none'}`);

const dayLabel = (days) => trans('admin_procurement_intelligence.units.days', { count: days });
</script>

<template>
    <section class="ui-card p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">
                {{ trans('admin_procurement_intelligence.minimum_stock.title') }}
            </h2>
            <span class="text-xs text-bakery-dark/60">
                {{ trans('admin_procurement_intelligence.counts.ingredients', { count: rows.length }) }}
            </span>
        </div>
        <div v-if="rows.length" class="mt-4 overflow-x-auto">
            <table class="min-w-[1260px] w-full text-sm">
                <thead class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                    <tr>
                        <th class="px-2 py-2">
                            <input
                                type="checkbox"
                                class="h-5 w-5 rounded border-bakery-brown/30 text-bakery-brown"
                                :checked="rows.length > 0 && selectedIds.length === rows.length"
                                :aria-label="trans('admin_procurement_intelligence.minimum_stock.select_all')"
                                @change="toggleAll"
                            />
                        </th>
                        <th class="px-2 py-2">{{ trans('admin_procurement_intelligence.columns.ingredient') }}</th>
                        <th class="px-2 py-2 text-right">{{ trans('admin_procurement_intelligence.columns.current_stock') }}</th>
                        <th class="px-2 py-2 text-right">{{ trans('admin_procurement_intelligence.columns.minimum_stock') }}</th>
                        <th class="px-2 py-2 text-right">{{ trans('admin_procurement_intelligence.columns.weekly_average_consumption') }}</th>
                        <th class="px-2 py-2">{{ trans('admin_procurement_intelligence.columns.recommended_supplier') }}</th>
                        <th class="px-2 py-2 text-right">{{ trans('admin_procurement_intelligence.columns.lead_time') }}</th>
                        <th class="px-2 py-2 text-right">{{ trans('admin_procurement_intelligence.columns.pack_size') }}</th>
                        <th class="px-2 py-2 text-right">{{ trans('admin_procurement_intelligence.columns.minimum_order') }}</th>
                        <th class="px-2 py-2 text-right">{{ trans('admin_procurement_intelligence.columns.stock_days') }}</th>
                        <th class="px-2 py-2 text-right">{{ trans('admin_procurement_intelligence.columns.suggested_order') }}</th>
                        <th class="px-2 py-2">{{ trans('admin_procurement_intelligence.columns.urgency') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="row.ingredient_id" class="border-b border-bakery-brown/10">
                        <td class="px-2 py-3">
                            <input
                                type="checkbox"
                                class="h-5 w-5 rounded border-bakery-brown/30 text-bakery-brown"
                                :checked="selectedIds.includes(row.ingredient_id)"
                                :aria-label="trans('admin_procurement_intelligence.minimum_stock.select_one', { name: row.ingredient_name })"
                                @change="toggleRow(row.ingredient_id)"
                            />
                        </td>
                        <td class="px-2 py-3 font-medium text-bakery-dark">{{ row.ingredient_name }}</td>
                        <td class="px-2 py-3 text-right text-bakery-dark">{{ row.current_stock }} {{ row.unit }}</td>
                        <td class="px-2 py-3 text-right text-bakery-dark">{{ row.minimum_stock }} {{ row.unit }}</td>
                        <td class="px-2 py-3 text-right text-bakery-dark">{{ row.weekly_average_consumption }} {{ row.unit }}</td>
                        <td class="px-2 py-3 text-bakery-dark">
                            <div>
                                <p>{{ row.recommended_supplier_name || trans('admin_procurement_intelligence.minimum_stock.no_supplier') }}</p>
                                <p class="text-xs text-bakery-dark/55">{{ sourceLabel(row.supplier_source) }}</p>
                            </div>
                        </td>
                        <td class="px-2 py-3 text-right text-bakery-dark">{{ row.lead_time_days !== null ? dayLabel(row.lead_time_days) : '-' }}</td>
                        <td class="px-2 py-3 text-right text-bakery-dark">{{ row.pack_size ? `${row.pack_size} ${row.unit}` : '-' }}</td>
                        <td class="px-2 py-3 text-right text-bakery-dark">{{ row.minimum_order_quantity ? `${row.minimum_order_quantity} ${row.unit}` : '-' }}</td>
                        <td class="px-2 py-3 text-right text-bakery-dark">{{ row.days_on_hand ?? '-' }}</td>
                        <td class="px-2 py-3 text-right font-semibold text-bakery-dark">{{ row.suggested_order_quantity }} {{ row.unit }}</td>
                        <td class="px-2 py-3"><ProcurementUrgencyBadge :value="row.urgency" /></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else class="mt-4 rounded-lg border border-dashed border-bakery-brown/25 p-6 text-sm text-bakery-dark/65">
            {{ trans('admin_procurement_intelligence.minimum_stock.empty') }}
        </div>
    </section>
</template>
