<script setup>
import { ref } from 'vue';
import SidebarItem from './SidebarItem.vue';

const props = defineProps({
    group: {
        type: Object,
        required: true,
    },
});

const collapsed = ref(false);
const toggle = () => {
    if (props.group.collapsible) {
        collapsed.value = !collapsed.value;
    }
};
</script>

<template>
    <section class="mt-5 first:mt-0">
        <button
            v-if="group.collapsible"
            type="button"
            class="flex w-full items-center justify-between px-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-bakery-brown/55"
            @click="toggle"
        >
            <span class="truncate">{{ group.label }}</span>
            <i
                class="pi pi-chevron-down text-[0.65rem] transition"
                :class="{ '-rotate-90': collapsed }"
                aria-hidden="true"
            />
        </button>

        <p
            v-else
            class="px-3 text-xs font-semibold uppercase tracking-[0.16em] text-bakery-brown/55"
        >
            {{ group.label }}
        </p>

        <div v-show="!collapsed" class="mt-2 space-y-1">
            <SidebarItem
                v-for="item in group.items"
                :key="item.route"
                :item="item"
            />
        </div>
    </section>
</template>
