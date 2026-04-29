<script setup>
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";

const props = defineProps({
    visible: {
        type: Boolean,
        required: true,
    },
    form: {
        type: Object,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    submitLabel: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(["update:visible", "submit"]);

const close = () => emit("update:visible", false);
</script>

<template>
    <Dialog
        :visible="props.visible"
        modal
        :header="props.title"
        :style="{ width: '32rem', maxWidth: '96vw' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <form id="role-form" class="space-y-4" @submit.prevent="emit('submit')">
            <div class="space-y-2">
                <label for="role-name" class="text-sm font-medium text-bakery-dark"
                    >Szerepkör neve</label
                >
                <InputText
                    id="role-name"
                    v-model="props.form.name"
                    class="w-full"
                    :invalid="Boolean(props.form.errors.name)"
                    placeholder="pl. bakery-manager"
                />
                <p class="text-xs text-bakery-dark/65">
                    Kisbetü, szám, pont, kötőjel vagy aláhúzás használható.
                </p>
                <p v-if="props.form.errors.name" class="text-xs text-red-700">
                    {{ props.form.errors.name }}
                </p>
            </div>
        </form>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button type="button" severity="secondary" label="Mégse" @click="close" />
                <Button
                    type="submit"
                    form="role-form"
                    :label="props.submitLabel"
                    :loading="props.form.processing"
                    :disabled="props.form.processing"
                />
            </div>
        </template>
    </Dialog>
</template>
