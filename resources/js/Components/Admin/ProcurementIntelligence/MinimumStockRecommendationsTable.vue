<script setup>
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

const sourceLabel = (source) => ({
    preferred_supplier: 'Preferált',
    latest_supplier: 'Legutóbbi',
    cheapest_fresh_supplier: 'Legolcsóbb friss',
    none: 'Nincs adat',
}[source] ?? 'Nincs adat');
</script>

<template>
    <section class="ui-card p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">Utánrendelési javaslat</h2>
            <span class="text-xs text-bakery-dark/60">{{ rows.length }} alapanyag</span>
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
                                aria-label="Minden utánrendelési javaslat kijelölése"
                                @change="toggleAll"
                            />
                        </th>
                        <th class="px-2 py-2">Alapanyag</th>
                        <th class="px-2 py-2 text-right">Aktuális készlet</th>
                        <th class="px-2 py-2 text-right">Minimum készlet</th>
                        <th class="px-2 py-2 text-right">Heti átlagfogyás</th>
                        <th class="px-2 py-2">Ajánlott beszállító</th>
                        <th class="px-2 py-2 text-right">Lead time</th>
                        <th class="px-2 py-2 text-right">Csomag</th>
                        <th class="px-2 py-2 text-right">Minimum rendelés</th>
                        <th class="px-2 py-2 text-right">Készlet nap</th>
                        <th class="px-2 py-2 text-right">Javasolt rendelés</th>
                        <th class="px-2 py-2">Sürgősség</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="row.ingredient_id" class="border-b border-bakery-brown/10">
                        <td class="px-2 py-3">
                            <input
                                type="checkbox"
                                class="h-5 w-5 rounded border-bakery-brown/30 text-bakery-brown"
                                :checked="selectedIds.includes(row.ingredient_id)"
                                :aria-label="`${row.ingredient_name} kijelölése beszerzési tervezethez`"
                                @change="toggleRow(row.ingredient_id)"
                            />
                        </td>
                        <td class="px-2 py-3 font-medium text-bakery-dark">{{ row.ingredient_name }}</td>
                        <td class="px-2 py-3 text-right text-bakery-dark">{{ row.current_stock }} {{ row.unit }}</td>
                        <td class="px-2 py-3 text-right text-bakery-dark">{{ row.minimum_stock }} {{ row.unit }}</td>
                        <td class="px-2 py-3 text-right text-bakery-dark">{{ row.weekly_average_consumption }} {{ row.unit }}</td>
                        <td class="px-2 py-3 text-bakery-dark">
                            <div>
                                <p>{{ row.recommended_supplier_name || 'Nincs beszállító' }}</p>
                                <p class="text-xs text-bakery-dark/55">{{ sourceLabel(row.supplier_source) }}</p>
                            </div>
                        </td>
                        <td class="px-2 py-3 text-right text-bakery-dark">{{ row.lead_time_days !== null ? `${row.lead_time_days} nap` : '-' }}</td>
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
            Nincs utánrendelési javaslat a jelenlegi készlet és fogyás alapján.
        </div>
    </section>
</template>
