import { computed, reactive } from "vue";
import { router } from "@inertiajs/vue3";

const cloneDefaults = (defaults) => ({ ...defaults });

export function useAdminFilterState({
    filters = {},
    defaults = {},
    routeName,
    toQuery,
    loading = null,
    routerOptions = {},
}) {
    const initialDefaults = cloneDefaults(defaults);

    const filterState = reactive({
        ...initialDefaults,
        ...Object.fromEntries(
            Object.entries(initialDefaults).map(([key, defaultValue]) => [
                key,
                (defaultValue === null ? filters[key] || null : filters[key] ?? defaultValue),
            ])
        ),
    });

    const sortOrder = computed(() =>
        filterState.sort_direction === "asc" ? 1 : -1
    );

    const resetFilters = () => {
        Object.assign(filterState, cloneDefaults(initialDefaults));
    };

    const load = (extra = {}) => {
        if (loading) {
            loading.value = true;
        }

        router.get(
            route(routeName),
            {
                ...(toQuery ? toQuery(filterState) : { ...filterState }),
                ...extra,
            },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
                ...routerOptions,
                onFinish: (...args) => {
                    if (loading) {
                        loading.value = false;
                    }

                    routerOptions.onFinish?.(...args);
                },
            }
        );
    };

    const submitFilters = () => load({ page: 1 });

    const clearFilters = () => {
        resetFilters();
        submitFilters();
    };

    const onSort = (event) => {
        filterState.sort_field = event.sortField;
        filterState.sort_direction = event.sortOrder === 1 ? "asc" : "desc";
        load({ page: 1 });
    };

    const onPage = (event) => {
        filterState.per_page = event.rows;
        load({ page: event.page + 1, per_page: event.rows });
    };

    return {
        filterState,
        sortOrder,
        load,
        submitFilters,
        clearFilters,
        resetFilters,
        onSort,
        onPage,
    };
}
