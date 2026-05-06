<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import Button from "primevue/button";
import DatePicker from "primevue/datepicker";
import InputNumber from "primevue/inputnumber";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import { trans } from "laravel-vue-i18n";

import AdminTableEmptyState from "@/Components/Admin/Table/AdminTableEmptyState.vue";
import EntityStatusBadge from "@/Components/Admin/Table/EntityStatusBadge.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineOptions({ layout: AdminLayout });

const props = defineProps({
    products: { type: Array, required: true },
    statuses: { type: Array, required: true },
});

const step = ref(1);

const makeDefaultItem = () => ({
    product_id: props.products[0]?.id ?? null,
    target_quantity: 1,
    unit_label: props.products[0]?.unit_label ?? "db",
    sort_order: 0,
});

const form = useForm({
    target_ready_at: null,
    notes: "",
    items: props.products.length > 0 ? [makeDefaultItem()] : [],
});

const steps = computed(() => [
    { index: 1, label: trans("admin.production_plans.flow.steps.products") },
    { index: 2, label: trans("admin.production_plans.flow.steps.target") },
    { index: 3, label: trans("admin.production_plans.flow.steps.ingredients") },
    { index: 4, label: trans("admin.production_plans.flow.steps.timeline") },
]);

const productById = (id) => props.products.find((product) => product.id === id);

const selectedProductIds = computed(() => form.items.map((item) => Number(item.product_id)).filter(Boolean));

const availableProducts = computed(() =>
    props.products.filter((product) => !selectedProductIds.value.includes(product.id))
);

const selectedItems = computed(() =>
    form.items
        .map((item, index) => ({
            ...item,
            sort_order: index,
            product: productById(item.product_id),
            target_quantity: Number(item.target_quantity ?? 0),
        }))
        .filter((item) => item.product)
);

const ingredientRequirements = computed(() => {
    const rows = {};

    selectedItems.value.forEach((item) => {
        item.product.product_ingredients.forEach((ingredient) => {
            if (!rows[ingredient.ingredient_id]) {
                rows[ingredient.ingredient_id] = {
                    ingredient_id: ingredient.ingredient_id,
                    name: ingredient.ingredient_name,
                    unit: ingredient.ingredient_unit,
                    total_required: 0,
                    current_stock: Number(ingredient.current_stock ?? 0),
                    minimum_stock: Number(ingredient.minimum_stock ?? 0),
                };
            }

            rows[ingredient.ingredient_id].total_required += Number(ingredient.quantity ?? 0) * item.target_quantity;
        });
    });

    return Object.values(rows)
        .map((row) => {
            const shortage = Math.max(0, row.total_required - row.current_stock);

            return {
                ...row,
                total_required: Number(row.total_required.toFixed(3)),
                shortage: Number(shortage.toFixed(3)),
                is_low_stock: row.current_stock <= row.minimum_stock,
                is_insufficient: shortage > 0,
            };
        })
        .sort((left, right) => right.shortage - left.shortage);
});

const targetDate = computed(() => {
    if (!form.target_ready_at) {
        return null;
    }

    return form.target_ready_at instanceof Date ? form.target_ready_at : new Date(form.target_ready_at);
});

const timelineRows = computed(() => {
    if (!targetDate.value) {
        return [];
    }

    const rows = [];

    selectedItems.value.forEach((item) => {
        let cursor = new Date(targetDate.value);
        const quantityFactor = Math.max(1, Math.ceil(item.target_quantity));
        const reversedSteps = [...item.product.recipe_steps].reverse();

        reversedSteps.forEach((recipeStep) => {
            const duration =
                (Number(recipeStep.duration_minutes ?? 0) + Number(recipeStep.wait_minutes ?? 0)) * quantityFactor;
            const totalMinutes = Math.max(1, duration);
            const startsAt = new Date(cursor.getTime() - totalMinutes * 60000);

            rows.push({
                product_id: item.product.id,
                product_name: item.product.name,
                title: recipeStep.title,
                work_instruction: recipeStep.work_instruction,
                starts_at: startsAt,
                ends_at: new Date(cursor),
                duration_minutes: totalMinutes,
            });

            cursor = startsAt;
        });
    });

    return rows.sort((left, right) => left.starts_at - right.starts_at);
});

