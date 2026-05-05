import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";

export function useLocaleFormat() {
    const page = usePage();
    const pageProps = computed(() => page?.props ?? {});

    const locale = computed(
        () => pageProps.value.preferences?.locale ?? "hu-HU",
    );

    const currency = computed(
        () => pageProps.value.preferences?.currency ?? "HUF",
    );

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

    const formatQuantity = (value, unit = "", options = {}) => {
        if (value === null || value === undefined || value === "") {
            return "-";
        }

        const numericValue = Number(value);

        if (Number.isNaN(numericValue)) {
            return "-";
        }

        const formatted = new Intl.NumberFormat(locale.value, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 3,
            ...options,
        }).format(numericValue);

        return unit ? `${formatted} ${unit}` : formatted;
    };

    return {
        locale,
        currency,
        number,
        formatCurrency,
        formatQuantity,
    };
}
