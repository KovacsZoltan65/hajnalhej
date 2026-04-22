<script setup>
import { computed } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import ProductionPlanForm from './ProductionPlanForm.vue';
import ProductionTimelinePanel from './ProductionTimelinePanel.vue';

const props = defineProps({
    visible: { type: Boolean, required: true },
    form: { type: Object, required: true },
    products: { type: Array, required: true },
    statuses: { type: Array, required: true },
    selectedPlan: { type: Object, default: null },
});

const emit = defineEmits(['update:visible', 'submit']);

const close = () => emit('update:visible', false);

const requirementRows = computed(() => props.selectedPlan?.details?.ingredient_requirements ?? []);
const summary = computed(() => props.selectedPlan?.details?.summary ?? null);
const timelineSteps = computed(() => props.selectedPlan?.details?.timeline_steps ?? []);
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        header="Gyartasi terv szerkesztese"
        :style="{ width: '68rem', maxWidth: '97vw' }"
        :content-style="{ maxHeight: '72vh', overflowY: 'auto' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <form id="production-plan-edit-form" class="space-y-4" @submit.prevent="emit('submit')">
            <ProductionPlanForm :form="form" :products="products" :statuses="statuses" mode="edit" />

            <div v-if="summary" class="grid gap-3 rounded-xl border border-bakery-brown/15 bg-[#fcf8f1] p-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg bg-white p-3">
                    <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/70">Aktív ido</p>
                    <p class="mt-1 text-lg font-semibold text-bakery-dark">{{ summary.total_active_minutes }} perc</p>
                </div>
                <div class="rounded-lg bg-white p-3">
                    <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/70">Várakozási ido</p>
                    <p class="mt-1 text-lg font-semibold text-bakery-dark">{{ summary.total_wait_minutes }} perc</p>
                </div>
                <div class="rounded-lg bg-white p-3">
                    <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/70">Teljes recept ido</p>
                    <p class="mt-1 text-lg font-semibold text-bakery-dark">{{ summary.total_recipe_minutes }} perc</p>
                </div>
                <div class="rounded-lg bg-white p-3">
                    <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/70">Hianyos alapanyagok</p>
                    <p class="mt-1 text-lg font-semibold text-bakery-dark">{{ summary.shortage_ingredients_count }} db</p>
                </div>
                <div class="rounded-lg bg-white p-3 sm:col-span-2 xl:col-span-4">
                    <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/70">Timeline</p>
                    <p class="mt-1 text-sm font-semibold text-bakery-dark">
                        {{ summary.timeline_steps_count }} lepes | dependency: {{ summary.dependency_steps_count }} |
                        kezdes: {{ summary.timeline_start_at ?? '-' }}
                    </p>
                </div>
            </div>

            <div v-if="requirementRows.length > 0" class="space-y-2 rounded-xl border border-bakery-brown/15 bg-white p-4">
                <h4 class="text-sm font-semibold text-bakery-dark">Osszesitett alapanyag igeny</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs uppercase tracking-[0.12em] text-bakery-brown/75">
                            <tr>
                                <th class="py-2 pr-2">Alapanyag</th>
                                <th class="py-2 pr-2">Szukseges</th>
                                <th class="py-2 pr-2">Keszlet</th>
                                <th class="py-2 pr-2">Hiany</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in requirementRows" :key="row.ingredient_id" class="border-t border-bakery-brown/10">
                                <td class="py-2 pr-2 font-medium text-bakery-dark">{{ row.name }}</td>
                                <td class="py-2 pr-2 text-bakery-dark/80">{{ row.total_required }} {{ row.unit }}</td>
                                <td class="py-2 pr-2 text-bakery-dark/80">{{ row.current_stock }} {{ row.unit }}</td>
                                <td class="py-2 pr-2" :class="row.shortage > 0 ? 'text-red-700 font-semibold' : 'text-bakery-dark/70'">
                                    {{ row.shortage }} {{ row.unit }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <ProductionTimelinePanel :steps="timelineSteps" />
        </form>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button type="button" severity="secondary" label="Mégse" @click="close" />
                <Button type="submit" form="production-plan-edit-form" label="Mentés" :loading="form.processing" />
            </div>
        </template>
    </Dialog>
</template>


