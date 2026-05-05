import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

export function useLocaleFormat() {
    const page = usePage();

    const locale = computed(() => page.props.locale ?? "hu");

    const number = (value, options = {}) =>
        new Intl.NumberFormat(locale.value, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 3,
            ...options,
        }).format(Number(value ?? 0));

    const currency = (value, currency = "HUF", options = {}) =>
        new Intl.NumberFormat(locale.value, {
            style: "currency",
            currency,
            maximumFractionDigits: 0,
            ...options,
        }).format(Number(value ?? 0));

    return {
        locale,
        number,
        currency,
    };
}
