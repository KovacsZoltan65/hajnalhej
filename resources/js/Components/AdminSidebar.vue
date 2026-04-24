<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const baseLinks = [
    { label: 'Vezérlőpult', href: '/admin/dashboard', icon: 'pi pi-chart-bar' },
    { label: 'Kategóriák', href: '/admin/categories', icon: 'pi pi-tags' },
    { label: 'Termékek', href: '/admin/products', icon: 'pi pi-box' },
    { label: 'Receptek', href: '/admin/recipes', icon: 'pi pi-list-check' },
    { label: 'Gyártástervező', href: '/admin/production-plans', icon: 'pi pi-sitemap' },
    { label: 'Alapanyagok', href: '/admin/ingredients', icon: 'pi pi-warehouse' },
    { label: 'Heti menük', href: '/admin/weekly-menus', icon: 'pi pi-calendar' },
    { label: 'Rendelések', href: '/admin/orders', icon: 'pi pi-shopping-bag' },
];

const links = computed(() => {
    const dynamicLinks = [...baseLinks];
    const can = page.props.auth?.can ?? {};

    if (can.manage_roles) {
        dynamicLinks.push({ label: 'Szerepkörök', href: '/admin/roles', icon: 'pi pi-shield' });
    }

    if (can.assign_user_roles || can.view_user_permissions) {
        dynamicLinks.push({ label: 'Felhasználói szerepkörök', href: '/admin/user-roles', icon: 'pi pi-users' });
    }

    if (can.manage_permissions) {
        dynamicLinks.push({ label: 'Jogosultságok', href: '/admin/permissions', icon: 'pi pi-key' });
    }

    if (can.view_security_dashboard) {
        dynamicLinks.push({ label: 'Biztonsági irányítópult', href: '/admin/security-dashboard', icon: 'pi pi-shield' });
    }

    if (can.view_conversion_analytics) {
        dynamicLinks.push({ label: 'Konverziós analitika', href: '/admin/conversion-analytics', icon: 'pi pi-chart-line' });
    }

    if (can.view_profit_dashboard) {
        dynamicLinks.push({ label: 'Profit irányítópult', href: '/admin/profit-dashboard', icon: 'pi pi-wallet' });
    }

    if (can.view_ceo_dashboard) {
        dynamicLinks.push({ label: 'CEO irányítópult', href: '/admin/ceo-dashboard', icon: 'pi pi-briefcase' });
    }

    if (can.view_suppliers || can.manage_suppliers) {
        dynamicLinks.push({ label: 'Beszállítók', href: '/admin/suppliers', icon: 'pi pi-truck' });
    }

    if (can.view_purchases || can.manage_purchases) {
        dynamicLinks.push({ label: 'Beszerzések', href: '/admin/purchases', icon: 'pi pi-file-import' });
    }

    if (can.view_procurement_intelligence) {
        dynamicLinks.push({ label: 'Beszerzési intelligencia', href: '/admin/procurement-intelligence', icon: 'pi pi-sparkles' });
    }

    if (can.view_inventory_dashboard || can.view_inventory) {
        dynamicLinks.push({ label: 'Készletmozgások', href: '/admin/inventory', icon: 'pi pi-chart-scatter' });
    }

    if (can.manage_stock_counts) {
        dynamicLinks.push({ label: 'Leltár', href: '/admin/stock-counts', icon: 'pi pi-clipboard' });
    }

    return dynamicLinks;
});

const isActive = (href) => page.url === href || page.url.startsWith(`${href}/`);
</script>

<template>
    <aside class="flex w-full flex-col gap-2 lg:w-64">
        <p class="px-3 text-xs font-semibold uppercase tracking-[0.2em] text-bakery-brown/70">Admin menü</p>
        <Link
            v-for="link in links"
            :key="link.label"
            :href="link.href"
            class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition"
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

