<script setup>
import { computed, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { loadLanguageAsync } from "laravel-vue-i18n";
import SelectButton from "primevue/selectbutton";

const page = usePage();
const isLoading = ref(false);
const selectedLocale = ref(page.props.locale);

const availableLocales = computed(() => page.props.available_locales ?? []);
const activeLocale = computed(() => page.props.locale);

watch(activeLocale, (locale) => {
    selectedLocale.value = locale;
});

const switchLocale = async (locale) => {
    if (!locale || locale === activeLocale.value || isLoading.value) {
        selectedLocale.value = activeLocale.value;
        return;
    }

    isLoading.value = true;

    try {
        await window.axios.post(
            route("locale.switch"),
            { locale },
            {
                headers: {
                    Accept: "application/json",
                },
            },
        );

        await loadLanguageAsync(locale);

        page.props.locale = locale;
        if (page.props.auth?.user) {
            page.props.auth.user.locale = locale;
        }

        document.documentElement.setAttribute("lang", locale);
        selectedLocale.value = locale;
    } catch (error) {
        selectedLocale.value = activeLocale.value;
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <SelectButton
        v-model="selectedLocale"
        :options="availableLocales"
        option-label="label"
        option-value="code"
        :allow-empty="false"
        :disabled="isLoading"
        class="locale-switcher"
        aria-label="Locale"
        @update:model-value="switchLocale"
    />
</template>
