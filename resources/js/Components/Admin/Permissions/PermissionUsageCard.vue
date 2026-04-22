<script setup>
const props = defineProps({
    rolesCount: {
        type: Number,
        required: true,
    },
    usersCount: {
        type: Number,
        required: true,
    },
    roleNames: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <article class="rounded-2xl border border-bakery-brown/15 bg-white/80 p-4">
        <h2 class="text-sm font-semibold uppercase tracking-[0.14em] text-bakery-brown/80">Usage</h2>
        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            <div class="rounded-lg bg-[#fff9f1] p-3">
                <p class="text-xs text-bakery-dark/70">Role count</p>
                <p class="text-lg font-semibold text-bakery-dark">{{ props.rolesCount }}</p>
            </div>
            <div class="rounded-lg bg-[#fff9f1] p-3">
                <p class="text-xs text-bakery-dark/70">User count</p>
                <p class="text-lg font-semibold text-bakery-dark">{{ props.usersCount }}</p>
            </div>
        </div>

        <div class="mt-3">
            <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/70">Role-ok</p>
            <p class="mt-2 text-sm text-bakery-dark/80">
                {{ props.roleNames.length > 0 ? props.roleNames.join(', ') : 'Nincs kapcsolodo role.' }}
            </p>
        </div>
    </article>
</template>
