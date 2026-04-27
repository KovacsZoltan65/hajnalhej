<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DashboardCard from '../../Components/DashboardCard.vue';
import SectionTitle from '../../Components/SectionTitle.vue';
import AdminLayout from '../../Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

defineProps({
    stats: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head title="Admin vezérlőpult" />

    <div class="space-y-8">
        <SectionTitle
            eyebrow="Admin"
            title="Napi attekintes"
            description="Első alap vezérlőpult valós admin struktúrával, készen a következő modulok fogadására."
        />

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <DashboardCard title="Mai rendelések" :value="stats.ordersToday.toString()" icon="pi pi-shopping-cart" />
            <DashboardCard title="Heti árbevétel" :value="stats.weekRevenue" icon="pi pi-wallet" />
            <DashboardCard title="Legnépszerűbb termék" :value="stats.topProduct" icon="pi pi-star-fill" />
            <DashboardCard title="Következő átvételi sáv" :value="stats.nextPickupSlot" icon="pi pi-clock" />
        </div>

        <section class="rounded-2xl border border-bakery-brown/15 bg-[#fdf8f1] p-5">
            <h3 class="font-heading text-2xl text-bakery-dark">Kovetkezo lépések</h3>
            <p class="mt-2 text-sm text-bakery-dark/75">
                A következő fázisban ide csatlakoznak a valós riportok, rendelési mutatók, valamint a CRUD modulok navigációs
                celpontjai.
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
