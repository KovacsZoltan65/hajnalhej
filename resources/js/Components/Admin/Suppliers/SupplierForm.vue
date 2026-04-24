<script setup>
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';

defineProps({
    form: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2 md:col-span-2">
            <label for="supplier-name" class="text-sm font-medium text-bakery-dark">Név</label>
            <InputText id="supplier-name" v-model="form.name" class="w-full" :invalid="Boolean(form.errors.name)" />
            <p v-if="form.errors.name" class="text-xs text-red-700">{{ form.errors.name }}</p>
        </div>

        <div class="space-y-2">
            <label for="supplier-email" class="text-sm font-medium text-bakery-dark">Email</label>
            <InputText id="supplier-email" v-model="form.email" class="w-full" />
            <p v-if="form.errors.email" class="text-xs text-red-700">{{ form.errors.email }}</p>
        </div>

        <div class="space-y-2">
            <label for="supplier-phone" class="text-sm font-medium text-bakery-dark">Telefonszám</label>
            <InputText id="supplier-phone" v-model="form.phone" class="w-full" />
            <p v-if="form.errors.phone" class="text-xs text-red-700">{{ form.errors.phone }}</p>
        </div>

        <div class="space-y-2">
            <label for="supplier-tax-number" class="text-sm font-medium text-bakery-dark">Adószám</label>
            <InputText id="supplier-tax-number" v-model="form.tax_number" class="w-full" />
            <p v-if="form.errors.tax_number" class="text-xs text-red-700">{{ form.errors.tax_number }}</p>
        </div>

        <div class="space-y-2 md:col-span-2">
            <label for="supplier-notes" class="text-sm font-medium text-bakery-dark">Megjegyzés</label>
            <Textarea id="supplier-notes" v-model="form.notes" rows="4" class="w-full" auto-resize />
            <p v-if="form.errors.notes" class="text-xs text-red-700">{{ form.errors.notes }}</p>
        </div>
    </div>
</template>

