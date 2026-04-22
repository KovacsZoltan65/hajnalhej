<script setup>
import Button from 'primevue/button';

const props = defineProps({
    items: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['edit', 'delete']);

const formatQuantity = (value) =>
    new Intl.NumberFormat('hu-HU', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 3,
    }).format(value);
</script>

<template>
    <div class="space-y-3">
        <div
            v-for="item in items"
            :key="item.id"
            class="flex items-start justify-between gap-3 rounded-xl border border-bakery-brown/10 bg-white/80 p-3"
        >
            <div>
                <p class="font-medium text-bakery-dark">{{ item.ingredient_name }}</p>
                <p class="text-xs text-bakery-dark/65">{{ formatQuantity(item.quantity) }} {{ item.ingredient_unit }}</p>
                <p v-if="item.notes" class="mt-1 text-xs text-bakery-dark/70">{{ item.notes }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span
                    v-if="item.ingredient_is_low_stock"
                    class="rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800"
                >
                    Alacsony készlet
                </span>
                <Button icon="pi pi-pencil" text size="small" rounded @click="emit('edit', item)" />
                <Button icon="pi pi-trash" text size="small" rounded severity="danger" @click="emit('delete', item)" />
            </div>
        </div>
        <div
            v-if="items.length === 0"
            class="rounded-xl border border-dashed border-bakery-brown/25 bg-[#fcf7ef] p-4 text-center text-sm text-bakery-dark/70"
        >
            Ehhez a termekhez meg nincs recepttétel.
        </div>
    </div>
</template>

