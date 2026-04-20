<script setup>
import { watch } from 'vue';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';
import { slugify } from '../../../Utils/slugify';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
});

watch(
    () => props.form.name,
    (name) => {
        props.form.slug = slugify(name);
    },
);
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2 md:col-span-2">
            <label for="category-name" class="text-sm font-medium text-bakery-dark">Nev</label>
            <InputText id="category-name" v-model="form.name" class="w-full" :invalid="Boolean(form.errors.name)" />
            <p v-if="form.errors.name" class="text-xs text-red-700">{{ form.errors.name }}</p>
        </div>

        <div class="space-y-2 md:col-span-2">
            <label for="category-slug" class="text-sm font-medium text-bakery-dark">Slug</label>
            <InputText id="category-slug" v-model="form.slug" class="w-full" disabled />
            <p class="text-xs text-bakery-dark/60">Automatikusan generalodik a nev alapjan.</p>
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
</template>
