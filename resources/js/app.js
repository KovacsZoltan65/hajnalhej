import "./bootstrap";
import "../css/app.css";
import "primeicons/primeicons.css";

import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createApp, h } from "vue";
import PrimeVue from "primevue/config";
import Aura from "@primeuix/themes/aura";
import ToastService from "primevue/toastservice";
import ConfirmationService from "primevue/confirmationservice";

import { i18nVue } from "laravel-vue-i18n";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";

const normalizeLocale = (locale) => String(locale || "hu").split(/[-_]/)[0] || "hu";

createInertiaApp({
    title: (title) => (title ? `${title} | Hajnalhej` : "Hajnalhej"),
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob("./Pages/**/*.vue")),
    setup({ el, App, props, plugin }) {
        const locale = normalizeLocale(
            props?.initialPage?.props?.preferences?.locale || document.documentElement.getAttribute("lang") || "hu"
        );

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(i18nVue, {
                locale,
                fallbackLocale: "hu",
                resolve: async (lang) => {
                    const normalizedLang = normalizeLocale(lang);
                    const messages = import.meta.glob("../../lang/*.json"); // */
                    return await messages[`../../lang/${normalizedLang}.json`]();
                },
            })
            .use(ToastService)
            .use(ConfirmationService)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                    options: {
                        darkModeSelector: false,
                    },
                },
            })
            .mount(el);
    },
    progress: {
        color: "#6B4423",
    },
});
