<script setup>
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import CategoryStatusBadge from '../Categories/CategoryStatusBadge.vue';

defineProps({
    recipes: {
        type: Object,
        required: true,
    },
    loading: {
        type: Boolean,
        required: true,
    },
    first: {
        type: Number,
        required: true,
    },
    sortField: {
        type: String,
        required: true,
    },
    sortOrder: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['sort', 'page', 'open-editor']);
</script>

<template>
    <div class="overflow-x-auto">
        <DataTable
            :value="recipes.data"
            lazy
            paginator
            scrollable
            :rows="recipes.per_page"
            :first="first"
            :total-records="recipes.total"
            :loading="loading"
            data-key="id"
            sort-mode="single"
            :sort-field="sortField"
            :sort-order="sortOrder"
            @sort="(event) => emit('sort', event)"
            @page="(event) => emit('page', event)"
        >
            <template #empty>
                <div class="rounded-xl border border-dashed border-bakery-brown/25 bg-[#fcf7ef] p-6 text-center text-sm text-bakery-dark/70">
                    Nincs megjeleníthető recept. Válassz terméket, majd add hozzá az első alapanyagot.
                </div>
            </template>

        <Column field="name" header="Termék" sortable>
            <template #body="{ data }">
                <div>
                    <p class="font-semibold text-bakery-dark">{{ data.name }}</p>
                    <p class="text-xs text-bakery-dark/60">/{{ data.slug }}</p>
                </div>
            </template>
        </Column>
        <Column field="category_name" header="Kategória" />
        <Column field="is_active" header="Státusz">
            <template #body="{ data }">
                <CategoryStatusBadge :active="data.is_active" />
            </template>
        </Column>
        <Column field="recipe_items_count" header="Recept tételek" sortable>
            <template #body="{ data }">
                <span class="font-medium text-bakery-dark">{{ data.recipe_items_count }}</span>
            </template>
        </Column>
        <Column field="recipe_steps_count" header="Lépések" sortable>
            <template #body="{ data }">
                <span class="font-medium text-bakery-dark">{{ data.recipe_steps_count }}</span>
            </template>
        </Column>
        <Column field="low_stock_ingredients_count" header="Alacsony készlet alapanyag">
            <template #body="{ data }">
                <span
                    class="rounded-full px-2 py-1 text-xs font-semibold"
                    :class="data.low_stock_ingredients_count > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
                >
                    {{ data.low_stock_ingredients_count }}
                </span>
            </template>
        </Column>
        <Column header="Műveletek" :exportable="false">
            <template #body="{ data }">
                <Button icon="pi pi-pencil" text rounded class="h-11! w-11!" aria-label="Recept szerkesztése" @click="emit('open-editor', data)" />
            </template>
        </Column>
        </DataTable>
    </div>
</template>


