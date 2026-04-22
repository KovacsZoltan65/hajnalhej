<script setup>
import { watch } from 'vue';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';
import { slugify } from '../../../Utils/slugify';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    stockStatuses: {
        type: Array,
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
        <div class="space-y-2">
            <label for="product-category" class="text-sm font-medium text-bakery-dark">Kategoria</label>
            <Select
                id="product-category"
                v-model="form.category_id"
                :options="categories"
                option-label="name"
                option-value="id"
                placeholder="Válassz kategóriát"
                class="w-full"
            />
            <p v-if="form.errors.category_id" class="text-xs text-red-700">{{ form.errors.category_id }}</p>
        </div>

        <div class="space-y-2">
            <label for="product-price" class="text-sm font-medium text-bakery-dark">Ar (Ft)</label>
            <InputNumber id="product-price" v-model="form.price" mode="decimal" :min="0" :min-fraction-digits="2" :max-fraction-digits="2" fluid />
            <p v-if="form.errors.price" class="text-xs text-red-700">{{ form.errors.price }}</p>
        </div>

        <div class="space-y-2 md:col-span-2">
            <label for="product-name" class="text-sm font-medium text-bakery-dark">Név</label>
            <InputText id="product-name" v-model="form.name" class="w-full" :invalid="Boolean(form.errors.name)" />
            <p v-if="form.errors.name" class="text-xs text-red-700">{{ form.errors.name }}</p>
        </div>

        <div class="space-y-2 md:col-span-2">
            <label for="product-slug" class="text-sm font-medium text-bakery-dark">Slug</label>
            <InputText id="product-slug" v-model="form.slug" class="w-full" disabled />
            <p class="text-xs text-bakery-dark/60">Automatikusan generalodik a nev alapjan.</p>
            <p v-if="form.errors.slug" class="text-xs text-red-700">{{ form.errors.slug }}</p>
        </div>

        <div class="space-y-2 md:col-span-2">
            <label for="product-short-description" class="text-sm font-medium text-bakery-dark">Rovid leiras</label>
            <InputText id="product-short-description" v-model="form.short_description" class="w-full" />
            <p v-if="form.errors.short_description" class="text-xs text-red-700">{{ form.errors.short_description }}</p>
        </div>

        <div class="space-y-2 md:col-span-2">
            <label for="product-description" class="text-sm font-medium text-bakery-dark">Leírás</label>
            <Textarea id="product-description" v-model="form.description" rows="4" class="w-full" auto-resize />
            <p v-if="form.errors.description" class="text-xs text-red-700">{{ form.errors.description }}</p>
        </div>

        <div class="space-y-2">
            <label for="product-stock-status" class="text-sm font-medium text-bakery-dark">Keszlet allapot</label>
            <Select
                id="product-stock-status"
                v-model="form.stock_status"
                :options="stockStatuses"
                option-label="label"
                option-value="value"
                class="w-full"
            />
            <p v-if="form.errors.stock_status" class="text-xs text-red-700">{{ form.errors.stock_status }}</p>
        </div>

        <div class="space-y-2">
            <label for="product-sort-order" class="text-sm font-medium text-bakery-dark">Sorrend</label>
            <InputNumber id="product-sort-order" v-model="form.sort_order" :min="0" fluid />
            <p v-if="form.errors.sort_order" class="text-xs text-red-700">{{ form.errors.sort_order }}</p>
        </div>

        <div class="space-y-2 md:col-span-2">
            <label for="product-image-path" class="text-sm font-medium text-bakery-dark">Kep utvonala</label>
            <InputText id="product-image-path" v-model="form.image_path" class="w-full" />
            <p v-if="form.errors.image_path" class="text-xs text-red-700">{{ form.errors.image_path }}</p>
        </div>

        <div class="flex items-center gap-2">
            <ToggleSwitch id="product-active" v-model="form.is_active" />
            <label for="product-active" class="text-sm text-bakery-dark/80">Aktív</label>
        </div>

        <div class="flex items-center gap-2">
            <ToggleSwitch id="product-featured" v-model="form.is_featured" />
            <label for="product-featured" class="text-sm text-bakery-dark/80">Kiemelt</label>
        </div>
    </div>
</template>


