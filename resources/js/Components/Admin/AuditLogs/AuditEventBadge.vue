<script setup>
const props = defineProps({
    eventKey: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        default: '',
    },
});

const classForEvent = (eventKey) => {
    if (eventKey.includes('blocked')) {
        return 'bg-rose-100 text-rose-700';
    }

    if (eventKey.includes('deleted')) {
        return 'bg-amber-100 text-amber-800';
    }

    if (eventKey.includes('created')) {
        return 'bg-emerald-100 text-emerald-700';
    }

    if (eventKey.includes('synced')) {
        return 'bg-blue-100 text-blue-700';
    }

    return 'bg-slate-100 text-slate-700';
};
</script>

<template>
    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="classForEvent(props.eventKey)">
        {{ props.label || props.eventKey }}
    </span>
</template>
