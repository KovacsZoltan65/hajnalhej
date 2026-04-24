<script setup>
defineProps({
    rows: {
        type: Array,
        required: true,
    },
    recentPurchases: {
        type: Array,
        default: () => [],
    },
});

const formatCurrency = (value) =>
    new Intl.NumberFormat('hu-HU', { style: 'currency', currency: 'HUF', maximumFractionDigits: 0 }).format(Number(value ?? 0));
</script>

<template>
    <section class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="ui-card p-4 sm:p-5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">Ingredient költség-idősor</h2>
                <span class="text-xs text-bakery-dark/60">{{ rows.length }} pont</span>
            </div>
            <div v-if="rows.length" class="mt-4 overflow-x-auto">
                <table class="min-w-[780px] w-full text-sm">
                    <thead class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                        <tr>
                            <th class="px-2 py-2">Dátum</th>
                            <th class="px-2 py-2">Alapanyag</th>
                            <th class="px-2 py-2">Beszállító</th>
                            <th class="px-2 py-2 text-right">Átlagár</th>
                            <th class="px-2 py-2 text-right">Weighted avg</th>
                            <th class="px-2 py-2 text-right">Utolsó ár</th>
                            <th class="px-2 py-2 text-right">Mennyiség</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in rows" :key="`${row.period_date}-${row.ingredient_id}-${row.supplier_id ?? 'none'}`" class="border-b border-bakery-brown/10">
                            <td class="px-2 py-3 font-medium text-bakery-dark">{{ row.period_date }}</td>
                            <td class="px-2 py-3 text-bakery-dark">{{ row.ingredient_name }}</td>
                            <td class="px-2 py-3 text-bakery-dark/80">{{ row.supplier_name }}</td>
                            <td class="px-2 py-3 text-right text-bakery-dark">{{ formatCurrency(row.average_unit_cost) }}</td>
                            <td class="px-2 py-3 text-right font-semibold text-bakery-dark">{{ formatCurrency(row.weighted_average_cost) }}</td>
                            <td class="px-2 py-3 text-right text-bakery-dark">{{ formatCurrency(row.last_unit_cost) }}</td>
                            <td class="px-2 py-3 text-right text-bakery-dark">{{ row.purchased_quantity }} {{ row.unit }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="mt-4 rounded-lg border border-dashed border-bakery-brown/25 p-6 text-sm text-bakery-dark/65">
                Nincs költség-idősor adat a kiválasztott időablakban.
            </div>
        </div>

        <aside class="ui-card p-4 sm:p-5">
            <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">Legutóbbi 5 beszerzés</h2>
            <div v-if="recentPurchases.length" class="mt-4 space-y-3">
                <article v-for="row in recentPurchases" :key="`${row.purchase_date}-${row.ingredient_id}-${row.unit_cost}`" class="rounded-lg border border-bakery-brown/15 p-3">
                    <p class="font-medium text-bakery-dark">{{ row.ingredient_name }}</p>
                    <p class="mt-1 text-sm text-bakery-dark/70">{{ row.supplier_name }} · {{ row.purchase_date }}</p>
                    <p class="mt-2 text-sm text-bakery-dark">{{ row.quantity }} {{ row.unit }} · {{ formatCurrency(row.unit_cost) }}</p>
                </article>
            </div>
            <p v-else class="mt-4 rounded-lg border border-dashed border-bakery-brown/25 p-4 text-sm text-bakery-dark/65">
                Még nincs friss beszerzési tétel.
            </p>
        </aside>
    </section>
</template>
