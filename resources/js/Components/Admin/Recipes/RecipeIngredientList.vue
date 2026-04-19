<script setup>
const props = defineProps({
    items: {
        type: Array,
        required: true,
    },
});

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
            <span
                v-if="item.ingredient_is_low_stock"
                class="rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800"
            >
                Low stock
            </span>
        </div>
        <div
            v-if="items.length === 0"
            class="rounded-xl border border-dashed border-bakery-brown/25 bg-[#fcf7ef] p-4 text-center text-sm text-bakery-dark/70"
        >
            Ehhez a termekhez meg nincs recepttetel.
        </div>
    </div>
</template>
