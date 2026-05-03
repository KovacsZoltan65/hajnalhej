<script setup>
import { Head, Link } from "@inertiajs/vue3";
import DashboardCard from "../../Components/DashboardCard.vue";
import SectionTitle from "../../Components/SectionTitle.vue";
import AdminLayout from "../../Layouts/AdminLayout.vue";

defineOptions({ layout: AdminLayout });

defineProps({
    stats: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head :title="$t('nav.dashboard')" />

    <div class="space-y-8">
        <SectionTitle
            :eyebrow="$t('dashboard.eyebrow')"
            :title="$t('nav.dashboard')"
            :description="$t('dashboard.description')"
        />

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <DashboardCard
                :title="$t('dashboard.card_todays_orders')"
                :value="stats.ordersToday.toString()"
                icon="pi pi-shopping-cart"
            />
            <DashboardCard
                :title="$t('dashboard.card_weekly_revenue')"
                :value="stats.weekRevenue"
                icon="pi pi-wallet"
            />
            <DashboardCard
                :title="$t('dashboard.card_most_popular_product')"
                :value="stats.topProduct"
                icon="pi pi-star-fill"
            />
            <DashboardCard
                :title="$t('dashboard.card_next_pickup_lane')"
                :value="stats.nextPickupSlot"
                icon="pi pi-clock"
            />
        </div>

        <section class="rounded-2xl border border-bakery-brown/15 bg-[#fdf8f1] p-5">
            <h3 class="font-heading text-2xl text-bakery-dark">
                {{ $t("dashboard.next_steps_title") }}
            </h3>
            <p class="mt-2 text-sm text-bakery-dark/75">
                {{ $t("dashboard.next_steps_description") }}
            </p>
            <div class="mt-4 flex flex-wrap gap-2">
                <Link
                    href="/admin/categories"
                    class="rounded-full border border-bakery-brown/20 px-4 py-2 text-sm font-medium text-bakery-brown hover:bg-bakery-brown/10"
                >
                    Kategoriak kezelese
                </Link>
                <Link
                    href="/admin/products"
                    class="rounded-full border border-bakery-brown/20 px-4 py-2 text-sm font-medium text-bakery-brown hover:bg-bakery-brown/10"
                >
                    Termekek kezelese
                </Link>
                <Link
                    href="/admin/recipes"
                    class="rounded-full border border-bakery-brown/20 px-4 py-2 text-sm font-medium text-bakery-brown hover:bg-bakery-brown/10"
                >
                    Receptek kezelese
                </Link>
                <Link
                    href="/admin/ingredients"
                    class="rounded-full border border-bakery-brown/20 px-4 py-2 text-sm font-medium text-bakery-brown hover:bg-bakery-brown/10"
                >
                    Alapanyagok kezelese
                </Link>
                <Link
                    href="/admin/weekly-menus"
                    class="rounded-full border border-bakery-brown/20 px-4 py-2 text-sm font-medium text-bakery-brown hover:bg-bakery-brown/10"
                >
                    Heti menuk kezelese
                </Link>
            </div>
        </section>
    </div>
</template>
