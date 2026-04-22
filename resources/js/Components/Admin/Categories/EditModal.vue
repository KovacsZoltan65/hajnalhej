<script setup>
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import CategoryForm from './CategoryForm.vue';

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
        header="Kategoria szerkesztese"
        :style="{ width: '40rem', maxWidth: '95vw' }"
        :content-style="{ maxHeight: '70vh', overflowY: 'auto' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <form id="category-edit-form" class="space-y-4" @submit.prevent="emit('submit')">
            <CategoryForm :form="form" />
        </form>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button type="button" severity="secondary" label="Mégse" @click="close" />
                <Button type="submit" form="category-edit-form" label="Mentés" :loading="form.processing" />
            </div>
        </template>
    </Dialog>
</template>

