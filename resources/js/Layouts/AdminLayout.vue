<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AppHeader from '../Components/AppHeader.vue';
import AppLogo from '../Components/AppLogo.vue';
import AdminSidebar from '../Components/AdminSidebar.vue';
import FlashToast from '../Components/FlashToast.vue';

const page = usePage();
const logoutForm = useForm({});

const logout = () => {
    logoutForm.post('/logout');
};

const hasAny = (can, permissions) => permissions.some((permission) => Boolean(can[permission]));

const menuGroups = computed(() => {
    const can = page.props.auth?.can ?? {};

    return [
        {
            label: 'Áttekintés',
            items: [
                { label: 'Dashboard', route: '/admin/dashboard', icon: 'pi pi-chart-bar' },
                can.view_ceo_dashboard
                    ? { label: 'CEO Dashboard', route: '/admin/ceo-dashboard', icon: 'pi pi-briefcase' }
                    : null,
                can.view_profit_dashboard
                    ? { label: 'Profit Dashboard', route: '/admin/profit-dashboard', icon: 'pi pi-wallet' }
                    : null,
                can.view_conversion_analytics
                    ? { label: 'Conversion Analytics', route: '/admin/conversion-analytics', icon: 'pi pi-chart-line' }
                    : null,
            ],
        },
        {
            label: 'Katalógus',
            items: [
                { label: 'Products', route: '/admin/products', icon: 'pi pi-box' },
                { label: 'Categories', route: '/admin/categories', icon: 'pi pi-tags' },
                { label: 'Recipes', route: '/admin/recipes', icon: 'pi pi-list-check' },
            ],
        },
        {
            label: 'Működés',
            items: [
                { label: 'Orders', route: '/admin/orders', icon: 'pi pi-shopping-bag' },
                { label: 'Weekly Menu', route: '/admin/weekly-menus', icon: 'pi pi-calendar' },
                { label: 'Production Plans', route: '/admin/production-plans', icon: 'pi pi-sitemap' },
            ],
        },
        {
            label: 'Készlet és beszerzés',
            items: [
                { label: 'Ingredients', route: '/admin/ingredients', icon: 'pi pi-warehouse' },
                hasAny(can, ['view_suppliers', 'manage_suppliers'])
                    ? { label: 'Supplier Terms', route: '/admin/ingredient-supplier-terms', icon: 'pi pi-sliders-h' }
                    : null,
                hasAny(can, ['view_inventory_dashboard', 'view_inventory'])
                    ? { label: 'Inventory', route: '/admin/inventory', icon: 'pi pi-chart-scatter' }
                    : null,
                can.manage_stock_counts ? { label: 'Stock Counts', route: '/admin/stock-counts', icon: 'pi pi-clipboard' } : null,
                hasAny(can, ['view_suppliers', 'manage_suppliers'])
                    ? { label: 'Suppliers', route: '/admin/suppliers', icon: 'pi pi-truck' }
                    : null,
                hasAny(can, ['view_purchases', 'manage_purchases'])
                    ? { label: 'Purchases', route: '/admin/purchases', icon: 'pi pi-file-import' }
                    : null,
                can.view_procurement_intelligence
                    ? { label: 'Procurement Intelligence', route: '/admin/procurement-intelligence', icon: 'pi pi-sparkles' }
                    : null,
            ],
        },
        {
            label: 'Adminisztráció',
            items: [
                hasAny(can, ['assign_user_roles', 'view_user_permissions'])
                    ? { label: 'Users', route: '/admin/user-roles', icon: 'pi pi-users' }
                    : null,
                can.manage_roles ? { label: 'Roles', route: '/admin/roles', icon: 'pi pi-shield' } : null,
                can.manage_permissions ? { label: 'Permissions', route: '/admin/permissions', icon: 'pi pi-key' } : null,
                can.view_security_dashboard
                    ? { label: 'Security Dashboard', route: '/admin/security-dashboard', icon: 'pi pi-shield' }
                    : null,
                can.view_audit_logs ? { label: 'Audit Logs', route: '/admin/audit-logs', icon: 'pi pi-history' } : null,
            ],
        },
    ]
        .map((group) => ({
            ...group,
            items: group.items.filter(Boolean),
        }))
        .filter((group) => group.items.length > 0);
});

onMounted(() => {
    document.body.classList.add('admin-shell-active');
});

onUnmounted(() => {
    document.body.classList.remove('admin-shell-active');
});
</script>

<template>
    <div
        class="ui-shell grid h-screen grid-cols-[minmax(0,1fr)] overflow-hidden bg-[#f7efe5] text-bakery-dark lg:grid-cols-[260px_minmax(0,1fr)]"
    >
        <FlashToast />

        <aside
            class="sidebar sticky top-0 hidden h-screen min-h-0 overflow-y-auto overscroll-contain border-r border-bakery-brown/15 bg-white/70 px-3 py-4 shadow-[8px_0_28px_rgba(43,33,24,0.06)] backdrop-blur lg:flex lg:flex-col"
        >
            <div class="shrink-0 border-b border-bakery-brown/10 px-2 pb-4">
                <Link href="/admin/dashboard" class="block rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bakery-gold/70">
                    <AppLogo />
                </Link>
            </div>

            <AdminSidebar :groups="menuGroups" class="mt-4 min-h-0 flex-1" />
        </aside>

        <section class="grid min-h-0 grid-rows-[auto_minmax(0,1fr)] overflow-hidden">
            <AppHeader container-class="max-w-none">
                <template #actions>
                    <div class="flex items-center gap-3">
                        <p class="hidden text-sm text-bakery-dark/75 sm:block">
                            Belépve:
                            <span class="font-semibold">{{ page.props.auth?.user?.name }}</span>
                        </p>
                        <button
                            type="button"
                            class="rounded-full border border-bakery-brown/25 px-4 py-2 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown hover:text-bakery-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bakery-gold/70"
                            @click="logout"
                        >
                            Kijelentkezés
                        </button>
                    </div>
                </template>
            </AppHeader>

            <main class="main-content min-h-0 overflow-y-auto overscroll-contain scroll-smooth p-4 sm:p-6 lg:p-8">
                <div class="ui-card ui-card-elevated min-h-full p-4 sm:p-6 lg:p-8">
                    <slot />
                </div>
            </main>
        </section>
    </div>
</template>
