<script setup>
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import TermForm from './TermForm.vue';

defineProps({
    visible: { type: Boolean, required: true },
    form: { type: Object, required: true },
    ingredients: { type: Array, required: true },
    suppliers: { type: Array, required: true },
});

const emit = defineEmits(['update:visible', 'submit']);
const close = () => emit('update:visible', false);
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        header="Új beszállítói feltétel"
        :style="{ width: '48rem', maxWidth: '97vw' }"
        :content-style="{ maxHeight: '72vh', overflowY: 'auto' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <form id="supplier-term-create-form" class="space-y-4" @submit.prevent="emit('submit')">
            <TermForm :form="form" :ingredients="ingredients" :suppliers="suppliers" />
        </form>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button type="button" severity="secondary" label="Mégse" @click="close" />
                <Button type="submit" form="supplier-term-create-form" label="Létrehozás" :loading="form.processing" />
            </div>
        </template>
    </Dialog>
</template>
