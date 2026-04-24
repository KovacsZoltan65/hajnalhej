<script setup>
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import SupplierForm from './SupplierForm.vue';

defineProps({
    visible: {
        type: Boolean,
        required: true,
    },
    form: {
        type: Object,
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
        header="Beszállító szerkesztése"
        :style="{ width: '44rem', maxWidth: '97vw' }"
        :content-style="{ maxHeight: '70vh', overflowY: 'auto' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <form id="supplier-edit-form" class="space-y-4" @submit.prevent="emit('submit')">
            <SupplierForm :form="form" />
        </form>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button type="button" severity="secondary" label="Mégse" @click="close" />
                <Button type="submit" form="supplier-edit-form" label="Mentés" :loading="form.processing" />
            </div>
        </template>
    </Dialog>
</template>

