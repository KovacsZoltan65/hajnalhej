<script setup>
import { usePage } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { watch } from 'vue';

const toast = useToast();
const page = usePage();

watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) {
            return;
        }

        if (flash.success) {
            toast.add({ severity: 'success', summary: 'Siker', detail: flash.success, life: 3000 });
        }

        if (flash.error) {
            toast.add({ severity: 'error', summary: 'Hiba', detail: flash.error, life: 3500 });
        }

        if (flash.info) {
            toast.add({ severity: 'info', summary: 'Informacio', detail: flash.info, life: 3000 });
        }
    },
    { deep: true, immediate: true },
);
</script>

<template>
    <Toast position="top-right" />
</template>
