<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { trans } from "laravel-vue-i18n";

import EntityStatusBadge from "@/Components/Admin/Table/EntityStatusBadge.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineOptions({ layout: AdminLayout });

defineProps({
    plan: { type: Object, required: true },
});
</script>

<template>
    <Head :title="trans('admin.production_plans.show.meta_title', { number: plan.plan_number })" />

    <div class="space-y-6">
        <SectionTitle
            :eyebrow="trans('admin_production_plans.eyebrow')"
            :title="trans('admin.production_plans.show.title', { number: plan.plan_number })"
            :description="trans('admin.production_plans.show.description')"
        />

        <div class="grid gap-4 rounded-lg border border-bakery-brown/15 bg-white/85 p-4 sm:grid-cols-2 xl:grid-cols-4">
            <div>
                <p class="text-xs font-semibold uppercase text-bakery-brown/70">
                    {{ trans("common.status") }}
                </p>
                <EntityStatusBadge class="mt-2" :status="plan.status">{{ plan.status }}</EntityStatusBadge>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-bakery-brown/70">
                    {{ trans("admin.production_plans.flow.fields.target_ready_at") }}
                </p>
                <p class="mt-2 font-semibold text-bakery-dark">
                    {{ plan.target_ready_at }}
                </p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-bakery-brown/70">
                    {{ trans("admin_production_plans.summary.recipe_minutes") }}
                </p>
                <p class="mt-2 font-semibold text-bakery-dark">{{ plan.total_recipe_minutes }} min</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-bakery-brown/70">
                    {{ trans("admin.production_plans.flow.summary.warnings") }}
                </p>
                <p class="mt-2 font-semibold text-bakery-dark">
                    {{ plan.summary.shortage_ingredients_count }}
                </p>
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-2">
            <section class="rounded-lg border border-bakery-brown/15 bg-white/85 p-4">
                <h2 class="text-lg font-semibold text-bakery-dark">
                    {{ trans("admin.production_plans.flow.ingredients.title") }}
                </h2>
                <div class="mt-3 space-y-2">
                    <div
                        v-for="row in plan.ingredient_requirements"
                        :key="row.ingredient_id"
                        class="grid gap-2 rounded-lg bg-[#fcf8f1] p-3 text-sm sm:grid-cols-[minmax(0,1fr)_8rem_8rem]"
                    >
                        <span class="font-semibold text-bakery-dark">{{ row.name }}</span>
                        <span>{{ row.total_required }} {{ row.unit }}</span>
                        <span>{{ row.current_stock }} {{ row.unit }}</span>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-bakery-brown/15 bg-white/85 p-4">
                <h2 class="text-lg font-semibold text-bakery-dark">
                    {{ trans("admin.production_plans.flow.timeline.title") }}
                </h2>
                <div class="mt-3 space-y-3">
                    <article
                        v-for="step in plan.timeline_steps"
                        :key="step.id"
                        class="rounded-lg bg-[#fcf8f1] p-3 text-sm"
                    >
                        <p class="font-semibold text-bakery-dark">{{ step.title }}</p>
                        <p class="text-bakery-dark/65">{{ step.starts_at }} - {{ step.ends_at }}</p>
                        <p v-if="step.work_instruction" class="mt-1 text-bakery-dark/75">
                            {{ step.work_instruction }}
                        </p>
                    </article>
                </div>
            </section>
        </div>

        <Link
            :href="route('admin.production-plans.index')"
            class="inline-flex min-h-11 items-center rounded-lg border border-bakery-brown/20 px-4 py-2 text-sm font-semibold text-bakery-brown hover:bg-bakery-brown/10"
        >
            {{ trans("common.back_to_list") }}
        </Link>
    </div>
</template>
