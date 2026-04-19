<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const links = [
    { label: 'Kezdolap', href: '/' },
    { label: 'Heti menu', href: '/weekly-menu' },
    { label: 'Rolunk', href: '/about' },
];

const isActive = (href) => page.url === href || page.url.startsWith(`${href}/`);
</script>

<template>
    <nav class="hidden items-center gap-1 md:flex">
        <Link
            v-for="link in links"
            :key="link.href"
            :href="link.href"
            class="rounded-full px-4 py-2 text-sm font-medium transition"
            :class="isActive(link.href) ? 'bg-bakery-brown text-bakery-cream' : 'text-bakery-dark/80 hover:bg-bakery-brown/10'"
        >
            {{ link.label }}
        </Link>
    </nav>
</template>
