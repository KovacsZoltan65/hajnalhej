<script setup>
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';

defineProps({
    items: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['edit-item', 'delete-item']);

const formatQuantity = (value) =>
    new Intl.NumberFormat('hu-HU', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 3,
    }).format(value);
</script>

<template>
    <DataTable :value="items" data-key="id">
        <template #empty>
            <div class="p-4 text-sm text-bakery-dark/70">A termekhez meg nincs recept tetel.</div>
        </template>

        <Column field="ingredient_name" header="Alapanyag">
            <template #body="{ data }">
                <div>
                    <p class="font-medium text-bakery-dark">{{ data.ingredient_name }}</p>
                    <p class="text-xs text-bakery-dark/60">{{ data.ingredient_unit }}</p>
                </div>
            </template>
        </Column>
        <Column field="quantity" header="Mennyiseg">
            <template #body="{ data }">
                <span>{{ formatQuantity(data.quantity) }} {{ data.ingredient_unit }}</span>
            </template>
        </Column>
        <Column field="sort_order" header="Sorrend" />
        <Column field="notes" header="Megjegyzes">
            <template #body="{ data }">
                <span class="text-bakery-dark/70">{{ data.notes || '-' }}</span>
            </template>
        </Column>
        <Column header="Muveletek">
            <template #body="{ data }">
                <div class="flex items-center gap-2">
                    <Button icon="pi pi-pencil" size="small" text rounded @click="emit('edit-item', data)" />
                    <Button icon="pi pi-trash" size="small" text rounded severity="danger" @click="emit('delete-item', data)" />
                </div>
            </template>
        </Column>
    </DataTable>
</template>
