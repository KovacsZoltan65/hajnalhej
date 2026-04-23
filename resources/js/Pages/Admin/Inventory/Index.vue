<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SectionTitle from '@/Components/SectionTitle.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    dashboard: { type: Object, required: true },
    ledger: { type: Object, required: true },
    filters: { type: Object, required: true },
    movement_types: { type: Array, required: true },
    ingredient_options: { type: Array, required: true },
    waste_reasons: { type: Array, required: true },
});

const search = ref(props.filters.search ?? '');
const movementType = ref(props.filters.movement_type ?? '');
const ingredientId = ref(props.filters.ingredient_id ?? '');

const wasteForm = useForm({ ingredient_id: null, quantity: 1, reason: 'lejárt', occurred_at: new Date().toISOString().slice(0, 10) });
const adjustmentForm = useForm({ ingredient_id: null, difference: 0, unit_cost: null, occurred_at: new Date().toISOString().slice(0, 10), notes: '' });

const load = () => {
    router.get('/admin/inventory', {
        days: props.filters.days ?? 7,
        search: search.value || undefined,
        movement_type: movementType.value || undefined,
        ingredient_id: ingredientId.value || undefined,
    }, { preserveState: true, replace: true, preserveScroll: true });
};

const asCurrency = (v) => new Intl.NumberFormat('hu-HU').format(Number(v ?? 0));
</script>

<template>
    <Head title="Készlet dashboard" />

    <div class="space-y-6">
        <SectionTitle
            eyebrow="Admin / Készlet"
            title="Készlet dashboard és főkönyv"
            description="Valós készletérték, selejt és bevételezés metrikák, részletes mozgásnaplóval."
        />

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <article class="ui-card p-4"><p class="text-xs uppercase text-bakery-dark/60">Készletérték</p><p class="mt-2 font-heading text-3xl">{{ asCurrency(dashboard.summary.total_stock_value) }} Ft</p></article>
            <article class="ui-card p-4"><p class="text-xs uppercase text-bakery-dark/60">Alacsony készlet</p><p class="mt-2 font-heading text-3xl">{{ dashboard.summary.low_stock_count }}</p></article>
            <article class="ui-card p-4"><p class="text-xs uppercase text-bakery-dark/60">Kifogyott</p><p class="mt-2 font-heading text-3xl">{{ dashboard.summary.out_of_stock_count }}</p></article>
            <article class="ui-card p-4"><p class="text-xs uppercase text-bakery-dark/60">Heti selejtérték</p><p class="mt-2 font-heading text-3xl">{{ asCurrency(dashboard.summary.weekly_waste_cost) }} Ft</p></article>
            <article class="ui-card p-4"><p class="text-xs uppercase text-bakery-dark/60">Heti bevételezés</p><p class="mt-2 font-heading text-3xl">{{ asCurrency(dashboard.summary.weekly_purchase_value) }} Ft</p></article>
        </section>

        <section class="ui-card p-4 sm:p-5 space-y-4">
            <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">Készletmozgás főkönyv</h2>
            <div class="grid gap-3 md:grid-cols-4">
                <InputText v-model="search" placeholder="Keresés jegyzet / referencia" @keyup.enter="load" />
                <Select v-model="movementType" :options="[{ label: 'Mind', value: '' }, ...movement_types.map((t) => ({ label: t, value: t }))]" option-label="label" option-value="value" />
                <Select v-model="ingredientId" :options="[{ label: 'Mind', value: '' }, ...ingredient_options.map((i) => ({ label: i.name, value: i.id }))]" option-label="label" option-value="value" />
                <Button label="Szűrés" class="!min-h-11" @click="load" />
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                        <tr>
                            <th class="px-2 py-2">Dátum</th>
                            <th class="px-2 py-2">Alapanyag</th>
                            <th class="px-2 py-2">Típus</th>
                            <th class="px-2 py-2 text-right">Mennyiség</th>
                            <th class="px-2 py-2 text-right">Egységár</th>
                            <th class="px-2 py-2 text-right">Érték</th>
                            <th class="px-2 py-2">Referencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in ledger.data" :key="row.id" class="border-b border-bakery-brown/10">
                            <td class="px-2 py-2">{{ row.occurred_at }}</td>
                            <td class="px-2 py-2">{{ row.ingredient_name }}</td>
                            <td class="px-2 py-2">{{ row.movement_type }}</td>
                            <td class="px-2 py-2 text-right" :class="row.direction === 'out' ? 'text-red-700' : 'text-green-700'">{{ row.direction === 'out' ? '-' : '+' }}{{ row.quantity }}</td>
                            <td class="px-2 py-2 text-right">{{ row.unit_cost !== null ? `${asCurrency(row.unit_cost)} Ft` : '-' }}</td>
                            <td class="px-2 py-2 text-right">{{ row.total_cost !== null ? `${asCurrency(row.total_cost)} Ft` : '-' }}</td>
                            <td class="px-2 py-2">{{ row.reference_type }} #{{ row.reference_id || '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <div class="ui-card p-4 sm:p-5 space-y-3">
                <h3 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">Selejt rögzítése</h3>
                <Select v-model="wasteForm.ingredient_id" :options="ingredient_options.map((i) => ({ label: i.name, value: i.id }))" option-label="label" option-value="value" placeholder="Alapanyag" />
                <InputText v-model="wasteForm.quantity" type="number" min="0.001" step="0.001" placeholder="Mennyiség" />
                <Select v-model="wasteForm.reason" :options="waste_reasons.map((r) => ({ label: r, value: r }))" option-label="label" option-value="value" />
                <InputText v-model="wasteForm.occurred_at" type="date" />
                <Button label="Selejt könyvelése" class="!min-h-11" :disabled="wasteForm.processing" @click="wasteForm.post('/admin/inventory/waste', { preserveScroll: true })" />
            </div>

            <div class="ui-card p-4 sm:p-5 space-y-3">
                <h3 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">Készletkorrekció</h3>
                <Select v-model="adjustmentForm.ingredient_id" :options="ingredient_options.map((i) => ({ label: i.name, value: i.id }))" option-label="label" option-value="value" placeholder="Alapanyag" />
                <InputText v-model="adjustmentForm.difference" type="number" step="0.001" placeholder="Különbözet (+ / -)" />
                <InputText v-model="adjustmentForm.unit_cost" type="number" step="0.0001" placeholder="Egységár (opcionális)" />
                <InputText v-model="adjustmentForm.occurred_at" type="date" />
                <InputText v-model="adjustmentForm.notes" placeholder="Megjegyzés" />
                <Button label="Korrekció könyvelése" class="!min-h-11" :disabled="adjustmentForm.processing" @click="adjustmentForm.post('/admin/inventory/adjustments', { preserveScroll: true })" />
            </div>
        </section>
    </div>
</template>

