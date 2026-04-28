<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SidebarGroup from './SidebarGroup.vue';

const page = usePage();

const props = defineProps({
    groups: {
        type: Array,
        required: true,
    },
});

const isActive = (route) => page.url === route || page.url.startsWith(`${route}/`);

const visibleGroups = computed(() => {
    return props.groups
        .map((group) => ({
            ...group,
            items: (group.items ?? [])
                .filter((item) => item && item.route)
                .map((item) => ({
                    ...item,
                    active: item.active ?? isActive(item.route),
                })),
        }))
        .filter((group) => group.items.length > 0);
});
</script>

<template>
    <nav
        aria-label="Admin menü"
        class="flex min-h-0 w-full flex-col overflow-y-auto pr-1"
    >
        <SidebarGroup
            v-for="group in visibleGroups"
            :key="group.label"
            :group="group"
        />
    </nav>
</template>
