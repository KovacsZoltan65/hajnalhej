<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '../../Layouts/PublicLayout.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head title="Rendeles sikeres" />

    <section class="mx-auto max-w-3xl space-y-6">
        <header class="rounded-3xl border border-emerald-200 bg-emerald-50 p-6 sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Hajnalhej Bakery</p>
            <h1 class="mt-2 font-heading text-4xl text-bakery-dark">Koszonjuk a rendelesedet!</h1>
            <p class="mt-2 text-sm text-bakery-dark/75">A rendelesedet rogzitettuk. Hamarosan visszaigazoljuk.</p>
        </header>

        <article class="rounded-2xl border border-bakery-brown/15 bg-[#fff9f1] p-5">
            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-bakery-dark/60">Rendeles azonosito</dt>
                    <dd class="font-semibold text-bakery-dark">{{ order.order_number }}</dd>
                </div>
                <div>
                    <dt class="text-bakery-dark/60">Vegosszeg</dt>
                    <dd class="font-semibold text-bakery-dark">{{ new Intl.NumberFormat('hu-HU').format(order.total) }} Ft</dd>
                </div>
                <div>
                    <dt class="text-bakery-dark/60">Atvetel datuma</dt>
                    <dd class="font-semibold text-bakery-dark">{{ order.pickup_date || '-' }}</dd>
                </div>
                <div>
                    <dt class="text-bakery-dark/60">Atveteli idosav</dt>
                    <dd class="font-semibold text-bakery-dark">{{ order.pickup_time_slot || '-' }}</dd>
                </div>
            </dl>
        </article>

        <div class="flex flex-wrap gap-3">
            <Link href="/weekly-menu" class="rounded-full bg-bakery-brown px-5 py-2.5 text-sm font-semibold text-bakery-cream transition hover:bg-bakery-dark">
                Ujabb rendeles inditasa
            </Link>
            <Link href="/account" class="rounded-full border border-bakery-brown/25 px-5 py-2.5 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown hover:text-bakery-cream">
                Fiokom
            </Link>
        </div>
    </section>
</template>
