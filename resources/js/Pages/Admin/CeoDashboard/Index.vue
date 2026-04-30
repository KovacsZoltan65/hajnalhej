<script setup>
import { Head, router } from "@inertiajs/vue3";
import { computed } from "vue";
import { currentLocale, trans, transChoice } from "laravel-vue-i18n";
import Select from "primevue/select";
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    filters: {
        type: Object,
        required: true,
    },
    dashboard: {
        type: Object,
        required: true,
    },
});

const localeCode = computed(() => currentLocale.value ?? "hu");
const numberLocale = computed(() => {
    const locales = {
        hu: "hu-HU",
        en: "en-US",
    };

    return locales[localeCode.value] ?? localeCode.value;
});

const formatDayOption = (days) =>
    transChoice("common.day_count", days, { count: days });

const dayOptions = [7, 14, 30, 90].map((days) => ({
    label: formatDayOption(days),
    value: days,
}));

const updateDays = (value) => {
    router.get(
        "/admin/ceo-dashboard",
        { days: value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const topProducts = computed(() => props.dashboard.top_products ?? []);
const auditHighlights = computed(() => props.dashboard.audit_highlights ?? []);
const trendPoints = computed(() => props.dashboard.order_profit_trend?.points ?? []);
const conversion = computed(() => props.dashboard.conversion ?? {});
const kpiInsights = computed(() => props.dashboard.kpi_insights ?? {});

const formatCurrency = (value) =>
    new Intl.NumberFormat(numberLocale.value, {
        style: "currency",
        currency: "HUF",
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));

const formatPercent = (value) =>
    new Intl.NumberFormat(numberLocale.value, {
        style: "percent",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0) / 100);

const formatSignedPercent = (value) =>
    new Intl.NumberFormat(numberLocale.value, {
        style: "percent",
        signDisplay: "exceptZero",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0) / 100);

const formatDate = (value) => {
    if (!value) {
        return "-";
    }

    return new Intl.DateTimeFormat(numberLocale.value).format(new Date(value));
};

const trendIcon = (direction) => {
    if (direction === "up") {
        return "pi pi-arrow-up";
    }
    if (direction === "down") {
        return "pi pi-arrow-down";
    }

    return "pi pi-minus";
};

const trendClass = (direction) => {
    if (direction === "up") {
        return "text-emerald-700";
    }
    if (direction === "down") {
        return "text-red-700";
    }

    return "text-bakery-dark/70";
};

const ragClass = (rag) => {
    if (rag === "green") {
        return "bg-emerald-100 text-emerald-800";
    }
    if (rag === "red") {
        return "bg-red-100 text-red-800";
    }

    return "bg-amber-100 text-amber-800";
};

const ragLabel = (rag) => {
    if (rag === "green") {
        return trans("common.rag_green");
    }
    if (rag === "red") {
        return trans("common.rag_red");
    }

    return trans("common.rag_amber");
};
</script>

<template>
    <Head :title="$t('nav.ceo_dashboard')" />

    <section class="space-y-6">
        <header class="ui-card p-5 sm:p-6">
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h1 class="font-heading text-3xl text-bakery-dark">
                        {{ $t("nav.ceo_dashboard") }}
                    </h1>
                    <p class="mt-2 text-sm text-bakery-dark/75">
                        {{ $t("ceo_dashboard.description") }}
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
            <!-- INCOME -->
            <article class="ui-card p-4">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">
                        {{ $t("common.income") }}
                    </p>
                    <span
                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold"
                        :class="ragClass(kpiInsights.revenue?.rag)"
                    >
                        {{ ragLabel(kpiInsights.revenue?.rag) }}
                    </span>
                </div>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">
                    {{ formatCurrency(dashboard.summary.revenue) }}
                </p>
                <p
                    class="mt-2 flex items-center gap-1 text-xs"
                    :class="trendClass(kpiInsights.revenue?.trend)"
                >
                    <i :class="trendIcon(kpiInsights.revenue?.trend)" />
                    {{ $t("common.wow") }}: {{ formatSignedPercent(kpiInsights.revenue?.wow?.percent) }}
                </p>
                <p class="mt-1 text-xs text-bakery-dark/70">
                    {{ $t("common.mom") }}: {{ formatSignedPercent(kpiInsights.revenue?.mom?.percent) }}
                </p>
            </article>

            <!-- Estimated Profile -->
            <article class="ui-card p-4">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">
                        {{ $t("ceo_dashboard.card_estimated_profit") }}
                    </p>
                    <span
                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold"
                        :class="ragClass(kpiInsights.estimated_profit?.rag)"
                    >
                        {{ ragLabel(kpiInsights.estimated_profit?.rag) }}
                    </span>
                </div>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">
                    {{ formatCurrency(dashboard.summary.estimated_profit) }}
                </p>
                <p
                    class="mt-2 flex items-center gap-1 text-xs"
                    :class="trendClass(kpiInsights.estimated_profit?.trend)"
                >
                    <i :class="trendIcon(kpiInsights.estimated_profit?.trend)" />
                    {{ $t("common.wow") }}:
                    {{ formatSignedPercent(kpiInsights.estimated_profit?.wow?.percent) }}
                </p>
                <p class="mt-1 text-xs text-bakery-dark/70">
                    {{ $t("common.mom") }}:
                    {{ formatSignedPercent(kpiInsights.estimated_profit?.mom?.percent) }}
                </p>
            </article>

            <!-- PROFIT RATE -->
            <article class="ui-card p-4">
                <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">
                    {{ $t("ceo_dashboard.card_profit_rate") }}
                </p>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">
                    {{ formatPercent(dashboard.summary.estimated_margin_rate) }}
                </p>
            </article>

            <!-- Checkout Conversion -->
            <article class="ui-card p-4">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">
                        {{ $t("ceo_dashboard.card_checkout_conversion") }}
                    </p>
                    <span
                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold"
                        :class="ragClass(kpiInsights.checkout_conversion_rate?.rag)"
                    >
                        {{ ragLabel(kpiInsights.checkout_conversion_rate?.rag) }}
                    </span>
                </div>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">
                    {{ formatPercent(dashboard.summary.checkout_conversion_rate) }}
                </p>
                <p
                    class="mt-2 flex items-center gap-1 text-xs"
                    :class="trendClass(kpiInsights.checkout_conversion_rate?.trend)"
                >
                    <i :class="trendIcon(kpiInsights.checkout_conversion_rate?.trend)" />
                    {{ $t("common.wow") }}:
                    {{
                        formatSignedPercent(
                            kpiInsights.checkout_conversion_rate?.wow?.percent
                        )
                    }}
                </p>
                <p class="mt-1 text-xs text-bakery-dark/70">
                    {{ $t("common.mom") }}:
                    {{
                        formatSignedPercent(
                            kpiInsights.checkout_conversion_rate?.mom?.percent
                        )
                    }}
                </p>
            </article>

            <!-- Returning Customer Rate -->
            <article class="ui-card p-4">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">
                        {{ $t("ceo_dashboard.card_returning_customer_rate") }}
                    </p>
                    <span
                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold"
                        :class="ragClass(kpiInsights.repeat_customer_rate?.rag)"
                    >
                        {{ ragLabel(kpiInsights.repeat_customer_rate?.rag) }}
                    </span>
                </div>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">
                    {{ formatPercent(dashboard.summary.repeat_customer_rate) }}
                </p>
                <p
                    class="mt-2 flex items-center gap-1 text-xs"
                    :class="trendClass(kpiInsights.repeat_customer_rate?.trend)"
                >
                    <i :class="trendIcon(kpiInsights.repeat_customer_rate?.trend)" />
                    {{ $t("common.wow") }}:
                    {{
                        formatSignedPercent(
                            kpiInsights.repeat_customer_rate?.wow?.percent
                        )
                    }}
                </p>
                <p class="mt-1 text-xs text-bakery-dark/70">
                    {{ $t("common.mom") }}:
                    {{
                        formatSignedPercent(
                            kpiInsights.repeat_customer_rate?.mom?.percent
                        )
                    }}
                </p>
            </article>

            <!-- LTV -->
            <article class="ui-card p-4">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs uppercase tracking-[0.12em] text-bakery-dark/60">
                        {{ $t("ceo_dashboard.card_lifetime_value") }}
                    </p>
                    <span
                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold"
                        :class="ragClass(kpiInsights.ltv?.rag)"
                    >
                        {{ ragLabel(kpiInsights.ltv?.rag) }}
                    </span>
                </div>
                <p class="mt-2 font-heading text-3xl text-bakery-dark">
                    {{ formatCurrency(dashboard.summary.ltv) }}
                </p>
                <p
                    class="mt-2 flex items-center gap-1 text-xs"
                    :class="trendClass(kpiInsights.ltv?.trend)"
                >
                    <i :class="trendIcon(kpiInsights.ltv?.trend)" />
                    {{ $t("common.wow") }}: {{ formatSignedPercent(kpiInsights.ltv?.wow?.percent) }}
                </p>
                <p class="mt-1 text-xs text-bakery-dark/70">
                    {{ $t("common.mom") }}: {{ formatSignedPercent(kpiInsights.ltv?.mom?.percent) }}
                </p>
            </article>
        </div>

        <section class="ui-card p-4 sm:p-5">
            <h2
                class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80"
            >
                {{ $t("ceo_dashboard.conversion_overview") }}
            </h2>

            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <article class="ui-card-soft p-4">
                    <p class="text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                        {{ $t("ceo_dashboard.checkout_funnel") }}
                    </p>
                    <p class="mt-2 text-sm text-bakery-dark">
                        {{ $t("common.submitted") }}:
                        <strong>{{ conversion.checkout_submitted ?? 0 }}</strong> ·
                        {{ $t("common.completed") }}:
                        <strong>{{ conversion.checkout_completed ?? 0 }}</strong>
                    </p>
                    <p class="mt-1 text-sm text-bakery-dark/80">
                        {{ $t("common.ratio") }}:
                        <strong>{{
                            formatPercent(conversion.checkout_conversion_rate)
                        }}</strong>
                    </p>
                </article>
                <article class="ui-card-soft p-4">
                    <p class="text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                        {{ $t("ceo_dashboard.registration_funnel") }}
                    </p>
                    <p class="mt-2 text-sm text-bakery-dark">
                        {{ $t("common.submitted") }}:
                        <strong>{{ conversion.registration_submitted ?? 0 }}</strong> ·
                        {{ $t("common.successful") }}:
                        <strong>{{ conversion.registration_completed ?? 0 }}</strong>
                    </p>
                    <p class="mt-1 text-sm text-bakery-dark/80">
                        {{ $t("common.ratio") }}:
                        <strong>{{
                            formatPercent(conversion.registration_conversion_rate)
                        }}</strong>
                    </p>
                </article>
            </div>
        </section>

        <section class="ui-card p-4 sm:p-5">
            <h2
                class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80"
            >
                {{ $t("ceo_dashboard.top_products") }}
            </h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead
                        class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60"
                    >
                        <tr>
                            <th class="px-2 py-2">{{ $t("common.product") }}</th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.income") }}
                            </th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.profit") }}
                            </th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.profit") }} %
                            </th>
                            <th class="px-2 py-2 text-right">{{ $t("common.piece") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in topProducts"
                            :key="`ceo-top-${row.product_id}`"
                            class="border-b border-bakery-brown/10"
                        >
                            <td class="px-2 py-2 font-medium text-bakery-dark">
                                {{ row.product_name }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ formatCurrency(row.revenue) }}
                            </td>
                            <td
                                class="px-2 py-2 text-right font-semibold text-bakery-dark"
                            >
                                {{ formatCurrency(row.estimated_profit) }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ formatPercent(row.margin_rate) }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ row.quantity }}
                            </td>
                        </tr>
                        <tr v-if="topProducts.length === 0">
                            <td
                                colspan="5"
                                class="px-2 py-4 text-center text-bakery-dark/70"
                            >
                                {{ $t("ceo_dashboard.empty_data") }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="ui-card p-4 sm:p-5">
                <h2
                    class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80"
                >
                    {{ $t("ceo_dashboard.safety_signs") }}
                </h2>
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    <article class="ui-card-soft p-4">
                        <div class="flex items-center justify-between gap-2">
                            <p
                                class="text-xs uppercase tracking-[0.1em] text-bakery-dark/60"
                            >
                                {{ $t("ceo_dashboard.critical_alerts") }}
                            </p>
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                :class="
                                    ragClass(
                                        dashboard.security_alerts.states?.critical_alerts
                                    )
                                "
                            >
                                {{
                                    ragLabel(
                                        dashboard.security_alerts.states?.critical_alerts
                                    )
                                }}
                            </span>
                        </div>
                        <p class="mt-2 font-heading text-2xl text-bakery-dark">
                            {{ dashboard.security_alerts.critical_alerts }}
                        </p>
                    </article>
                    <article class="ui-card-soft p-4">
                        <div class="flex items-center justify-between gap-2">
                            <p
                                class="text-xs uppercase tracking-[0.1em] text-bakery-dark/60"
                            >
                                {{ $t("ceo_dashboard.orphaned_permissions") }}
                            </p>
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                :class="
                                    ragClass(
                                        dashboard.security_alerts.states
                                            ?.orphan_permissions
                                    )
                                "
                            >
                                {{
                                    ragLabel(
                                        dashboard.security_alerts.states
                                            ?.orphan_permissions
                                    )
                                }}
                            </span>
                        </div>
                        <p class="mt-2 font-heading text-2xl text-bakery-dark">
                            {{ dashboard.security_alerts.orphan_permissions }}
                        </p>
                    </article>
                    <article class="ui-card-soft p-4">
                        <div class="flex items-center justify-between gap-2">
                            <p
                                class="text-xs uppercase tracking-[0.1em] text-bakery-dark/60"
                            >
                                {{ $t("ceo_dashboard.dangerous_permissions") }}
                            </p>
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                :class="
                                    ragClass(
                                        dashboard.security_alerts.states
                                            ?.dangerous_permissions
                                    )
                                "
                            >
                                {{
                                    ragLabel(
                                        dashboard.security_alerts.states
                                            ?.dangerous_permissions
                                    )
                                }}
                            </span>
                        </div>
                        <p class="mt-2 font-heading text-2xl text-bakery-dark">
                            {{ dashboard.security_alerts.dangerous_permissions }}
                        </p>
                    </article>
                    <article class="ui-card-soft p-4">
                        <div class="flex items-center justify-between gap-2">
                            <p
                                class="text-xs uppercase tracking-[0.1em] text-bakery-dark/60"
                            >
                                {{ $t("ceo_dashboard.high_risk_users") }}
                            </p>
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                :class="
                                    ragClass(
                                        dashboard.security_alerts.states?.high_risk_users
                                    )
                                "
                            >
                                {{
                                    ragLabel(
                                        dashboard.security_alerts.states?.high_risk_users
                                    )
                                }}
                            </span>
                        </div>
                        <p class="mt-2 font-heading text-2xl text-bakery-dark">
                            {{ dashboard.security_alerts.high_risk_users }}
                        </p>
                    </article>
                </div>
            </section>

            <section class="ui-card p-4 sm:p-5">
                <h2
                    class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80"
                >
                    {{ $t("ceo_dashboard.audit_highlights") }}
                </h2>
                <div class="mt-4 space-y-3">
                    <article
                        v-for="item in auditHighlights"
                        :key="`audit-${item.id}`"
                        class="ui-card-soft p-3"
                    >
                        <p class="text-xs uppercase tracking-[0.1em] text-bakery-dark/60">
                            {{ item.log_name }} · {{ item.severity }}
                        </p>
                        <p class="mt-1 font-medium text-bakery-dark">{{ item.label }}</p>
                        <p class="mt-1 text-xs text-bakery-dark/70">{{ item.summary }}</p>
                        <p class="mt-1 text-xs text-bakery-dark/60">
                            {{ $t("common.time") }}: {{ formatDate(item.timestamp) }}
                        </p>
                    </article>
                    <p
                        v-if="auditHighlights.length === 0"
                        class="ui-card-soft p-3 text-sm text-bakery-dark/70"
                    >
                        {{ $t("ceo_dashboard.audit_highlights_empty") }}
                    </p>
                </div>
            </section>
        </div>

        <section class="ui-card p-4 sm:p-5">
            <h2
                class="text-sm font-semibold uppercase tracking-[0.12em] text-bakery-brown/80"
            >
                {{ $t("ceo_dashboard.order_profit_trend") }}
            </h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead
                        class="border-b border-bakery-brown/15 text-left text-xs uppercase tracking-[0.1em] text-bakery-dark/60"
                    >
                        <tr>
                            <th class="px-2 py-2">{{ $t("common.date") }}</th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.income") }}
                            </th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.profit") }}
                            </th>
                            <th class="px-2 py-2 text-right">
                                {{ $t("common.profit") }} %
                            </th>
                            <th class="px-2 py-2 text-right">{{ $t("nav.orders") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in trendPoints"
                            :key="`ceo-trend-${row.date}`"
                            class="border-b border-bakery-brown/10"
                        >
                            <td class="px-2 py-2 font-medium text-bakery-dark">
                                {{ row.date }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ formatCurrency(row.revenue) }}
                            </td>
                            <td
                                class="px-2 py-2 text-right font-semibold text-bakery-dark"
                            >
                                {{ formatCurrency(row.estimated_profit) }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ formatPercent(row.margin_rate) }}
                            </td>
                            <td class="px-2 py-2 text-right text-bakery-dark">
                                {{ row.orders_count }}
                            </td>
                        </tr>
                        <tr v-if="trendPoints.length === 0">
                            <td
                                colspan="5"
                                class="px-2 py-4 text-center text-bakery-dark/70"
                            >
                                {{ $t("ceo_dashboard.order_profit_trend_empty") }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</template>
