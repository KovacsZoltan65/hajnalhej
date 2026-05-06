<script setup>
import Button from "primevue/button";
import Menu from "primevue/menu";
import { ref } from "vue";

defineProps({
    items: { type: Array, required: true },
});

const menu = ref(null);
</script>

<template>
    <Button
        icon="pi pi-ellipsis-v"
        text
        rounded
        class="h-11! w-11!"
        :aria-label="$t('common.actions')"
        @click="menu.toggle($event)"
    />
    <Menu ref="menu" :model="items" popup />
</template>
