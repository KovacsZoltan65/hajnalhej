<script setup>
defineProps({
    summary: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-7">
        <article class="rounded-2xl border border-bakery-brown/10 bg-white/80 p-4 shadow-sm">
            <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/70">Osszes termek</p>
            <p class="mt-2 font-heading text-2xl text-bakery-dark">{{ summary.total_products }}</p>
        </article>
        <article class="rounded-2xl border border-bakery-brown/10 bg-white/80 p-4 shadow-sm">
            <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/70">Recepttel</p>
            <p class="mt-2 font-heading text-2xl text-bakery-dark">{{ summary.with_recipe }}</p>
        </article>
        <article class="rounded-2xl border border-bakery-brown/10 bg-white/80 p-4 shadow-sm">
            <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/70">Recept nelkul</p>
            <p class="mt-2 font-heading text-2xl text-bakery-dark">{{ summary.without_recipe }}</p>
        </article>
        <article class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 shadow-sm">
            <p class="text-xs uppercase tracking-[0.14em] text-amber-700">Low stock erintett</p>
            <p class="mt-2 font-heading text-2xl text-amber-800">{{ summary.with_low_stock }}</p>
        </article>
        <article class="rounded-2xl border border-bakery-brown/10 bg-white/80 p-4 shadow-sm">
            <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/70">Ossz. hozzavalo</p>
            <p class="mt-2 font-heading text-2xl text-bakery-dark">{{ summary.total_ingredients }}</p>
        </article>
        <article class="rounded-2xl border border-bakery-brown/10 bg-white/80 p-4 shadow-sm">
            <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/70">Ossz. lepes</p>
            <p class="mt-2 font-heading text-2xl text-bakery-dark">{{ summary.total_steps }}</p>
        </article>
        <article class="rounded-2xl border border-bakery-brown/10 bg-white/80 p-4 shadow-sm">
            <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/70">Ossz. recept ido</p>
            <p class="mt-2 font-heading text-2xl text-bakery-dark">{{ summary.total_recipe_minutes }} p</p>
        </article>
    </section>
</template>
