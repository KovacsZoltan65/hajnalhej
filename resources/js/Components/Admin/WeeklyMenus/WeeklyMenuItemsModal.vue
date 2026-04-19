<script setup>
import { reactive, watch } from 'vue';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import ToggleSwitch from 'primevue/toggleswitch';
import WeeklyMenuStatusBadge from './WeeklyMenuStatusBadge.vue';

const props = defineProps({
    visible: { type: Boolean, required: true },
    menu: { type: Object, default: null },
    products: { type: Array, required: true },
});

const emit = defineEmits(['update:visible', 'save-item', 'delete-item']);

const form = reactive({
    id: null,
    product_id: null,
    override_name: '',
    override_short_description: '',
    override_price: null,
    sort_order: 0,
    is_active: true,
    badge_text: '',
    stock_note: '',
});

const resetForm = () => {
    form.id = null;
    form.product_id = props.products[0]?.id ?? null;
    form.override_name = '';
    form.override_short_description = '';
    form.override_price = null;
    form.sort_order = 0;
    form.is_active = true;
    form.badge_text = '';
    form.stock_note = '';
};

watch(
    () => props.visible,
    (open) => {
        if (open) {
            resetForm();
        }
    },
);

const editItem = (item) => {
    form.id = item.id;
    form.product_id = item.product_id;
    form.override_name = item.override_name ?? '';
    form.override_short_description = item.override_short_description ?? '';
    form.override_price = item.override_price;
    form.sort_order = item.sort_order;
    form.is_active = item.is_active;
    form.badge_text = item.badge_text ?? '';
    form.stock_note = item.stock_note ?? '';
};

const submit = () => {
    emit('save-item', {
        id: form.id,
        product_id: form.product_id,
        override_name: form.override_name || null,
        override_short_description: form.override_short_description || null,
        override_price: form.override_price,
        sort_order: form.sort_order ?? 0,
        is_active: form.is_active,
        badge_text: form.badge_text || null,
        stock_note: form.stock_note || null,
    });

    resetForm();
};

const remove = (item) => emit('delete-item', item);
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :header="menu ? `Tetelkezeles: ${menu.title}` : 'Tetelkezeles'"
        :style="{ width: '70rem', maxWidth: '98vw' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <div class="space-y-6">
            <form class="grid gap-3 rounded-2xl border border-bakery-brown/15 bg-[#fcf7ef] p-4 md:grid-cols-3" @submit.prevent="submit">
                <Select
                    v-model="form.product_id"
                    :options="products"
                    option-label="name"
                    option-value="id"
                    placeholder="Termek"
                    class="md:col-span-3"
                    filter
                >
                    <template #option="slotProps">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-medium">{{ slotProps.option.name }}</p>
                                <p class="text-xs text-bakery-dark/70">{{ slotProps.option.category_name ?? 'Kategoria nelkul' }}</p>
                            </div>
                            <span class="text-xs text-bakery-brown">{{ new Intl.NumberFormat('hu-HU').format(slotProps.option.price) }} Ft</span>
                        </div>
                    </template>
                </Select>

                <InputText v-model="form.override_name" placeholder="Override nev (opcionalis)" class="md:col-span-2" />
                <InputNumber v-model="form.override_price" mode="decimal" :min="0" :min-fraction-digits="2" :max-fraction-digits="2" placeholder="Override ar" fluid />

                <InputText v-model="form.override_short_description" placeholder="Override rovid leiras" class="md:col-span-2" />
                <InputNumber v-model="form.sort_order" :min="0" placeholder="Sorrend" fluid />

                <InputText v-model="form.badge_text" placeholder="Badge" />
                <InputText v-model="form.stock_note" placeholder="Keszlet megjegyzes" />

                <div class="flex items-center gap-2">
                    <ToggleSwitch v-model="form.is_active" />
                    <span class="text-sm">Aktiv</span>
                </div>

                <div class="md:col-span-3 flex justify-end gap-2">
                    <Button type="button" severity="secondary" label="Uj" @click="resetForm" />
                    <Button type="submit" :label="form.id ? 'Tetel frissitese' : 'Tetel hozzaadasa'" />
                </div>
            </form>

            <DataTable :value="menu?.items ?? []" data-key="id">
                <template #empty>
                    <div class="p-4 text-sm text-bakery-dark/70">Nincs tetel a heti menuhoz.</div>
                </template>

                <Column field="product_name" header="Termek" />
                <Column field="override_name" header="Override nev" />
                <Column field="override_price" header="Override ar">
                    <template #body="{ data }">
                        <span v-if="data.override_price !== null">{{ new Intl.NumberFormat('hu-HU').format(data.override_price) }} Ft</span>
                        <span v-else class="text-bakery-dark/60">-</span>
                    </template>
                </Column>
                <Column field="sort_order" header="Sorrend" />
                <Column field="is_active" header="Statusz">
                    <template #body="{ data }">
                        <WeeklyMenuStatusBadge :status="data.is_active ? 'published' : 'draft'" />
                    </template>
                </Column>
                <Column header="Muveletek">
                    <template #body="{ data }">
                        <div class="flex items-center gap-2">
                            <Button icon="pi pi-pencil" size="small" text rounded @click="editItem(data)" />
                            <Button icon="pi pi-trash" size="small" text rounded severity="danger" @click="remove(data)" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </Dialog>
</template>
