<script setup>
import { watch } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';
import { slugify } from '../../../Utils/slugify';

const props = defineProps({
    visible: {
        type: Boolean,
        required: true,
    },
    mode: {
        type: String,
        required: true,
    },
    form: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['update:visible', 'submit']);

watch(
    () => props.form.name,
    (name) => {
        if (!props.visible || props.form.slug.length > 0) {
            return;
        }

        props.form.slug = slugify(name);
    },
);

const close = () => emit('update:visible', false);
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :header="mode === 'create' ? 'Uj kategoria' : 'Kategoria szerkesztese'"
        :style="{ width: '40rem', maxWidth: '95vw' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <form class="space-y-4" @submit.prevent="emit('submit')">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2 md:col-span-2">
                    <label for="category-name" class="text-sm font-medium text-bakery-dark">Nev</label>
                    <InputText id="category-name" v-model="form.name" class="w-full" :invalid="Boolean(form.errors.name)" />
                    <p v-if="form.errors.name" class="text-xs text-red-700">{{ form.errors.name }}</p>
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label for="category-slug" class="text-sm font-medium text-bakery-dark">Slug</label>
                    <InputText id="category-slug" v-model="form.slug" class="w-full" :invalid="Boolean(form.errors.slug)" />
                    <p v-if="form.errors.slug" class="text-xs text-red-700">{{ form.errors.slug }}</p>
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label for="category-description" class="text-sm font-medium text-bakery-dark">Leiras</label>
                    <Textarea id="category-description" v-model="form.description" rows="4" class="w-full" auto-resize />
                    <p v-if="form.errors.description" class="text-xs text-red-700">{{ form.errors.description }}</p>
                </div>

                <div class="space-y-2">
                    <label for="category-sort-order" class="text-sm font-medium text-bakery-dark">Sorrend</label>
                    <InputNumber id="category-sort-order" v-model="form.sort_order" :min="0" fluid />
                    <p v-if="form.errors.sort_order" class="text-xs text-red-700">{{ form.errors.sort_order }}</p>
                </div>

                <div class="flex items-center gap-2 pt-7">
                    <ToggleSwitch id="category-active" v-model="form.is_active" />
                    <label for="category-active" class="text-sm text-bakery-dark/80">Aktiv kategoria</label>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-bakery-brown/10 pt-4">
                <Button type="button" severity="secondary" label="Megse" @click="close" />
                <Button type="submit" :label="mode === 'create' ? 'Letrehozas' : 'Mentes'" :loading="form.processing" />
            </div>
        </form>
    </Dialog>
</template>
