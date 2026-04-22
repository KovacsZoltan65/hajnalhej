<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const baseLinks = [
    { label: 'Dashboard', href: '/admin/dashboard', icon: 'pi pi-chart-bar' },
    { label: 'Kategoriak', href: '/admin/categories', icon: 'pi pi-tags' },
    { label: 'Termekek', href: '/admin/products', icon: 'pi pi-box' },
    { label: 'Receptek', href: '/admin/recipes', icon: 'pi pi-list-check' },
    { label: 'Gyartastervezo', href: '/admin/production-plans', icon: 'pi pi-sitemap' },
    { label: 'Alapanyagok', href: '/admin/ingredients', icon: 'pi pi-warehouse' },
    { label: 'Heti menuk', href: '/admin/weekly-menus', icon: 'pi pi-calendar' },
    { label: 'Rendelesek', href: '/admin/orders', icon: 'pi pi-shopping-bag' },
];

const links = computed(() => {
    const dynamicLinks = [...baseLinks];
    const can = page.props.auth?.can ?? {};

    if (can.manage_roles) {
        dynamicLinks.push({ label: 'Szerepkorok', href: '/admin/roles', icon: 'pi pi-shield' });
    }

    if (can.assign_user_roles || can.view_user_permissions) {
        dynamicLinks.push({ label: 'Felhasznalo szerepkorok', href: '/admin/user-roles', icon: 'pi pi-users' });
    }

    if (can.manage_permissions) {
        dynamicLinks.push({ label: 'Jogosultsagok', href: '/admin/permissions', icon: 'pi pi-key' });
    }

    return dynamicLinks;
});

const isActive = (href) => page.url === href || page.url.startsWith(`${href}/`);
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
