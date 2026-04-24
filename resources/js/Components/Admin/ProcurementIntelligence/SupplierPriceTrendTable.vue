<script setup>
defineProps({
    rows: {
        type: Array,
        required: true,
    },
});

const formatCurrency = (value) =>
    value === null || value === undefined
        ? '-'
        : new Intl.NumberFormat('hu-HU', { style: 'currency', currency: 'HUF', maximumFractionDigits: 0 }).format(Number(value));

const formatPercent = (value) => `${Number(value ?? 0).toFixed(2)}%`;
</script>

<template>
    <section class="ui-card p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">Beszállítói ártrend</h2>
            <span class="text-xs text-bakery-dark/60">{{ rows.length }} sor</span>
        </div>
        <div v-if="rows.length" class="mt-4 overflow-x-auto">
            <table class="min-w-[980px] w-full text-sm">
                <thead class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                    <tr>
                        <th class="px-2 py-2">Alapanyag</th>
                        <th class="px-2 py-2">Beszállító</th>
                        <th class="px-2 py-2 text-right">Utolsó ár</th>
                        <th class="px-2 py-2 text-right">Előző ár</th>
                        <th class="px-2 py-2 text-right">Változás</th>
                        <th class="px-2 py-2 text-right">Változás %</th>
                        <th class="px-2 py-2">Legolcsóbb</th>
                        <th class="px-2 py-2">Legdrágább</th>
                        <th class="px-2 py-2">Trend</th>
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
            Nincs beszerzési ártrend a kiválasztott szűrőkkel.
        </div>
    </section>
</template>