const warningsCount = computed(
    () => ingredientRequirements.value.filter((row) => row.is_low_stock || row.is_insufficient).length
);

const formatForBackend = (value) => {
    if (!value) {
        return null;
    }

    const date = value instanceof Date ? value : new Date(value);
    const pad = (part) => String(part).padStart(2, "0");

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(
        date.getDate()
    )} ${pad(date.getHours())}:${pad(date.getMinutes())}:00`;
};

const canContinue = computed(() => {
    if (step.value === 1) {
        return selectedItems.value.length > 0 && selectedItems.value.every((item) => item.target_quantity > 0);
    }

    if (step.value === 2) {
        return Boolean(form.target_ready_at);
    }

    return true;
});

const addItem = () => {
    const product = availableProducts.value[0];

    if (!product) {
        return;
    }

    form.items.push({
        product_id: product.id,
        target_quantity: 1,
        unit_label: product.unit_label ?? "db",
        sort_order: form.items.length,
    });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const next = () => {
    if (!canContinue.value) {
        return;
    }

    step.value = Math.min(4, step.value + 1);
};

const previous = () => {
    step.value = Math.max(1, step.value - 1);
};

const submit = () => {
    form.transform(() => ({
        target_ready_at: formatForBackend(form.target_ready_at),
        notes: form.notes,
        items: form.items.map((item, index) => ({
            product_id: item.product_id,
            target_quantity: item.target_quantity,
            unit_label: item.unit_label || "db",
            sort_order: index,
        })),
    })).post(route("admin.production-plans.create-flow.store"), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="trans('admin.production_plans.flow.meta_title')" />

    <div class="space-y-6">
        <SectionTitle
            :eyebrow="trans('admin.production_plans.flow.eyebrow')"
            :title="trans('admin.production_plans.flow.title')"
            :description="trans('admin.production_plans.flow.description')"
        />

        <div class="grid gap-5 xl:grid-cols-[15rem_minmax(0,1fr)_20rem]">
            <aside class="rounded-lg border border-bakery-brown/15 bg-white/85 p-3">
                <button
                    v-for="item in steps"
                    :key="item.index"
                    type="button"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-3 text-left text-sm font-semibold"
                    :class="
                        item.index === step ? 'bg-bakery-brown text-white' : 'text-bakery-dark hover:bg-bakery-brown/10'
                    "
                    @click="step = item.index"
                >
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-white/20">
                        {{ item.index }}
                    </span>
                    {{ item.label }}
                </button>
            </aside>

            <main class="rounded-lg border border-bakery-brown/15 bg-white/85 p-4 shadow-sm sm:p-5">
                <section v-if="step === 1" class="space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-bakery-dark">
                            {{ trans("admin.production_plans.flow.products.title") }}
                        </h2>
                        <Button
                            icon="pi pi-plus"
                            :label="trans('admin.production_plans.flow.actions.add_product')"
                            :disabled="availableProducts.length === 0"
                            @click="addItem"
                        />
                    </div>

                    <AdminTableEmptyState
                        v-if="products.length === 0"
                        :title="trans('admin.production_plans.flow.empty.products_title')"
                        :description="trans('admin.production_plans.flow.empty.products_description')"
                    />

                    <div
                        v-for="(item, index) in form.items"
                        v-else
                        :key="`plan-flow-item-${index}`"
                        class="grid gap-3 rounded-lg border border-bakery-brown/10 bg-[#fcf8f1] p-3 md:grid-cols-[minmax(0,1fr)_10rem_auto]"
                    >
                        <div class="space-y-1">
                            <label class="text-xs font-semibold uppercase text-bakery-brown/80">
                                {{ trans("common.product") }}
                            </label>
                            <Select
                                v-model="item.product_id"
                                :options="[productById(item.product_id), ...availableProducts].filter(Boolean)"
                                option-label="name"
                                option-value="id"
                                class="w-full"
                            />
                            <p v-if="form.errors[`items.${index}.product_id`]" class="text-xs text-red-700">
                                {{ form.errors[`items.${index}.product_id`] }}
                            </p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-semibold uppercase text-bakery-brown/80">
                                {{ trans("common.quantity") }}
                            </label>
                            <InputNumber
                                v-model="item.target_quantity"
                                :min="0"
                                :min-fraction-digits="0"
                                :max-fraction-digits="3"
                                class="w-full"
                            />
                            <p v-if="form.errors[`items.${index}.target_quantity`]" class="text-xs text-red-700">
                                {{ form.errors[`items.${index}.target_quantity`] }}
                            </p>
                        </div>
                        <div class="flex items-end justify-end">
                            <Button
                                icon="pi pi-trash"
                                text
                                rounded
                                severity="danger"
                                :disabled="form.items.length <= 1"
                                @click="removeItem(index)"
                            />
                        </div>
                    </div>
                    <p v-if="form.errors.items" class="text-xs text-red-700">
                        {{ form.errors.items }}
                    </p>
                </section>

                <section v-else-if="step === 2" class="space-y-4">
                    <h2 class="text-lg font-semibold text-bakery-dark">
                        {{ trans("admin.production_plans.flow.target.title") }}
                    </h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-1">
                            <label class="text-xs font-semibold uppercase text-bakery-brown/80">
                                {{ trans("admin.production_plans.flow.fields.target_ready_at") }}
                            </label>
                            <DatePicker v-model="form.target_ready_at" show-time hour-format="24" class="w-full" />
                            <p v-if="form.errors.target_ready_at" class="text-xs text-red-700">
                                {{ form.errors.target_ready_at }}
                            </p>
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-xs font-semibold uppercase text-bakery-brown/80">
                                {{ trans("admin.production_plans.flow.fields.notes") }}
                            </label>
                            <Textarea v-model="form.notes" rows="4" class="w-full" />
                        </div>
                    </div>
                </section>

                <section v-else-if="step === 3" class="space-y-4">
                    <h2 class="text-lg font-semibold text-bakery-dark">
                        {{ trans("admin.production_plans.flow.ingredients.title") }}
                    </h2>
                    <AdminTableEmptyState
                        v-if="ingredientRequirements.length === 0"
                        :title="trans('admin.production_plans.flow.empty.ingredients_title')"
                        :description="trans('admin.production_plans.flow.empty.ingredients_description')"
                    />
                    <div v-else class="overflow-hidden rounded-lg border border-bakery-brown/10">
                        <div
                            v-for="row in ingredientRequirements"
                            :key="row.ingredient_id"
                            class="grid gap-3 border-b border-bakery-brown/10 bg-white p-3 text-sm last:border-b-0 md:grid-cols-[minmax(0,1fr)_8rem_8rem_8rem]"
                        >
                            <div>
                                <p class="font-semibold text-bakery-dark">
                                    {{ row.name }}
                                </p>
                                <EntityStatusBadge v-if="row.is_insufficient" status="out_of_stock">
                                    {{ trans("admin.production_plans.flow.warnings.insufficient") }}
                                </EntityStatusBadge>
                                <EntityStatusBadge v-else-if="row.is_low_stock" status="draft">
                                    {{ trans("admin.production_plans.flow.warnings.low_stock") }}
                                </EntityStatusBadge>
                            </div>
                            <span>{{ row.total_required }} {{ row.unit }}</span>
                            <span>{{ row.current_stock }} {{ row.unit }}</span>
                            <span>{{ row.shortage }} {{ row.unit }}</span>
                        </div>
                    </div>
                </section>

                <section v-else class="space-y-4">
                    <h2 class="text-lg font-semibold text-bakery-dark">
                        {{ trans("admin.production_plans.flow.timeline.title") }}
                    </h2>
                    <AdminTableEmptyState
                        v-if="timelineRows.length === 0"
                        :title="trans('admin.production_plans.flow.empty.timeline_title')"
                        :description="trans('admin.production_plans.flow.empty.timeline_description')"
                    />
                    <div v-else class="space-y-3">
                        <article
                            v-for="(row, index) in timelineRows"
                            :key="`${row.product_id}-${row.title}-${index}`"
                            class="rounded-lg border border-bakery-brown/10 bg-white p-3"
                        >
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase text-bakery-brown/70">
                                        {{ row.product_name }}
                                    </p>
                                    <h3 class="font-semibold text-bakery-dark">
                                        {{ row.title }}
                                    </h3>
                                </div>
                                <p class="text-sm font-semibold text-bakery-dark">{{ row.duration_minutes }} min</p>
                            </div>
                            <p class="mt-2 text-sm text-bakery-dark/70">
                                {{ row.starts_at.toLocaleString() }} -
                                {{ row.ends_at.toLocaleString() }}
                            </p>
                            <p v-if="row.work_instruction" class="mt-2 text-sm text-bakery-dark/75">
                                {{ row.work_instruction }}
                            </p>
                        </article>
                    </div>
                </section>

                <div
                    class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-bakery-brown/10 pt-4"
                >
                    <Button
                        :label="trans('admin.production_plans.flow.actions.previous')"
                        icon="pi pi-arrow-left"
                        outlined
                        :disabled="step === 1 || form.processing"
                        @click="previous"
                    />
                    <div class="flex items-center gap-2">
                        <Button
                            v-if="step < 4"
                            :label="trans('admin.production_plans.flow.actions.next')"
                            icon="pi pi-arrow-right"
                            icon-pos="right"
                            :disabled="!canContinue || form.processing"
                            @click="next"
                        />
                        <Button
                            v-else
                            icon="pi pi-check"
                            :label="trans('admin.production_plans.flow.actions.save')"
                            :loading="form.processing"
                            :disabled="selectedItems.length === 0 || !form.target_ready_at"
                            @click="submit"
                        />
                    </div>
                </div>
            </main>

            <aside class="space-y-3 rounded-lg border border-bakery-brown/15 bg-white/85 p-4">
                <h2 class="text-base font-semibold text-bakery-dark">
                    {{ trans("admin.production_plans.flow.summary.title") }}
                </h2>
                <div class="space-y-2 text-sm text-bakery-dark/75">
                    <p>
                        <strong class="text-bakery-dark">{{ selectedItems.length }}</strong>
                        {{ trans("admin.production_plans.flow.summary.products") }}
                    </p>
                    <p>
                        <strong class="text-bakery-dark">{{ ingredientRequirements.length }}</strong>
                        {{ trans("admin.production_plans.flow.summary.ingredients") }}
                    </p>
                    <p>
                        <strong class="text-bakery-dark">{{ warningsCount }}</strong>
                        {{ trans("admin.production_plans.flow.summary.warnings") }}
                    </p>
                </div>
                <div class="border-t border-bakery-brown/10 pt-3">
                    <p class="text-xs font-semibold uppercase text-bakery-brown/70">
                        {{ trans("admin.production_plans.flow.summary.selected_products") }}
                    </p>
                    <ul class="mt-2 space-y-1 text-sm text-bakery-dark/75">
                        <li v-for="item in selectedItems" :key="item.product_id">
                            {{ item.product.name }} / {{ item.target_quantity }}
                            {{ item.unit_label }}
                        </li>
                    </ul>
                </div>
                <div class="border-t border-bakery-brown/10 pt-3">
                    <p class="text-xs font-semibold uppercase text-bakery-brown/70">
                        {{ trans("admin.production_plans.flow.summary.target_ready_at") }}
                    </p>
                    <p class="mt-1 text-sm text-bakery-dark/75">
                        {{ targetDate ? targetDate.toLocaleString() : "-" }}
                    </p>
                </div>
                <Link
                    :href="route('admin.production-plans.index')"
                    class="inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-bakery-brown/20 px-4 py-2 text-sm font-semibold text-bakery-brown hover:bg-bakery-brown/10"
                >
                    {{ trans("common.back_to_list") }}
                </Link>
            </aside>
        </div>
    </div>
</template>
