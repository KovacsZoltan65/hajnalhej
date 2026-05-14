import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";

export function useExport() {
    const loading = ref(false);

    const startExport = ({ type, format, filters = {} }, options = {}) => {
        loading.value = true;

        router.post(
            route("admin.exports.store"),
            { type, format, filters },
            {
                preserveScroll: true,
                onFinish: () => {
                    loading.value = false;
                    options.onFinish?.();
                },
                onSuccess: () => options.onSuccess?.(),
                onError: (errors) => options.onError?.(errors),
            }
        );
    };

    const downloadUrl = (exportJob) => route("admin.exports.download", exportJob.id);

    return {
        loading: computed(() => loading.value),
        startExport,
        downloadUrl,
    };
}
