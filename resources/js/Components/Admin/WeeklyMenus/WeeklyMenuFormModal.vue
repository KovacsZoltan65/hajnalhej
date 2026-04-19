<script setup>
import { watch } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';
import { slugify } from '../../../Utils/slugify';

const props = defineProps({
    visible: { type: Boolean, required: true },
    mode: { type: String, required: true },
    form: { type: Object, required: true },
    statuses: { type: Array, required: true },
});

const emit = defineEmits(['update:visible', 'submit']);

watch(
    () => props.form.title,
    (title) => {
        if (!props.visible || props.form.slug.length > 0) {
            return;
        }

        props.form.slug = slugify(title);
    },
);

const close = () => emit('update:visible', false);
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :header="mode === 'create' ? 'Uj heti menu' : 'Heti menu szerkesztese'"
        :style="{ width: '44rem', maxWidth: '96vw' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <form class="space-y-4" @submit.prevent="emit('submit')">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-medium text-bakery-dark">Cim</label>
                    <InputText v-model="form.title" class="w-full" :invalid="Boolean(form.errors.title)" />
                    <p v-if="form.errors.title" class="text-xs text-red-700">{{ form.errors.title }}</p>
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-medium text-bakery-dark">Slug</label>
                    <InputText v-model="form.slug" class="w-full" :invalid="Boolean(form.errors.slug)" />
                    <p v-if="form.errors.slug" class="text-xs text-red-700">{{ form.errors.slug }}</p>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-bakery-dark">Het kezdete</label>
                    <InputText v-model="form.week_start" type="date" class="w-full" />
                    <p v-if="form.errors.week_start" class="text-xs text-red-700">{{ form.errors.week_start }}</p>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-bakery-dark">Het vege</label>
                    <InputText v-model="form.week_end" type="date" class="w-full" />
                    <p v-if="form.errors.week_end" class="text-xs text-red-700">{{ form.errors.week_end }}</p>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-bakery-dark">Statusz</label>
                    <Select v-model="form.status" :options="statuses" option-label="label" option-value="value" class="w-full" />
                </div>

                <div class="flex items-center gap-2 pt-7">
                    <ToggleSwitch v-model="form.is_featured" />
                    <label class="text-sm text-bakery-dark/80">Kiemelt menu</label>
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-medium text-bakery-dark">Public megjegyzes</label>
                    <Textarea v-model="form.public_note" rows="3" class="w-full" auto-resize />
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-medium text-bakery-dark">Internal megjegyzes</label>
                    <Textarea v-model="form.internal_note" rows="3" class="w-full" auto-resize />
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-bakery-brown/10 pt-4">
                <Button type="button" severity="secondary" label="Megse" @click="close" />
                <Button type="submit" :label="mode === 'create' ? 'Letrehozas' : 'Mentes'" :loading="form.processing" />
            </div>
        </form>
    </Dialog>
</template>
