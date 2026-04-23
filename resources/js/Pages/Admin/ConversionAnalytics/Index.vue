<script setup>
import { Head, router } from '@inertiajs/vue3';
import Select from 'primevue/select';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    filters: {
        type: Object,
        required: true,
    },
    analytics: {
        type: Object,
        required: true,
    },
});

const dayOptions = [
    { label: '1 nap', value: 1 },
    { label: '7 nap', value: 7 },
    { label: '14 nap', value: 14 },
    { label: '30 nap', value: 30 },
    { label: '90 nap', value: 90 },
];

const updateDays = (value) => {
    router.get('/admin/conversion-analytics', { days: value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};
</script>

<template>
    <Head title="Konverziós analitika" />

    <section class="space-y-6">
        <header class="ui-card p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="font-heading text-3xl text-bakery-dark">Konverziós analitika</h1>
                    <p class="mt-2 text-sm text-bakery-dark/75">
                        CTA kattintások, kosár/checkout/regisztráció funnel és A/B hero teljesítmény.
                    </p>
                </div>
                <div class="w-full sm:w-48">
                    <Select
                        :model-value="filters.days"
                        :options="dayOptions"
                        option-label="label"
                        option-value="value"
                        class="w-full"
                        @update:model-value="updateDays"
                    />
                </div>
            </div>
        </header>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">Összes esemény</p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">{{ analytics.summary.total_events }}</p>
            </article>
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">CTA kattintás</p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">{{ analytics.summary.cta_clicks }}</p>
            </article>
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">Checkout lezárás</p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">{{ analytics.summary.checkout_completions }}</p>
            </article>
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">Regisztráció kész</p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">{{ analytics.summary.registration_completions }}</p>
            </article>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="ui-card overflow-hidden p-4 sm:p-5">
                <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">Funnel lépések</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                            <tr>
                                <th class="px-2 py-2">Funnel</th>
                                <th class="px-2 py-2">Lépés</th>
                                <th class="px-2 py-2 text-right">Esemény</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in analytics.funnel_steps" :key="`${row.funnel}-${row.step}`" class="border-b border-bakery-brown/10">
                                <td class="px-2 py-2 font-medium text-bakery-dark">{{ row.funnel }}</td>
                                <td class="px-2 py-2 text-bakery-dark/80">{{ row.step }}</td>
                                <td class="px-2 py-2 text-right font-semibold text-bakery-dark">{{ row.count }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="ui-card overflow-hidden p-4 sm:p-5">
                <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">Top CTA kattintások</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                            <tr>
                                <th class="px-2 py-2">CTA azonosító</th>
                                <th class="px-2 py-2 text-right">Kattintás</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in analytics.cta_top" :key="row.cta_id" class="border-b border-bakery-brown/10">
                                <td class="px-2 py-2 font-medium text-bakery-dark">{{ row.cta_id }}</td>
                                <td class="px-2 py-2 text-right font-semibold text-bakery-dark">{{ row.count }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <section class="ui-card overflow-hidden p-4 sm:p-5">
            <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80">A/B hero megtekintések</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <article v-for="variant in analytics.hero_variant_views" :key="variant.hero_variant" class="ui-card-soft p-4">
                    <p class="text-xs uppercase tracking-[0.1em] text-bakery-dark/60">{{ variant.hero_variant }}</p>
                    <p class="mt-2 font-heading text-3xl text-bakery-dark">{{ variant.count }}</p>
                </article>
            </div>
        </section>
    </section>
</template>

