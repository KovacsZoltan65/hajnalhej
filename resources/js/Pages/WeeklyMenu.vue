<script setup>
import { Head } from '@inertiajs/vue3';
import WeeklyMenuSection from '../Components/Public/WeeklyMenuSection.vue';
import PublicLayout from '../Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

defineProps({
    menu: {
        type: Object,
        default: null,
    },
    groups: {
        type: Array,
        default: () => [],
    },
    fallback_used: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <Head title="Heti menu" />

    <WeeklyMenuSection :menu="menu" :groups="groups" :fallback-used="fallback_used" />
</template>
