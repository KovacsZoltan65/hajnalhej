import { computed, getCurrentInstance } from "vue";

export function useLocaleFormat() {
    const instance = getCurrentInstance();

    const locale = computed(
        () =>
            instance?.proxy?.$page?.props?.locale ||
            document.documentElement.getAttribute("lang") ||
            undefined,
    );

    const formatCurrency = (value, options = {}) =>
        new Intl.NumberFormat(locale.value, {
            style: "currency",
            currency: "HUF",
            maximumFractionDigits: 0,
            ...options,
        }).format(Number(value ?? 0));

    const formatNumber = (value, options = {}) =>
        new Intl.NumberFormat(locale.value, options).format(Number(value ?? 0));

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
        formatCurrency,
        formatNumber,
        formatDate,
        formatDateTime,
    };
}
