<script setup>
import { Head } from "@inertiajs/vue3";
import ProductWizard from "@/Components/Admin/ProductFlow/ProductWizard.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineOptions({ layout: AdminLayout });

defineProps({
    categories: { type: Array, required: true },
    ingredients: { type: Array, required: true },
    stockStatuses: { type: Array, required: true },
});
</script>

<template>
    <Head :title="$t('admin.products.flow.title')" />
    <div class="space-y-6">
        <SectionTitle
            :eyebrow="$t('admin.products.flow.eyebrow')"
            :title="$t('admin.products.flow.title')"
            :description="$t('admin.products.flow.description')"
        />
        <ProductWizard :categories="categories" :ingredients="ingredients" :stock-statuses="stockStatuses" />
    </div>
</template>
