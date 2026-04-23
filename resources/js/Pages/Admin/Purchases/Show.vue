<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

defineProps({
    purchase: { type: Object, required: true },
});
</script>

<template>
    <Head :title="`Beszerzés #${purchase.id}`" />

    <section class="space-y-6">
        <div class="ui-card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <h1 class="font-heading text-2xl text-bakery-dark">Beszerzés #{{ purchase.id }}</h1>
                <Link href="/admin/purchases" class="text-sm underline">Vissza</Link>
            </div>
            <div class="mt-4 grid gap-3 md:grid-cols-3 text-sm">
                <p><strong>Beszállító:</strong> {{ purchase.supplier_name || '-' }}</p>
                <p><strong>Referencia:</strong> {{ purchase.reference_number || '-' }}</p>
                <p><strong>Dátum:</strong> {{ purchase.purchase_date }}</p>
                <p><strong>Státusz:</strong> {{ purchase.status }}</p>
                <p><strong>Könyvelt:</strong> {{ purchase.posted_at || '-' }}</p>
                <p><strong>Összesen:</strong> {{ new Intl.NumberFormat('hu-HU').format(purchase.total) }} Ft</p>
            </div>
            <p v-if="purchase.notes" class="mt-3 text-sm text-bakery-dark/75">{{ purchase.notes }}</p>
        </div>

        <div class="ui-card p-4 sm:p-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                    <tr>
                        <th class="px-2 py-2">Alapanyag</th>
                        <th class="px-2 py-2 text-right">Mennyiség</th>
                        <th class="px-2 py-2 text-right">Egységár</th>
                        <th class="px-2 py-2 text-right">Összesen</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in purchase.items" :key="item.id" class="border-b border-bakery-brown/10">
                        <td class="px-2 py-2">{{ item.ingredient_name }}</td>
                        <td class="px-2 py-2 text-right">{{ item.quantity }} {{ item.unit }}</td>
                        <td class="px-2 py-2 text-right">{{ new Intl.NumberFormat('hu-HU').format(item.unit_cost) }} Ft</td>
                        <td class="px-2 py-2 text-right">{{ new Intl.NumberFormat('hu-HU').format(item.line_total) }} Ft</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>

