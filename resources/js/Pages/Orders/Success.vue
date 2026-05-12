<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import { useLocaleFormat } from '@/composables/useLocaleFormat';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const { formatCurrency } = useLocaleFormat();
</script>

<template>
    <Head :title="$t('orders.success.meta_title')" />

    <section class="mx-auto max-w-3xl space-y-6">
        <header class="rounded-3xl border border-emerald-200 bg-emerald-50 p-6 sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">{{ $t('orders.success.brand_name') }}</p>
            <h1 class="mt-2 font-heading text-4xl text-bakery-dark">{{ $t('orders.success.title') }}</h1>
            <p class="mt-2 text-sm text-bakery-dark/75">{{ $t('orders.success.subtitle') }}</p>
        </header>

        <article class="rounded-2xl border border-bakery-brown/15 bg-[#fff9f1] p-5">
            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-bakery-dark/60">{{ $t('orders.success.order_number') }}</dt>
                    <dd class="font-semibold text-bakery-dark">{{ order.order_number }}</dd>
                </div>
                <div>
                    <dt class="text-bakery-dark/60">{{ $t('orders.success.total_label') }}</dt>
                    <dd class="font-semibold text-bakery-dark">{{ formatCurrency(order.total) }}</dd>
                </div>
                <div>
                    <dt class="text-bakery-dark/60">{{ $t('orders.success.pickup_date') }}</dt>
                    <dd class="font-semibold text-bakery-dark">{{ order.pickup_date || '-' }}</dd>
                </div>
                <div>
                    <dt class="text-bakery-dark/60">{{ $t('orders.success.pickup_time_slot') }}</dt>
                    <dd class="font-semibold text-bakery-dark">{{ order.pickup_time_slot || '-' }}</dd>
                </div>
            </dl>
        </article>

        <div class="flex flex-wrap gap-3">
            <Link href="/weekly-menu" class="rounded-full bg-bakery-brown px-5 py-2.5 text-sm font-semibold text-bakery-cream transition hover:bg-bakery-dark">
                {{ $t('orders.success.start_new_order') }}
            </Link>
            <Link href="/account" class="rounded-full border border-bakery-brown/25 px-5 py-2.5 text-sm font-semibold text-bakery-brown transition hover:bg-bakery-brown hover:text-bakery-cream">
                {{ $t('orders.success.account') }}
            </Link>
        </div>
    </section>
</template>
