<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SectionTitle from '@/Components/SectionTitle.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    stock_counts: { type: Object, required: true },
    statuses: { type: Array, required: true },
    ingredient_options: { type: Array, required: true },
    filters: { type: Object, required: true },
});

const status = ref(props.filters.status ?? '');
const load = () => {
    router.get('/admin/stock-counts', { status: status.value || undefined }, { preserveState: true, replace: true, preserveScroll: true });
};

const form = useForm({
    count_date: new Date().toISOString().slice(0, 10),
    notes: '',
    items: props.ingredient_options.slice(0, 5).map((i) => ({
        ingredient_id: i.id,
        expected_quantity: i.current_stock,
        counted_quantity: i.current_stock,
    })),
});
</script>

<template>
    <Head title="Leltár" />
    <div class="space-y-6">
        <SectionTitle
            eyebrow="Admin / Leltár"
            title="Leltárak"
            description="Készletszámlálás, különbözet könyvelés és zárás auditált folyamatban."
        />

        <section class="ui-card p-4 sm:p-5 space-y-4">
            <div class="grid gap-3 md:grid-cols-3">
                <Select v-model="status" :options="[{ label: 'Mind', value: '' }, ...statuses.map((s) => ({ label: s, value: s }))]" option-label="label" option-value="value" />
                <div />
                <Button label="Szűrés" class="!min-h-11" @click="load" />
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                        <tr>
                            <th class="px-2 py-2">Dátum</th>
                            <th class="px-2 py-2">Státusz</th>
                            <th class="px-2 py-2 text-right">Tételek</th>
                            <th class="px-2 py-2">Készítette</th>
                            <th class="px-2 py-2 text-right">Művelet</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in stock_counts.data" :key="row.id" class="border-b border-bakery-brown/10">
                            <td class="px-2 py-2">{{ row.count_date }}</td>
                            <td class="px-2 py-2">{{ row.status }}</td>
                            <td class="px-2 py-2 text-right">{{ row.items_count }}</td>
                            <td class="px-2 py-2">{{ row.created_by || '-' }}</td>
                            <td class="px-2 py-2 text-right"><Link :href="`/admin/stock-counts/${row.id}`" class="underline">Részletek</Link></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ui-card p-4 sm:p-5 space-y-3">
            <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">Új leltár</h2>
            <InputText v-model="form.count_date" type="date" />
            <InputText v-model="form.notes" placeholder="Megjegyzés" />
            <div class="space-y-2">
                <div v-for="(item, idx) in form.items" :key="idx" class="grid gap-2 md:grid-cols-3">
                    <Select v-model="item.ingredient_id" :options="ingredient_options.map((i) => ({ label: i.name, value: i.id }))" option-label="label" option-value="value" />
                    <InputText v-model="item.expected_quantity" type="number" step="0.001" placeholder="Várt mennyiség" />
                    <InputText v-model="item.counted_quantity" type="number" step="0.001" placeholder="Számolt mennyiség" />
                </div>
            </div>
            <Button label="Leltár mentése" class="!min-h-11" :disabled="form.processing" @click="form.post('/admin/stock-counts', { preserveScroll: true })" />
        </section>
    </div>
</template>

