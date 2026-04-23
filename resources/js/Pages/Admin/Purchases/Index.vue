<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import SectionTitle from '@/Components/SectionTitle.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    purchases: { type: Object, required: true },
    suppliers: { type: Array, required: true },
    ingredient_options: { type: Array, required: true },
    statuses: { type: Array, required: true },
    filters: { type: Object, required: true },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const supplierId = ref(props.filters.supplier_id ?? '');

const newItem = () => ({ ingredient_id: null, quantity: 1, unit: 'db', unit_cost: 0 });
const form = useForm({
    supplier_id: null,
    reference_number: '',
    purchase_date: new Date().toISOString().slice(0, 10),
    notes: '',
    items: [newItem()],
});

const supplierOptions = computed(() => [{ label: 'Mind', value: '' }, ...props.suppliers.map((s) => ({ label: s.name, value: s.id }))]);
const statusOptions = computed(() => [{ label: 'Mind', value: '' }, ...props.statuses.map((s) => ({ label: s, value: s }))]);
const ingredientOptions = computed(() => props.ingredient_options.map((i) => ({ label: `${i.name} (${i.unit})`, value: i.id, unit: i.unit })));

const load = () => {
    router.get('/admin/purchases', {
        search: search.value || undefined,
        status: status.value || undefined,
        supplier_id: supplierId.value || undefined,
    }, { preserveState: true, replace: true, preserveScroll: true });
};

const addItem = () => form.items.push(newItem());
const removeItem = (idx) => form.items.splice(idx, 1);

const onIngredientChange = (idx) => {
    const selected = ingredientOptions.value.find((o) => o.value === form.items[idx].ingredient_id);
    if (selected) {
        form.items[idx].unit = selected.unit;
    }
};

const total = computed(() => form.items.reduce((sum, item) => sum + (Number(item.quantity || 0) * Number(item.unit_cost || 0)), 0));

const postPurchase = () => {
    form.post('/admin/purchases', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const postNow = (id) => router.post(`/admin/purchases/${id}/post`, {}, { preserveScroll: true });
const cancelPurchase = (id) => router.post(`/admin/purchases/${id}/cancel`, {}, { preserveScroll: true });
</script>

<template>
    <Head title="Beszerzések" />

    <div class="space-y-6">
        <SectionTitle
            eyebrow="Admin / Beszerzések"
            title="Beszerzések"
            description="Bevételezésre kész beszerzési dokumentumok valós készletkönyveléssel."
        />

        <section class="ui-card p-4 sm:p-5 space-y-4">
            <div class="grid gap-3 md:grid-cols-4">
                <InputText v-model="search" placeholder="Keresés referencia / megjegyzés" @keyup.enter="load" />
                <Select v-model="status" :options="statusOptions" option-label="label" option-value="value" />
                <Select v-model="supplierId" :options="supplierOptions" option-label="label" option-value="value" />
                <Button label="Szűrés" class="!min-h-11" @click="load" />
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                        <tr>
                            <th class="px-2 py-2">Dátum</th>
                            <th class="px-2 py-2">Referencia</th>
                            <th class="px-2 py-2">Beszállító</th>
                            <th class="px-2 py-2">Státusz</th>
                            <th class="px-2 py-2 text-right">Összesen</th>
                            <th class="px-2 py-2 text-right">Művelet</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="purchase in purchases.data" :key="purchase.id" class="border-b border-bakery-brown/10">
                            <td class="px-2 py-2">{{ purchase.purchase_date }}</td>
                            <td class="px-2 py-2 font-medium">{{ purchase.reference_number || '-' }}</td>
                            <td class="px-2 py-2">{{ purchase.supplier_name || '-' }}</td>
                            <td class="px-2 py-2">
                                <span class="rounded-full px-2 py-1 text-xs bg-bakery-brown/10 text-bakery-brown">{{ purchase.status }}</span>
                            </td>
                            <td class="px-2 py-2 text-right">{{ new Intl.NumberFormat('hu-HU').format(purchase.total) }} Ft</td>
                            <td class="px-2 py-2">
                                <div class="flex justify-end gap-2">
                                    <Link :href="`/admin/purchases/${purchase.id}`" class="text-sm underline">Részletek</Link>
                                    <Button v-if="purchase.status === 'draft'" label="Könyvelés" size="small" class="!min-h-11" @click="postNow(purchase.id)" />
                                    <Button v-if="purchase.status === 'draft'" label="Stornó" size="small" severity="danger" text class="!min-h-11" @click="cancelPurchase(purchase.id)" />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ui-card p-4 sm:p-5 space-y-4">
            <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">Új beszerzés</h2>
            <div class="grid gap-3 md:grid-cols-2">
                <Select v-model="form.supplier_id" :options="props.suppliers" option-label="name" option-value="id" placeholder="Beszállító" />
                <InputText v-model="form.reference_number" placeholder="Referencia szám" />
                <InputText v-model="form.purchase_date" type="date" />
                <InputText v-model="form.notes" placeholder="Megjegyzés" />
            </div>

            <div class="space-y-2">
                <div v-for="(item, idx) in form.items" :key="idx" class="grid gap-2 md:grid-cols-5 items-center">
                    <Select v-model="item.ingredient_id" :options="ingredientOptions" option-label="label" option-value="value" placeholder="Alapanyag" @update:model-value="onIngredientChange(idx)" />
                    <InputText v-model="item.quantity" type="number" min="0.001" step="0.001" placeholder="Mennyiség" />
                    <InputText v-model="item.unit" placeholder="Egység" />
                    <InputText v-model="item.unit_cost" type="number" min="0" step="0.0001" placeholder="Egységár" />
                    <div class="flex justify-end">
                        <Button label="Sor törlés" text severity="danger" class="!min-h-11" @click="removeItem(idx)" />
                    </div>
                </div>
                <Button label="Tétel hozzáadása" outlined class="!min-h-11" @click="addItem" />
            </div>

            <div class="flex items-center justify-between">
                <p class="text-sm text-bakery-dark/80">Összesen: <strong>{{ new Intl.NumberFormat('hu-HU').format(total) }} Ft</strong></p>
                <Button label="Beszerzés mentése (draft)" class="!min-h-11" :disabled="form.processing" @click="postPurchase" />
            </div>
        </section>
    </div>
</template>

