<script setup>
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import ProductForm from './ProductForm.vue';

defineProps({
    visible: {
        type: Boolean,
        required: true,
    },
    form: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    stockStatuses: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['update:visible', 'submit']);

const close = () => emit('update:visible', false);
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        header="Termek szerkesztese"
        :style="{ width: '52rem', maxWidth: '97vw' }"
        :content-style="{ maxHeight: '70vh', overflowY: 'auto' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <form id="product-edit-form" class="space-y-4" @submit.prevent="emit('submit')">
            <ProductForm :form="form" :categories="categories" :stock-statuses="stockStatuses" />
        </form>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button type="button" severity="secondary" label="Megse" @click="close" />
                <Button type="submit" form="product-edit-form" label="Mentes" :loading="form.processing" />
            </div>
        </template>
    </Dialog>
</template>
