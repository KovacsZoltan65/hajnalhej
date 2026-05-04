<script setup>
import { Head, router } from "@inertiajs/vue3";
import { computed } from "vue";
import { trans, currentLocale, transChoice } from "laravel-vue-i18n";
import Select from "primevue/select";
import AdminLayout from "@/Layouts/AdminLayout.vue";

import { createDayOptions } from "@/Utils/functions";

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

const formatDayOption = (days) => transChoice("common.day_count", days, { count: days });

const dayOptions = createDayOptions(trans, [1, 7, 14, 30, 90]);

const updateDays = (value) => {
    router.get(
        route("admin.conversion-analytics.index"),
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
const commerce = computed(() => props.analytics.commerce ?? {});
const commerceTrendPoints = computed(() => props.analytics.commerce_trend?.points ?? []);
const topProductRevenueRows = computed(() => props.analytics.top_product_revenue ?? []);
const funnelStats = computed(() => props.analytics.funnel_stats ?? []);
const heroComparison = computed(() => props.analytics.hero_comparison ?? []);
const dropOffRows = computed(() => props.analytics.drop_off_top ?? []);
const localeCode = computed(() => currentLocale.value ?? "hu");
const numberLocale = computed(() => {
    const locales = {
        hu: "hu-HU",
        en: "en-US",
    };

    return locales[localeCode.value] ?? localeCode.value;
});

const formatPercent = (value) =>
    new Intl.NumberFormat(numberLocale.value, {
        style: "percent",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0) / 100);

const formatCurrency = (value) =>
    new Intl.NumberFormat(numberLocale.value, {
        style: "currency",
        currency: "HUF",
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));

const formatDate = (value) => {
    if (!value) {
        return "-";
    }

    return new Intl.DateTimeFormat(numberLocale.value).format(new Date(value));
};
</script>

<template>
    <Head :title="$t('nav.conversion_analytics')" />

    <section class="space-y-6">
        <header class="ui-card p-5 sm:p-6">
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1 class="font-heading text-3xl text-bakery-dark">
                        {{ $t("nav.conversion_analytics") }}
                    </h1>
                    <p class="mt-2 text-sm text-bakery-dark/75">
                        {{ $t("conversion_analytics.description") }}
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

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
            <!-- Összes esemény -->
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">
                    {{ $t("common.all_events") }}
                </p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">
                    {{ analytics.summary.total_events }}
                </p>
            </article>
            <!-- CTA Kattintás -->
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">
                    {{ $t("common.cta_click") }}
                </p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">
                    {{ analytics.summary.cta_clicks }}
                </p>
            </article>
            <!-- Checkout lezárás -->
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">
                    {{ $t("common.checkout_closing") }}
                </p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">
                    {{ analytics.summary.checkout_completions }}
                </p>
            </article>
            <!-- Regisztráció kész -->
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">
                    {{ $t("common.registration_complete") }}
                </p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">
                    {{ analytics.summary.registration_completions }}
                </p>
            </article>
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">
                    {{ $t("common.income") }}
                </p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">
                    {{ formatCurrency(analytics.summary.revenue_total) }}
                </p>
            </article>
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">
                    {{ $t("common.avg_basket_value") }}
                </p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">
                    {{ formatCurrency(analytics.summary.average_cart_value) }}
                </p>
            </article>
        </div>

        <section class="ui-card p-4 sm:p-5">
            <h2
                class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80"
            >
                {{ $t("common.business_indicators") }}
            </h2>
            <div class="mt-4 grid gap-3 md:grid-cols-3 xl:grid-cols-6">
                <article class="ui-card-soft p-4">
                    <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
                        {{ $t("common.income") }}
                    </p>
                    <p class="mt-2 font-heading text-2xl text-bakery-dark">
                        {{ formatCurrency(commerce.revenue_total) }}
                    </p>
                </article>
                <article class="ui-card-soft p-4">
                    <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
                        {{ $t("common.order_number") }}
                    </p>
                    <p class="mt-2 font-heading text-2xl text-bakery-dark">
                        {{ commerce.orders_count ?? 0 }}
                    </p>
                </article>
                <article class="ui-card-soft p-4">
                    <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
                        {{ $t("common.individual_customers") }}
                    </p>
                    <p class="mt-2 font-heading text-2xl text-bakery-dark">
                        {{ commerce.unique_customers ?? 0 }}
                    </p>
                </article>
                <article class="ui-card-soft p-4">
                    <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
                        {{ $t("common.returning_customer_rate") }}
                    </p>
                    <p class="mt-2 font-heading text-2xl text-bakery-dark">
                        {{ formatPercent(commerce.repeat_customer_rate) }}
                    </p>
                </article>
                <article class="ui-card-soft p-4">
                    <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
                        {{ $t("common.avg_basket_value") }}
                    </p>
                    <p class="mt-2 font-heading text-2xl text-bakery-dark">
                        {{ formatCurrency(commerce.average_cart_value) }}
                    </p>
                </article>
                <article class="ui-card-soft p-4">
                    <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
                        {{ $t("common.ltv_periodic") }}
                    </p>
                    <p class="mt-2 font-heading text-2xl text-bakery-dark">
                        {{ formatCurrency(commerce.ltv) }}
                    </p>
                </article>
            </div>
        </section>

        <!-- Konverziós arányok -->
        <section class="ui-card p-4 sm:p-5">
            <h2
                class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80"
            >
                {{ $t("conversion_analytics.real_conversion_rates") }}
            </h2>
            <div class="mt-4 grid gap-3 md:grid-cols-3">
                <article
                    v-for="card in rateCards"
                    :key="card.id"
                    class="ui-card-soft p-4"
                >
                    <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
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
                {{ $t("conversion_analytics.revenue_and_basket_value_trend") }}
            </h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead
                        class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-widest text-bakery-dark/60"
                    >
                        <tr>
                            <th class="px-2 py-2">{{ $t("common.date") }}</th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.income") }}
                            </th>
                            <th class="px-2 py-2 text-right">{{ $t("common.order") }}</th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.avg_basket_value") }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="point in commerceTrendPoints"
                            :key="`commerce-${point.date}`"
                            class="border-b border-bakery-brown/10"
                        >
                            <td class="px-2 py-2 font-medium text-bakery-dark">
                                {{ formatDate(point.date) }}
                            </td>
                            <td
                                class="px-2 py-2 text-right font-semibold text-bakery-dark"
                            >
                                {{ formatCurrency(point.revenue) }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ point.orders_count }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ formatCurrency(point.average_cart_value) }}
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
                {{ $t("conversion_analytics.top_product_revenue") }}
            </h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead
                        class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-widest text-bakery-dark/60"
                    >
                        <tr>
                            <th class="px-2 py-2">{{ $t("common.product") }}</th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.income") }}
                            </th>
                            <th class="px-2 py-2 text-right">{{ $t("common.piece") }}</th>
                            <th class="px-2 py-2 text-right">{{ $t("common.order") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in topProductRevenueRows"
                            :key="`product-revenue-${row.product_name}`"
                            class="border-b border-bakery-brown/10"
                        >
                            <td class="px-2 py-2 font-medium text-bakery-dark">
                                {{ row.product_name }}
                            </td>
                            <td
                                class="px-2 py-2 text-right font-semibold text-bakery-dark"
                            >
                                {{ formatCurrency(row.revenue) }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ row.quantity }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ row.orders }}
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
                {{ $t("common.time_series_trends") }}
            </h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead
                        class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-widest text-bakery-dark/60"
                    >
                        <tr>
                            <th class="px-2 py-2">{{ $t("common.date") }}</th>
                            <th class="px-2 py-2 text-right">{{ $t("common.cta") }}</th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.add_to_card") }}
                            </th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.checkout_submit") }}
                            </th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.checkout_completed") }}
                            </th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.reg_completed") }}
                            </th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.submit_complete") }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="point in trendPoints"
                            :key="point.date"
                            class="border-b border-bakery-brown/10"
                        >
                            <td class="px-2 py-2 font-medium text-bakery-dark">
                                {{ formatDate(point.date) }}
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
                {{ $t("conversion_analytics.hero_variant_comparison") }}
            </h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead
                        class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-widest text-bakery-dark/60"
                    >
                        <tr>
                            <th class="px-2 py-2">{{ $t("common.variant") }}</th>
                            <th class="px-2 py-2 text-right">{{ $t("common.view") }}</th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.view_share") }}
                            </th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.cta_ctr") }}
                            </th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.reg_ctr") }}
                            </th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("conversion_analytics.checkout_session_rate") }}
                            </th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("conversion_analytics.reg_session_rate") }}
                            </th>
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
                {{ $t("conversion_analytics.top_funnel_drop_off_points") }}
            </h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead
                        class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-widest text-bakery-dark/60"
                    >
                        <tr>
                            <th class="px-2 py-2">{{ $t("common.funnel") }}</th>
                            <th class="px-2 py-2">{{ $t("common.step_change") }}</th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.fall") }} ({{ $t("common.pcs") }})
                            </th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.fall") }} ({{ $t("common.percent_code") }})
                            </th>
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
                    {{ $t("conversion_analytics.funnel_steps") }}
                </h2>
                <div class="mt-4 space-y-4">
                    <article
                        v-for="funnel in funnelStats"
                        :key="funnel.id"
                        class="ui-card-soft p-4"
                    >
                        <p class="text-xs uppercase tracking-widest text-bakery-dark/60">
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
                    {{ $t("common.cta_top_click") }}
                </h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead
                            class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-widest text-bakery-dark/60"
                        >
                            <tr>
                                <th class="px-2 py-2">{{ $t("common.cta_id") }}</th>
                                <th class="px-2 py-2 text-right">
                                    {{ $t("common.click") }}
                                </th>
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
