<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const links = [
    { label: 'Dashboard', href: '/admin/dashboard', icon: 'pi pi-chart-bar' },
    { label: 'Kategoriak', href: '/admin/categories', icon: 'pi pi-tags' },
    { label: 'Termekek', href: '/admin/products', icon: 'pi pi-box' },
    { label: 'Alapanyagok', href: '/admin/ingredients', icon: 'pi pi-warehouse' },
    { label: 'Heti menuk', href: '/admin/weekly-menus', icon: 'pi pi-calendar' },
    { label: 'Rendelesek', href: '#', icon: 'pi pi-shopping-bag' },
];

const isActive = (href) => href !== '#' && (page.url === href || page.url.startsWith(`${href}/`));
</script>

<template>
    <aside class="flex w-full flex-col gap-2 lg:w-64">
        <p class="px-3 text-xs font-semibold uppercase tracking-[0.2em] text-bakery-brown/70">Admin menu</p>
        <Link
            v-for="link in links"
            :key="link.label"
            :href="link.href"
            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition"
            :class="
                isActive(link.href)
                    ? 'bg-bakery-brown text-bakery-cream shadow-sm'
                    : 'text-bakery-dark/80 hover:bg-bakery-brown/10'
            "
        >
            <i :class="link.icon" />
            <span>{{ link.label }}</span>
        </Link>
    </aside>
</template>
