<script setup>
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import WasteEntryForm from './WasteEntryForm.vue';

defineProps({
    visible: {
        type: Boolean,
        required: true,
    },
    form: {
        type: Object,
        required: true,
    },
    ingredientOptions: {
        type: Array,
        required: true,
    },
    productOptions: {
        type: Array,
        required: true,
    },
    wasteReasons: {
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
        header="Selejt rögzítése"
        :style="{ width: '36rem', maxWidth: '97vw' }"
        :content-style="{ maxHeight: '70vh', overflowY: 'auto' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <form id="inventory-waste-form" class="space-y-4" @submit.prevent="emit('submit')">
            <WasteEntryForm
                :form="form"
                :ingredient-options="ingredientOptions"
                :product-options="productOptions"
                :waste-reasons="wasteReasons"
            />
        </form>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button type="button" severity="secondary" label="Mégse" @click="close" />
                <Button type="submit" form="inventory-waste-form" label="Könyvelés" :loading="form.processing" />
            </div>
        </template>
    </Dialog>
</template>
