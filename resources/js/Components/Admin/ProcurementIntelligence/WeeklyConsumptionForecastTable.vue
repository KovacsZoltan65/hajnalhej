<script setup>
defineProps({
    rows: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <section class="ui-card p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">Heti várható fogyás</h2>
            <span class="text-xs text-bakery-dark/60">4 hetes átlag</span>
        </div>
        <div v-if="rows.length" class="mt-4 overflow-x-auto">
            <table class="min-w-[760px] w-full text-sm">
                <thead class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                    <tr>
                        <th class="px-2 py-2">Alapanyag</th>
                        <th class="px-2 py-2 text-right">Elmúlt heti fogyás</th>
                        <th class="px-2 py-2 text-right">4 hetes átlag</th>
                        <th class="px-2 py-2 text-right">Következő heti várható</th>
                        <th class="px-2 py-2 text-right">Fedezet nap</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="row.ingredient_id" class="border-b border-bakery-brown/10">
                        <td class="px-2 py-3 font-medium text-bakery-dark">{{ row.ingredient_name }}</td>
                        <td class="px-2 py-3 text-right text-bakery-dark">{{ row.last_week_consumption }} {{ row.unit }}</td>
                        <td class="px-2 py-3 text-right text-bakery-dark">{{ row.four_week_average }} {{ row.unit }}</td>
                        <td class="px-2 py-3 text-right font-semibold text-bakery-dark">{{ row.next_week_forecast }} {{ row.unit }}</td>
                        <td class="px-2 py-3 text-right text-bakery-dark">{{ row.coverage_days ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else class="mt-4 rounded-lg border border-dashed border-bakery-brown/25 p-6 text-sm text-bakery-dark/65">
            Nincs production_out fogyási adat az előrejelzéshez.
        </div>
    </section>
</template>
