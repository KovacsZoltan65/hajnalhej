<script setup>
import Button from "primevue/button";
import Dialog from "primevue/dialog";

defineProps({
    visible: { type: Boolean, required: true },
    courier: { type: Object, default: null },
    processing: { type: Boolean, default: false },
});

const emit = defineEmits(["update:visible", "confirm"]);
const close = () => emit("update:visible", false);
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :header="$t('admin_couriers.confirm_delete_header')"
        :style="{ width: '32rem', maxWidth: '95vw' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <p class="text-sm leading-6 text-bakery-dark/75">
            {{ $t("admin_couriers.confirm_delete_message", { name: courier?.name ?? "" }) }}
        </p>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button type="button" severity="secondary" :label="$t('common.cancel')" @click="close" />
                <Button
                    type="button"
                    severity="danger"
                    :label="$t('common.delete')"
                    :loading="processing"
                    @click="emit('confirm')"
                />
            </div>
        </template>
    </Dialog>
</template>
