<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    item: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="item.route"
        :title="item.label"
        class="group flex h-10 min-w-0 items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium leading-none transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bakery-gold/70"
        :class="
            item.active
                ? 'bg-bakery-brown text-bakery-cream shadow-sm'
                : 'text-bakery-dark/75 hover:bg-bakery-brown/10 hover:text-bakery-brown'
        "
    >
        <i
            v-if="item.icon"
            :class="[item.icon, 'shrink-0 text-sm']"
            aria-hidden="true"
        />
        <span class="min-w-0 truncate">{{ item.label }}</span>
    </Link>
</template>
