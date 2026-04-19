<script setup>
const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const map = {
    draft: { label: 'Draft', className: 'bg-stone-200 text-stone-700' },
    published: { label: 'Published', className: 'bg-green-100 text-green-800' },
    archived: { label: 'Archived', className: 'bg-amber-100 text-amber-800' },
};

const config = map[props.status] ?? map.draft;
</script>

<template>
    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="config.className">{{ config.label }}</span>
</template>
