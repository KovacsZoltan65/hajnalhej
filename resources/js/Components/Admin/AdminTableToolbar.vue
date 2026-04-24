<script setup>
defineProps({
    stacked: {
        type: Boolean,
        default: false,
    },
    filtersGridClass: {
        type: String,
        default: 'grid gap-3 sm:grid-cols-2 xl:grid-cols-4',
    },
    actionsClass: {
        type: String,
        default: 'flex items-center gap-2 xl:flex-nowrap',
    },
});
</script>

<template>
    <div
        class="ui-toolbar grid gap-3"
        :class="stacked ? 'grid-cols-1' : 'xl:grid-cols-[minmax(0,1fr)_auto] xl:items-end'"
    >
        <div :class="filtersGridClass" class="min-w-0">
            <slot name="filters" />
        </div>

        <div :class="[actionsClass, { 'xl:justify-self-end': !stacked }]" class="[&_.p-button]:min-h-11">
            <slot name="actions" />
        </div>
    </div>
</template>
