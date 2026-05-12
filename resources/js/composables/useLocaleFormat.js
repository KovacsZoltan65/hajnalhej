import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

export function useLocaleFormat() {
    const page = usePage();
    const pageProps = computed(() => page?.props ?? {});

    const locale = computed(
        () =>
            pageProps.value.locale ||
            pageProps.value.preferences?.locale ||
            document.documentElement.getAttribute("lang") ||
            "hu"
    );

    const currency = computed(() => pageProps.value.preferences?.currency ?? "HUF");

    const number = (value, options = {}) => {
        const numericValue = Number(value ?? 0);

        if (Number.isNaN(numericValue)) {
            return "-";
        }

        return new Intl.NumberFormat(locale.value, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 3,
            ...options,
        }).format(numericValue);
    };

    const formatCurrency = (value, options = {}) => {
        if (value === null || value === undefined) {
            return "-";
        }

        const numericValue = Number(value);

        if (Number.isNaN(numericValue)) {
            return "-";
        }

        return new Intl.NumberFormat(locale.value, {
            style: "currency",
            currency: currency.value,
            maximumFractionDigits: 0,
            ...options,
        }).format(numericValue);
    };

    const formatNumber = (value, options = {}) => number(value, options);

    const formatQuantity = (value, unit = "", options = {}) => {
        if (value === null || value === undefined || value === "") {
            return "-";
        }

        const formatted = number(value, options);

        return formatted === "-" || !unit ? formatted : `${formatted} ${unit}`;
    };

    const formatDate = (value, options = {}) => {
        if (!value) {
            return "";
        }

        return new Intl.DateTimeFormat(locale.value, options).format(new Date(value));
    };

    const formatDateTime = (value, options = {}) =>
        formatDate(value, {
            dateStyle: "medium",
            timeStyle: "short",
            ...options,
        });

    return {
        locale,
        currency,
        number,
        formatCurrency,
        formatNumber,
        formatQuantity,
        formatDate,
        formatDateTime,
    };
}
