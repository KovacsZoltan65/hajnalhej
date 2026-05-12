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

const normalizePath = (value) => {
    if (!value) {
        return "/";
    }

    let path = String(value);

    try {
        path = new URL(path, window.location.origin).pathname;
    } catch {
        path = path.split("#")[0].split("?")[0] || "/";
    }

    return path.length > 1 ? path.replace(/\/+$/, "") : path;
};

const isActive = (route) => {
    const currentPath = normalizePath(page.url);
    const routePath = normalizePath(route);

    return currentPath === routePath || currentPath.startsWith(`${routePath}/`);
};

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
