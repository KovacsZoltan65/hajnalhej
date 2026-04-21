<script setup>
const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const labels = {
    pending: 'Pending',
    confirmed: 'Confirmed',
    in_preparation: 'In preparation',
    ready_for_pickup: 'Ready for pickup',
    completed: 'Completed',
    cancelled: 'Cancelled',
};

const classes = {
    pending: 'bg-amber-100 text-amber-800',
    confirmed: 'bg-blue-100 text-blue-800',
    in_preparation: 'bg-indigo-100 text-indigo-800',
    ready_for_pickup: 'bg-emerald-100 text-emerald-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-rose-100 text-rose-800',
};
</script>

<template>
    <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="classes[props.status] ?? 'bg-slate-100 text-slate-700'">
        {{ labels[props.status] ?? props.status }}
    </span>
</template>
