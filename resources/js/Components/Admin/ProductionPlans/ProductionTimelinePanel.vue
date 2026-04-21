<script setup>
defineProps({
    steps: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div class="space-y-2 rounded-xl border border-bakery-brown/15 bg-white p-4">
        <div class="flex items-center justify-between">
            <h4 class="text-sm font-semibold text-bakery-dark">Valos timeline</h4>
            <p class="text-xs text-bakery-dark/65">{{ steps.length }} lepes</p>
        </div>

        <div v-if="steps.length === 0" class="rounded-lg border border-dashed border-bakery-brown/20 bg-[#fcf8f1] p-3 text-sm text-bakery-dark/70">
            Nincs generalt timeline. Adj meg receptlepeseket a termekekhez.
        </div>

        <div v-else class="max-h-72 space-y-2 overflow-y-auto pr-1">
            <div
                v-for="step in steps"
                :key="step.id"
                class="rounded-lg border px-3 py-2"
                :class="step.is_dependency ? 'border-bakery-gold/45 bg-[#fffaf0]' : 'border-bakery-brown/12 bg-[#fcf8f1]'"
            >
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="text-sm font-semibold text-bakery-dark">{{ step.title }}</p>
                        <p class="text-xs text-bakery-dark/70">
                            {{ step.starts_at }} -> {{ step.ends_at }}
                        </p>
                    </div>
                    <span
                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                        :class="step.is_dependency ? 'bg-bakery-gold/30 text-bakery-dark' : 'bg-bakery-brown/10 text-bakery-dark'"
                    >
                        {{ step.is_dependency ? 'Starter' : 'Fo lepes' }}
                    </span>
                </div>
                <p class="mt-1 text-xs text-bakery-dark/75">
                    Aktiv: {{ step.duration_minutes }} perc | Varakozas: {{ step.wait_minutes }} perc
                </p>
                <p v-if="step.work_instruction" class="mt-1 text-xs text-bakery-dark/75">
                    <span class="font-semibold">Mit kell csinalni:</span> {{ step.work_instruction }}
                </p>
                <p v-if="step.completion_criteria" class="mt-1 text-xs text-bakery-dark/75">
                    <span class="font-semibold">Kesz allapot:</span> {{ step.completion_criteria }}
                </p>
                <p v-if="step.attention_points" class="mt-1 text-xs text-bakery-dark/75">
                    <span class="font-semibold">Mire figyelj:</span> {{ step.attention_points }}
                </p>
                <p v-if="step.required_tools" class="mt-1 text-xs text-bakery-dark/75">
                    <span class="font-semibold">Szukseges eszkoz:</span> {{ step.required_tools }}
                </p>
                <p v-if="step.expected_result" class="mt-1 text-xs text-bakery-dark/75">
                    <span class="font-semibold">Elvart eredmeny:</span> {{ step.expected_result }}
                </p>
                <p v-if="step.depends_on_product_name" class="mt-1 text-xs text-bakery-dark/75">
                    Fuggoseg celtermek: {{ step.depends_on_product_name }}
                </p>
            </div>
        </div>
    </div>
</template>
