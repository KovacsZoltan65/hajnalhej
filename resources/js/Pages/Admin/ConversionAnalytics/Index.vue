<script setup>
import { Head, router } from "@inertiajs/vue3";
import { computed } from "vue";
import Select from "primevue/select";
import AdminLayout from "@/Layouts/AdminLayout.vue";

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
    { label: "1 nap", value: 1 },
    { label: "7 nap", value: 7 },
    { label: "14 nap", value: 14 },
    { label: "30 nap", value: 30 },
    { label: "90 nap", value: 90 },
];

const updateDays = (value) => {
    router.get(
        "/admin/conversion-analytics",
        { days: value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const rateCards = computed(() => props.analytics.conversion_rates ?? []);
const trendPoints = computed(() => props.analytics.trend?.points ?? []);
const funnelStats = computed(() => props.analytics.funnel_stats ?? []);
const heroComparison = computed(() => props.analytics.hero_comparison ?? []);
const dropOffRows = computed(() => props.analytics.drop_off_top ?? []);

const formatPercent = (value) => `${Number(value ?? 0).toFixed(2)}%`;
</script>

<template>
    <Head title="Konverziós analitika" />

    <section class="space-y-6">
        <header class="ui-card p-5 sm:p-6">
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1 class="font-heading text-3xl text-bakery-dark">
                        Konverziós analitika
                    </h1>
                    <p class="mt-2 text-sm text-bakery-dark/75">
                        Konverziós arányok, idősoros trendek, hero variáns összehasonlítás
                        és funnel drop-off pontok.
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
            <!-- Összes esemény -->
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">
                    Összes esemény
                </p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">
                    {{ analytics.summary.total_events }}
                </p>
            </article>
            <!-- CTA Kattintás -->
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">
                    CTA kattintás
                </p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">
                    {{ analytics.summary.cta_clicks }}
                </p>
            </article>
            <!-- Checkout lezárás -->
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">
                    Checkout lezárás
                </p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">
                    {{ analytics.summary.checkout_completions }}
                </p>
            </article>
            <!-- Regisztráció kész -->
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">
                    Regisztráció kész
                </p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">
                    {{ analytics.summary.registration_completions }}
                </p>
            </article>
        </div>

        <!-- Konverziós arányok -->
        <section class="ui-card p-4 sm:p-5">
            <h2
                class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80"
            >
                Valódi konverziós arányok
            </h2>
            <div class="mt-4 grid gap-3 md:grid-cols-3">
                <article
                    v-for="card in rateCards"
                    :key="card.id"
                    class="ui-card-soft p-4"
                >
                    <p class="text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                        {{ card.label }}
                    </p>
                    <p class="mt-2 font-heading text-3xl text-bakery-dark">
                        {{ formatPercent(card.rate) }}
                    </p>
                    <p class="mt-1 text-xs text-bakery-dark/70">
                        {{ card.numerator }} / {{ card.denominator }}
                    </p>
                </article>
            </div>
        </section>

        <section class="ui-card p-4 sm:p-5">
            <h2
                class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80"
            >
                Idősoros trendek
            </h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead
                        class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60"
                    >
                        <tr>
                            <th class="px-2 py-2">Dátum</th>
                            <th class="px-2 py-2 text-right">CTA</th>
                            <th class="px-2 py-2 text-right">Kosárba</th>
                            <th class="px-2 py-2 text-right">Checkout submit</th>
                            <th class="px-2 py-2 text-right">Checkout completed</th>
                            <th class="px-2 py-2 text-right">Reg. completed</th>
                            <th class="px-2 py-2 text-right">Submit->Complete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="point in trendPoints"
                            :key="point.date"
                            class="border-b border-bakery-brown/10"
                        >
                            <td class="px-2 py-2 font-medium text-bakery-dark">
                                {{ point.date }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ point.cta_clicks }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ point.cart_adds }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ point.checkout_submitted }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ point.checkout_completed }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ point.registration_completed }}
                            </td>
                            <td
                                class="px-2 py-2 text-right font-semibold text-bakery-dark"
                            >
                                {{
                                    formatPercent(point.checkout_submit_to_complete_rate)
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ui-card p-4 sm:p-5">
            <h2
                class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80"
            >
                Hero variáns összehasonlítás
            </h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead
                        class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60"
                    >
                        <tr>
                            <th class="px-2 py-2">Variáns</th>
                            <th class="px-2 py-2 text-right">Megtekintés</th>
                            <th class="px-2 py-2 text-right">View share</th>
                            <th class="px-2 py-2 text-right">CTA CTR</th>
                            <th class="px-2 py-2 text-right">Reg CTR</th>
                            <th class="px-2 py-2 text-right">Checkout session rate</th>
                            <th class="px-2 py-2 text-right">Reg session rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in heroComparison"
                            :key="row.variant"
                            class="border-b border-bakery-brown/10"
                        >
                            <td class="px-2 py-2 font-medium text-bakery-dark">
                                {{ row.variant }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ row.views }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ formatPercent(row.view_share) }}
                            </td>
                            <td
                                class="px-2 py-2 text-right font-semibold text-bakery-dark"
                            >
                                {{ formatPercent(row.cta_ctr) }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ formatPercent(row.register_ctr) }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ formatPercent(row.checkout_session_rate) }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ formatPercent(row.registration_session_rate) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="ui-card p-4 sm:p-5">
            <h2
                class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80"
            >
                Top funnel drop-off pontok
            </h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead
                        class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60"
                    >
                        <tr>
                            <th class="px-2 py-2">Funnel</th>
                            <th class="px-2 py-2">Lépésváltás</th>
                            <th class="px-2 py-2 text-right">Esés (db)</th>
                            <th class="px-2 py-2 text-right">Esés (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in dropOffRows"
                            :key="`${row.funnel}-${row.from}-${row.to}`"
                            class="border-b border-bakery-brown/10"
                        >
                            <td class="px-2 py-2 font-medium text-bakery-dark">
                                {{ row.funnel }}
                            </td>
                            <td class="px-2 py-2 text-bakery-dark/80">
                                {{ row.from }} -> {{ row.to }}
                            </td>
                            <td
                                class="px-2 py-2 text-right font-semibold text-bakery-dark"
                            >
                                {{ row.drop_count }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ formatPercent(row.drop_rate) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="ui-card overflow-hidden p-4 sm:p-5">
                <h2
                    class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80"
                >
                    Funnel lépések
                </h2>
                <div class="mt-4 space-y-4">
                    <article
                        v-for="funnel in funnelStats"
                        :key="funnel.id"
                        class="ui-card-soft p-4"
                    >
                        <p class="text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                            {{ funnel.label }}
                        </p>
                        <div class="mt-3 space-y-2">
                            <div
                                v-for="step in funnel.steps"
                                :key="`${funnel.id}-${step.event_key}-${step.label}`"
                                class="flex items-center justify-between text-sm"
                            >
                                <span class="text-bakery-dark/80">{{ step.label }}</span>
                                <span class="font-semibold text-bakery-dark"
                                    >{{ step.count }} ·
                                    {{
                                        formatPercent(step.conversion_from_previous)
                                    }}</span
                                >
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <section class="ui-card overflow-hidden p-4 sm:p-5">
                <h2
                    class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80"
                >
                    Top CTA kattintások
                </h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead
                            class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60"
                        >
                            <tr>
                                <th class="px-2 py-2">CTA azonosító</th>
                                <th class="px-2 py-2 text-right">Kattintás</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in analytics.cta_top"
                                :key="row.cta_id"
                                class="border-b border-bakery-brown/10"
                            >
                                <td class="px-2 py-2 font-medium text-bakery-dark">
                                    {{ row.cta_id }}
                                </td>
                                <td
                                    class="px-2 py-2 text-right font-semibold text-bakery-dark"
                                >
                                    {{ row.count }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </section>
</template>
