<script setup>
import Button from 'primevue/button';
import Tag from 'primevue/tag';

defineProps({
    steps: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['edit', 'delete']);
</script>

<template>
    <div class="space-y-3">
        <div
            v-for="step in steps"
            :key="step.id"
            class="rounded-xl border border-bakery-brown/10 bg-white/80 p-3"
        >
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <p class="font-medium text-bakery-dark">{{ step.title }}</p>
                        <Tag :value="step.step_type" severity="secondary" />
                    </div>
                    <p v-if="step.description" class="mt-1 text-xs text-bakery-dark/70">{{ step.description }}</p>
                    <p v-if="step.work_instruction" class="mt-1 text-xs text-bakery-dark/80">
                        <span class="font-semibold">Utasitas:</span> {{ step.work_instruction }}
                    </p>
                    <p v-if="step.completion_criteria" class="mt-1 text-xs text-bakery-dark/80">
                        <span class="font-semibold">Kesz allapot:</span> {{ step.completion_criteria }}
                    </p>
                    <p v-if="step.attention_points" class="mt-1 text-xs text-bakery-dark/80">
                        <span class="font-semibold">Figyelmeztetes:</span> {{ step.attention_points }}
                    </p>
                    <p v-if="step.required_tools" class="mt-1 text-xs text-bakery-dark/80">
                        <span class="font-semibold">Eszkoz:</span> {{ step.required_tools }}
                    </p>
                    <p v-if="step.expected_result" class="mt-1 text-xs text-bakery-dark/80">
                        <span class="font-semibold">Elvárt eredmény:</span> {{ step.expected_result }}
                    </p>
                    <p class="mt-1 text-xs text-bakery-dark/65">
                        Aktív: {{ step.duration_minutes ?? 0 }} p | Várakozás: {{ step.wait_minutes ?? 0 }} p
                        <span v-if="step.temperature_celsius !== null">| Homerseklet: {{ step.temperature_celsius }} C</span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button icon="pi pi-pencil" text size="small" rounded @click="emit('edit', step)" />
                    <Button icon="pi pi-trash" text size="small" rounded severity="danger" @click="emit('delete', step)" />
                </div>
            </div>
        </div>
        <div
            v-if="steps.length === 0"
            class="rounded-xl border border-dashed border-bakery-brown/25 bg-[#fcf7ef] p-4 text-center text-sm text-bakery-dark/70"
        >
            Ehhez a termékhez meg nincs receptlepes.
        </div>
    </div>
</template>


