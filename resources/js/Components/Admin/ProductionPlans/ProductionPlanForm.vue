<script setup>
import { computed } from 'vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';

const props = defineProps({
    form: { type: Object, required: true },
    products: { type: Array, required: true },
    statuses: { type: Array, required: true },
    mode: { type: String, default: 'create' },
});

const canRemoveRows = computed(() => (props.form.items?.length ?? 0) > 1);

const addItemRow = () => {
    const firstProductId = props.products[0]?.id ?? null;

    props.form.items.push({
        product_id: firstProductId,
        target_quantity: 1,
        unit_label: 'db',
        sort_order: props.form.items.length,
    });
};

const removeItemRow = (index) => {
    if ((props.form.items?.length ?? 0) <= 1) {
        return;
    }

    props.form.items.splice(index, 1);
};
</script>

<template>
    <div class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
                <label class="text-sm font-medium text-bakery-dark">Kesz legyen ekkor</label>
                <InputText v-model="form.target_ready_at" type="datetime-local" class="w-full" />
                <p v-if="form.errors.target_ready_at" class="text-xs text-red-700">{{ form.errors.target_ready_at }}</p>
                <p v-if="form.errors.target_at" class="text-xs text-red-700">{{ form.errors.target_at }}</p>
            </div>

            <div v-if="mode === 'edit'" class="space-y-2">
                <label class="text-sm font-medium text-bakery-dark">Statusz</label>
                <Select v-model="form.status" :options="statuses" option-label="label" option-value="value" class="w-full" />
                <p v-if="form.errors.status" class="text-xs text-red-700">{{ form.errors.status }}</p>
            </div>

            <div v-if="mode === 'edit'" class="flex items-center gap-2 pt-7">
                <ToggleSwitch v-model="form.is_locked" />
                <label class="text-sm text-bakery-dark/80">Terv lezarasa</label>
            </div>

            <div class="space-y-2 md:col-span-2">
                <label class="text-sm font-medium text-bakery-dark">Megjegyzes</label>
                <Textarea v-model="form.notes" rows="3" auto-resize class="w-full" />
                <p v-if="form.errors.notes" class="text-xs text-red-700">{{ form.errors.notes }}</p>
            </div>
        </div>

        <div class="space-y-3 rounded-xl border border-bakery-brown/15 bg-[#fcf8f1] p-4">
            <div class="flex items-center justify-between gap-2">
                <h4 class="text-sm font-semibold text-bakery-dark">Termekek es mennyisegek</h4>
                <Button type="button" icon="pi pi-plus" label="Uj tetel" size="small" @click="addItemRow" />
            </div>

            <div
                v-for="(item, index) in form.items"
                :key="`plan-item-${index}`"
                class="grid gap-3 rounded-lg border border-bakery-brown/10 bg-white p-3 md:grid-cols-[minmax(0,1fr)_8rem_6rem_5rem_auto]"
            >
                <div class="space-y-1">
                    <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Termek</label>
                    <Select v-model="item.product_id" :options="products" option-label="name" option-value="id" class="w-full" />
                    <p v-if="form.errors[`items.${index}.product_id`]" class="text-xs text-red-700">{{ form.errors[`items.${index}.product_id`] }}</p>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Mennyiseg</label>
                    <InputText v-model="item.target_quantity" type="number" min="0.001" step="0.001" class="w-full" />
                    <p v-if="form.errors[`items.${index}.target_quantity`]" class="text-xs text-red-700">{{ form.errors[`items.${index}.target_quantity`] }}</p>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Egyseg</label>
                    <InputText v-model="item.unit_label" class="w-full" />
                    <p v-if="form.errors[`items.${index}.unit_label`]" class="text-xs text-red-700">{{ form.errors[`items.${index}.unit_label`] }}</p>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">Sorrend</label>
                    <InputText v-model="item.sort_order" type="number" min="0" step="1" class="w-full" />
                </div>

                <div class="flex items-end justify-end">
                    <Button
                        type="button"
                        icon="pi pi-trash"
                        size="small"
                        text
                        rounded
                        severity="danger"
                        :disabled="!canRemoveRows"
                        @click="removeItemRow(index)"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
