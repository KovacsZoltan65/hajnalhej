<script setup>
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import ProductionPlanForm from './ProductionPlanForm.vue';

defineProps({
    visible: { type: Boolean, required: true },
    form: { type: Object, required: true },
    products: { type: Array, required: true },
    statuses: { type: Array, required: true },
});

const emit = defineEmits(['update:visible', 'submit']);

const close = () => emit('update:visible', false);
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        header="Uj gyartasi terv"
        :style="{ width: '64rem', maxWidth: '97vw' }"
        :content-style="{ maxHeight: '70vh', overflowY: 'auto' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <form id="production-plan-create-form" class="space-y-4" @submit.prevent="emit('submit')">
            <ProductionPlanForm :form="form" :products="products" :statuses="statuses" mode="create" />
        </form>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button type="button" severity="secondary" label="Mégse" @click="close" />
                <Button type="submit" form="production-plan-create-form" label="Létrehozás" :loading="form.processing" />
            </div>
        </template>
    </Dialog>
</template>


