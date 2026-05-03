<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const map = {
    draft: { labelKey: 'admin_weekly_menus.status.draft', className: 'bg-stone-200 text-stone-700' },
    published: { labelKey: 'admin_weekly_menus.status.published', className: 'bg-green-100 text-green-800' },
    archived: { labelKey: 'admin_weekly_menus.status.archived', className: 'bg-amber-100 text-amber-800' },
};

const config = computed(() => map[props.status] ?? map.draft);
</script>

<template>
    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="config.className">{{ $t(config.labelKey) }}</span>
</template>
