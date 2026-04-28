<script setup>
import { reactive, watch } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import WeeklyMenuItemForm from './WeeklyMenuItemForm.vue';

const props = defineProps({
    visible: { type: Boolean, required: true },
    item: { type: Object, default: null },
    products: { type: Array, required: true },
});

const emit = defineEmits(['update:visible', 'save']);

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

const fillForm = () => {
    const item = props.item;

    form.id = item?.id ?? null;
    form.product_id = item?.product_id ?? props.products[0]?.id ?? null;
    form.override_name = item?.override_name ?? '';
    form.override_short_description = item?.override_short_description ?? '';
    form.override_price = item?.override_price ?? null;
    form.sort_order = item?.sort_order ?? 0;
    form.is_active = item?.is_active ?? true;
    form.badge_text = item?.badge_text ?? '';
    form.stock_note = item?.stock_note ?? '';
};

watch(
    () => props.visible,
    (open) => {
        if (open) {
            fillForm();
        }
    },
);

watch(
    () => props.item,
    () => {
        if (props.visible) {
            fillForm();
        }
    },
);

const close = () => emit('update:visible', false);

const submit = () => {
    emit('save', {
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

    close();
};
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        header="Heti menü tétel szerkesztése"
        :style="{ width: '48rem', maxWidth: '96vw' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <form class="space-y-5" @submit.prevent="submit">
            <WeeklyMenuItemForm :form="form" :products="products" />

            <div class="flex justify-end gap-2">
                <Button type="button" severity="secondary" label="Mégse" @click="close" />
                <Button type="submit" label="Tétel frissítése" />
            </div>
        </form>
    </Dialog>
</template>
