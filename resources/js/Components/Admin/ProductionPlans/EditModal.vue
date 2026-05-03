<script setup>
import { computed } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import { trans } from 'laravel-vue-i18n';
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
        :header="trans('admin_production_plans.modals.edit_title')"
        :style="{ width: '68rem', maxWidth: '97vw' }"
        :content-style="{ maxHeight: '72vh', overflowY: 'auto' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <form id="production-plan-edit-form" class="space-y-4" @submit.prevent="emit('submit')">
            <ProductionPlanForm :form="form" :products="products" :statuses="statuses" mode="edit" />

            <div v-if="summary" class="grid gap-3 rounded-xl border border-bakery-brown/15 bg-[#fcf8f1] p-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg bg-white p-3">
                    <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/70">{{ trans('admin_production_plans.summary.active_minutes') }}</p>
                    <p class="mt-1 text-lg font-semibold text-bakery-dark">{{ trans('admin_production_plans.units.minutes', { count: summary.total_active_minutes }) }}</p>
                </div>
                <div class="rounded-lg bg-white p-3">
                    <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/70">{{ trans('admin_production_plans.summary.wait_minutes') }}</p>
                    <p class="mt-1 text-lg font-semibold text-bakery-dark">{{ trans('admin_production_plans.units.minutes', { count: summary.total_wait_minutes }) }}</p>
                </div>
                <div class="rounded-lg bg-white p-3">
                    <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/70">{{ trans('admin_production_plans.summary.recipe_minutes') }}</p>
                    <p class="mt-1 text-lg font-semibold text-bakery-dark">{{ trans('admin_production_plans.units.minutes', { count: summary.total_recipe_minutes }) }}</p>
                </div>
                <div class="rounded-lg bg-white p-3">
                    <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/70">{{ trans('admin_production_plans.summary.shortage_ingredients') }}</p>
                    <p class="mt-1 text-lg font-semibold text-bakery-dark">{{ trans('admin_production_plans.units.pieces', { count: summary.shortage_ingredients_count }) }}</p>
                </div>
                <div class="rounded-lg bg-white p-3 sm:col-span-2 xl:col-span-4">
                    <p class="text-xs uppercase tracking-[0.14em] text-bakery-brown/70">{{ trans('admin_production_plans.timeline.title') }}</p>
                    <p class="mt-1 text-sm font-semibold text-bakery-dark">
                        {{ trans('admin_production_plans.timeline.summary', {
                            steps: summary.timeline_steps_count,
                            dependencies: summary.dependency_steps_count,
                            start: summary.timeline_start_at ?? '-',
                        }) }}
                    </p>
                </div>
            </div>

            <div v-if="requirementRows.length > 0" class="space-y-2 rounded-xl border border-bakery-brown/15 bg-white p-4">
                <h4 class="text-sm font-semibold text-bakery-dark">{{ trans('admin_production_plans.requirements.title') }}</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs uppercase tracking-[0.12em] text-bakery-brown/75">
                            <tr>
                                <th class="py-2 pr-2">{{ trans('admin_production_plans.requirements.ingredient') }}</th>
                                <th class="py-2 pr-2">{{ trans('admin_production_plans.requirements.required') }}</th>
                                <th class="py-2 pr-2">{{ trans('admin_production_plans.requirements.stock') }}</th>
                                <th class="py-2 pr-2">{{ trans('admin_production_plans.requirements.shortage') }}</th>
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
                <Button type="button" severity="secondary" :label="trans('common.cancel')" @click="close" />
                <Button type="submit" form="production-plan-edit-form" :label="trans('admin_production_plans.actions.save')" :loading="form.processing" />
            </div>
        </template>
    </Dialog>
</template>

