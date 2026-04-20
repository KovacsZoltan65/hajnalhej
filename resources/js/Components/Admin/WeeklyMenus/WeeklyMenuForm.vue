<script setup>
import { watch } from 'vue';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';
import { slugify } from '../../../Utils/slugify';

const props = defineProps({
    form: { type: Object, required: true },
    statuses: { type: Array, required: true },
});

watch(
    () => props.form.title,
    (title) => {
        props.form.slug = slugify(title);
    },
);
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2 md:col-span-2">
            <label class="text-sm font-medium text-bakery-dark">Cim</label>
            <InputText id="weekly-menu-title" v-model="form.title" class="w-full" :invalid="Boolean(form.errors.title)" />
            <p v-if="form.errors.title" class="text-xs text-red-700">{{ form.errors.title }}</p>
        </div>

        <div class="space-y-2 md:col-span-2">
            <label class="text-sm font-medium text-bakery-dark">Slug</label>
            <InputText id="weekly-menu-slug" v-model="form.slug" class="w-full" disabled />
            <p class="text-xs text-bakery-dark/60">Automatikusan generalodik a cim alapjan.</p>
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
</template>
