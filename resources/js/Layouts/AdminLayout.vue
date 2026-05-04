<script setup>
import { computed, onMounted, onUnmounted } from "vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import AppHeader from "../Components/AppHeader.vue";
import AppLogo from "../Components/AppLogo.vue";
import AdminSidebar from "../Components/AdminSidebar.vue";
import FlashToast from "../Components/FlashToast.vue";
import { trans } from "laravel-vue-i18n";

const page = usePage();
const logoutForm = useForm({});

const logout = () => {
    logoutForm.post(route("logout"));
};

const hasAny = (can, permissions) =>
    permissions.some((permission) => Boolean(can[permission]));

const menuGroups = computed(() => {
    const can = page.props.auth?.can ?? {};

    return [
        {
            label: trans("nav.overview"),
            items: [
                {
                    label: trans("nav.dashboard"),
                    route: route("admin.dashboard"),
                    icon: "pi pi-chart-bar",
                },
                can.view_ceo_dashboard
                    ? {
                          label: trans("nav.ceo_dashboard"),
                          route: route("admin.ceo-dashboard.index"),
                          icon: "pi pi-briefcase",
                      }
                    : null,
                can.view_profit_dashboard
                    ? {
                          label: trans("nav.profit_dashboard"),
                          route: route("admin.profit-dashboard.index"),
                          icon: "pi pi-wallet",
                      }
                    : null,
                can.view_conversion_analytics
                    ? {
                          label: trans("nav.conversion_analytics"),
                          route: route("admin.conversion-analytics.index"),
                          icon: "pi pi-chart-line",
                      }
                    : null,
            ],
        },
        {
            label: trans("nav.catalog"),
            items: [
                {
                    label: trans("nav.products"),
                    route: route("admin.products.index"),
                    icon: "pi pi-box",
                },
                {
                    label: trans("nav.categories"),
                    route: route("admin.categories.store"),
                    icon: "pi pi-tags",
                },
                {
                    label: trans("nav.recipes"),
                    route: route("admin.recipes.index"),
                    icon: "pi pi-list-check",
                },
            ],
        },
        {
            label: trans("nav.operation"),
            items: [
                {
                    label: trans("nav.orders"),
                    route: route("admin.orders.index"),
                    icon: "pi pi-shopping-bag",
                },
                {
                    label: trans("nav.weekly_menu"),
                    route: route("admin.weekly-menus.index"),
                    icon: "pi pi-calendar",
                },
                {
                    label: trans("nav.production_plans"),
                    route: route("admin.production-plans.index"),
                    icon: "pi pi-sitemap",
                },
            ],
        },
        {
            label: trans("nav.inventory_procurement"),
            items: [
                {
                    label: trans("nav.ingredients"),
                    route: route("admin.ingredients.index"),
                    icon: "pi pi-warehouse",
                },
                hasAny(can, ["view_suppliers", "manage_suppliers"])
                    ? {
                          label: trans("nav.suplier_terms"),
                          route: route("admin.ingredient-supplier-terms.index"),
                          icon: "pi pi-sliders-h",
                      }
                    : null,
                hasAny(can, ["view_inventory_dashboard", "view_inventory"])
                    ? {
                          label: trans("nav.inventory"),
                          route: route("admin.inventory.index"),
                          icon: "pi pi-chart-scatter",
                      }
                    : null,
                can.manage_stock_counts
                    ? {
                          label: trans("nav.stock_count"),
                          route: route("admin.stock-counts.index"),
                          icon: "pi pi-clipboard",
                      }
                    : null,
                hasAny(can, ["view_suppliers", "manage_suppliers"])
                    ? {
                          label: trans("nav.supliers"),
                          route: route("admin.suppliers.index"),
                          icon: "pi pi-truck",
                      }
                    : null,
                hasAny(can, ["view_purchases", "manage_purchases"])
                    ? {
                          label: trans("nav.purchases"),
                          route: route("admin.purchases.index"),
                          icon: "pi pi-file-import",
                      }
                    : null,
                can.view_procurement_intelligence
                    ? {
                          label: trans("nav.procurement_inteligence"),
                          route: route("admin.procurement-intelligence.index"),
                          icon: "pi pi-sparkles",
                      }
                    : null,
            ],
        },
        {
            label: trans("nav.administration"),
            items: [
                hasAny(can, ["assign_user_roles", "view_user_permissions"])
                    ? {
                          label: trans("nav.user_roles"),
                          route: route("admin.user-roles.index"),
                          icon: "pi pi-user-edit",
                      }
                    : null,
                can.view_admin_users
                    ? {
                          label: trans("nav.users"),
                          route: trans("admin.users.index"),
                          icon: "pi pi-users",
                      }
                    : null,
                can.manage_roles
                    ? {
                          label: trans("nav.roles"),
                          route: route("admin.roles.index"),
                          icon: "pi pi-shield",
                      }
                    : null,
                can.manage_permissions
                    ? {
                          label: trans("nav.permissions"),
                          route: route("admin.permissions.index"),
                          icon: "pi pi-key",
                      }
                    : null,
                can.view_security_dashboard
                    ? {
                          label: trans("nav.security_dashboard"),
                          route: route("admin.security-dashboard.index"),
                          icon: "pi pi-shield",
                      }
                    : null,
                can.view_audit_logs
                    ? {
                          label: trans("nav.audit_logs"),
                          route: route("admin.audit-logs.index"),
                          icon: "pi pi-history",
                      }
                    : null,
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
    document.body.classList.add("admin-shell-active");
});

onUnmounted(() => {
    document.body.classList.remove("admin-shell-active");
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
                <Link
                    :href="route('admin.dashboard')"
                    class="block rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bakery-gold/70"
                >
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
                            <span class="font-semibold">{{
                                page.props.auth?.user?.name
                            }}</span>
                        </p>
                        <button
                            type="button"
                            class="rounded-full border border-bakery-brown/25 px-4 py-2 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown hover:text-bakery-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bakery-gold/70"
                            @click="logout"
                        >
                            {{ $t("common.logout") }}
                        </button>
                    </div>
                </template>
            </AppHeader>

            <main
                class="main-content min-h-0 overflow-y-auto overscroll-contain scroll-smooth p-4 sm:p-6 lg:p-8"
            >
                <div class="ui-card ui-card-elevated min-h-full p-4 sm:p-6 lg:p-8">
                    <slot />
                </div>
            </main>
        </section>
    </div>
</template>
