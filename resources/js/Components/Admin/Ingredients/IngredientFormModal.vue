<script setup>
import { watch } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
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
    units: {
        type: Array,
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
        :header="mode === 'create' ? 'Uj alapanyag' : 'Alapanyag szerkesztese'"
        :style="{ width: '50rem', maxWidth: '97vw' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <form class="space-y-4" @submit.prevent="emit('submit')">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2 md:col-span-2">
                    <label for="ingredient-name" class="text-sm font-medium text-bakery-dark">Nev</label>
                    <InputText id="ingredient-name" v-model="form.name" class="w-full" :invalid="Boolean(form.errors.name)" />
                    <p v-if="form.errors.name" class="text-xs text-red-700">{{ form.errors.name }}</p>
                </div>

                <div class="space-y-2">
                    <label for="ingredient-slug" class="text-sm font-medium text-bakery-dark">Slug</label>
                    <InputText id="ingredient-slug" v-model="form.slug" class="w-full" :invalid="Boolean(form.errors.slug)" />
                    <p v-if="form.errors.slug" class="text-xs text-red-700">{{ form.errors.slug }}</p>
                </div>

                <div class="space-y-2">
                    <label for="ingredient-sku" class="text-sm font-medium text-bakery-dark">SKU</label>
                    <InputText id="ingredient-sku" v-model="form.sku" class="w-full" :invalid="Boolean(form.errors.sku)" />
                    <p v-if="form.errors.sku" class="text-xs text-red-700">{{ form.errors.sku }}</p>
                </div>

                <div class="space-y-2">
                    <label for="ingredient-unit" class="text-sm font-medium text-bakery-dark">Mertekegyseg</label>
                    <Select
                        id="ingredient-unit"
                        v-model="form.unit"
                        :options="units.map((unit) => ({ label: unit, value: unit }))"
                        option-label="label"
                        option-value="value"
                        class="w-full"
                    />
                    <p v-if="form.errors.unit" class="text-xs text-red-700">{{ form.errors.unit }}</p>
                </div>

                <div class="space-y-2">
                    <label for="ingredient-current-stock" class="text-sm font-medium text-bakery-dark">Aktualis keszlet</label>
                    <InputNumber
                        id="ingredient-current-stock"
                        v-model="form.current_stock"
                        mode="decimal"
                        :min="0"
                        :min-fraction-digits="0"
                        :max-fraction-digits="3"
                        fluid
                    />
                    <p v-if="form.errors.current_stock" class="text-xs text-red-700">{{ form.errors.current_stock }}</p>
                </div>

                <div class="space-y-2">
                    <label for="ingredient-minimum-stock" class="text-sm font-medium text-bakery-dark">Minimum keszlet</label>
                    <InputNumber
                        id="ingredient-minimum-stock"
                        v-model="form.minimum_stock"
                        mode="decimal"
                        :min="0"
                        :min-fraction-digits="0"
                        :max-fraction-digits="3"
                        fluid
                    />
                    <p v-if="form.errors.minimum_stock" class="text-xs text-red-700">{{ form.errors.minimum_stock }}</p>
                </div>

                <div class="space-y-2 md:col-span-2">
                    <label for="ingredient-notes" class="text-sm font-medium text-bakery-dark">Megjegyzes</label>
                    <Textarea id="ingredient-notes" v-model="form.notes" rows="4" auto-resize class="w-full" />
                    <p v-if="form.errors.notes" class="text-xs text-red-700">{{ form.errors.notes }}</p>
                </div>

                <div class="flex items-center gap-2 md:col-span-2">
                    <ToggleSwitch id="ingredient-active" v-model="form.is_active" />
                    <label for="ingredient-active" class="text-sm text-bakery-dark/80">Aktiv alapanyag</label>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-bakery-brown/10 pt-4">
                <Button type="button" severity="secondary" label="Megse" @click="close" />
                <Button type="submit" :label="mode === 'create' ? 'Letrehozas' : 'Mentes'" :loading="form.processing" />
            </div>
        </form>
    </Dialog>
</template>
