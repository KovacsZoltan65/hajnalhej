<script setup>
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import AdjustmentForm from "./AdjustmentForm.vue";

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
});

const emit = defineEmits(["update:visible", "submit"]);

const close = () => emit("update:visible", false);
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :header="$t('admin_inventory.stock_correction')"
        :style="{ width: '38rem', maxWidth: '97vw' }"
        :content-style="{ maxHeight: '70vh', overflowY: 'auto' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <form id="inventory-adjustment-form" class="space-y-4" @submit.prevent="emit('submit')">
            <AdjustmentForm :form="form" :ingredient-options="ingredientOptions" />
        </form>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button type="button" severity="secondary" :label="$t('common.cancel')" @click="close" />
                <Button
                    type="submit"
                    form="inventory-adjustment-form"
                    :label="$t('common.accounting')"
                    :loading="form.processing"
                />
            </div>
        </template>
    </Dialog>
</template>
