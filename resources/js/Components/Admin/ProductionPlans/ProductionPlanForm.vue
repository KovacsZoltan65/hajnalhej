<script setup>
import { computed } from "vue";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import ToggleSwitch from "primevue/toggleswitch";
import { trans } from "laravel-vue-i18n";
import BaseDatePicker from "@/Components/BaseDatePicker.vue";

const props = defineProps({
    form: { type: Object, required: true },
    products: { type: Array, required: true },
    statuses: { type: Array, required: true },
    mode: { type: String, default: "create" },
});

const canRemoveRows = computed(() => (props.form.items?.length ?? 0) > 1);

const selectedProducts = computed(() =>
    (props.form.items ?? [])
        .map((item) => props.products.find((product) => Number(product.id) === Number(item.product_id)))
        .filter(Boolean)
);

const recipeDurationMinutes = (product) =>
    (product?.recipe_steps ?? []).reduce(
        (total, recipeStep) => total + Number(recipeStep.duration_minutes ?? 0) + Number(recipeStep.wait_minutes ?? 0),
        0
    );

const longestRecipeDurationMinutes = computed(() => {
    const longest = selectedProducts.value.reduce(
        (maxDuration, product) => Math.max(maxDuration, recipeDurationMinutes(product)),
        0
    );

    return Math.max(15, longest);
});

const minTargetReadyAt = computed(() => {
    const date = new Date();
    date.setMinutes(date.getMinutes() + longestRecipeDurationMinutes.value);

    return date;
});

const formatDateTime = (value) =>
    new Intl.DateTimeFormat(undefined, {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(value);

const earliestReadyAtText = computed(() =>
    trans("admin.production_plans.flow.target.earliest_ready_at", {
        datetime: formatDateTime(minTargetReadyAt.value),
    })
);

const createEmptyPlanItem = () => ({
    product_id: null,
    target_quantity: null,
    unit_label: "",
    sort_order: null,
});

const addItemRow = () => {
    props.form.items.push(createEmptyPlanItem());
};

const removeItemRow = (index) => {
    if ((props.form.items?.length ?? 0) <= 1) {
        return;
    }

    props.form.items.splice(index, 1);
};

const ingredientName = (ingredient) => ingredient.ingredient_name ?? ingredient.name ?? "";
const ingredientUnit = (ingredient) => ingredient.ingredient_unit ?? ingredient.unit ?? "";

const ingredientRequirements = computed(() => {
    const rows = new Map();

    (props.form.items ?? []).forEach((item) => {
        const quantity = Number(item.target_quantity ?? 0);

        if (!item.product_id || !quantity) {
            return;
        }

        const product = props.products.find((option) => Number(option.id) === Number(item.product_id));

        (product?.product_ingredients ?? []).forEach((ingredient) => {
            const ingredientId = Number(ingredient.ingredient_id ?? ingredient.id ?? 0);

            if (!ingredientId) {
                return;
            }

            const required = Number(ingredient.quantity ?? 0) * quantity;

            if (!rows.has(ingredientId)) {
                rows.set(ingredientId, {
                    ingredient_id: ingredientId,
                    name: ingredientName(ingredient),
                    unit: ingredientUnit(ingredient),
                    total_required: 0,
                    current_stock: Number(ingredient.current_stock ?? 0),
                    minimum_stock: Number(ingredient.minimum_stock ?? 0),
                });
            }

            rows.get(ingredientId).total_required += required;
        });
    });

    return Array.from(rows.values())
        .map((row) => {
            const shortage = Math.max(0, row.total_required - row.current_stock);

            return {
                ...row,
                total_required: Number(row.total_required.toFixed(3)),
                current_stock: Number(row.current_stock.toFixed(3)),
                minimum_stock: Number(row.minimum_stock.toFixed(3)),
                shortage: Number(shortage.toFixed(3)),
                is_low_stock: row.current_stock <= row.minimum_stock,
                is_insufficient: shortage > 0,
            };
        })
        .sort((left, right) => right.shortage - left.shortage || left.name.localeCompare(right.name));
});
</script>

<template>
    <div class="space-y-5">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
                <label class="text-sm font-medium text-bakery-dark">{{
                    trans("admin_production_plans.form.target_ready_at")
                }}</label>
                <BaseDatePicker
                    v-model="form.target_ready_at"
                    mode="datetime"
                    output-type="date"
                    :min-date="minTargetReadyAt"
                    class="w-full"
                />
                <p v-if="form.errors.target_ready_at" class="text-xs text-red-700">
                    {{ form.errors.target_ready_at }}
                </p>
                <p v-if="form.errors.target_at" class="text-xs text-red-700">
                    {{ form.errors.target_at }}
                </p>
                <p class="text-xs text-bakery-dark/60">
                    {{ earliestReadyAtText }}
                </p>
            </div>

            <div v-if="mode === 'edit'" class="space-y-2">
                <label class="text-sm font-medium text-bakery-dark">{{
                    trans("admin_production_plans.form.status")
                }}</label>
                <Select
                    v-model="form.status"
                    :options="statuses"
                    option-label="label"
                    option-value="value"
                    class="w-full"
                />
                <p v-if="form.errors.status" class="text-xs text-red-700">
                    {{ form.errors.status }}
                </p>
            </div>

            <div v-if="mode === 'edit'" class="flex items-center gap-2 pt-7">
                <ToggleSwitch v-model="form.is_locked" />
                <label class="text-sm text-bakery-dark/80">{{ trans("admin_production_plans.form.lock_plan") }}</label>
            </div>

            <div class="space-y-2 md:col-span-2">
                <label class="text-sm font-medium text-bakery-dark">{{
                    trans("admin_production_plans.form.notes")
                }}</label>
                <Textarea v-model="form.notes" rows="3" auto-resize class="w-full" />
                <p v-if="form.errors.notes" class="text-xs text-red-700">
                    {{ form.errors.notes }}
                </p>
            </div>
        </div>

        <div class="space-y-3 rounded-xl border border-bakery-brown/15 bg-[#fcf8f1] p-4">
            <div class="flex items-center justify-between gap-2">
                <h4 class="text-sm font-semibold text-bakery-dark">
                    {{ trans("admin_production_plans.form.items_title") }}
                </h4>
                <Button
                    type="button"
                    icon="pi pi-plus"
                    :label="trans('admin_production_plans.form.add_item')"
                    size="small"
                    @click="addItemRow"
                />
            </div>

            <div
                v-for="(item, index) in form.items"
                :key="`plan-item-${index}`"
                class="grid gap-3 rounded-lg border border-bakery-brown/10 bg-white p-3 md:grid-cols-[minmax(0,1fr)_8rem_6rem_5rem_auto]"
            >
                <!-- TERMÉK -->
                <div class="space-y-1">
                    <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">{{
                        trans("common.product")
                    }}</label>
                    <Select
                        v-model="item.product_id"
                        :options="products"
                        option-label="name"
                        option-value="id"
                        class="w-full"
                    />
                    <p v-if="form.errors[`items.${index}.product_id`]" class="text-xs text-red-700">
                        {{ form.errors[`items.${index}.product_id`] }}
                    </p>
                </div>

                <!-- MENNYISÉG -->
                <div class="space-y-1">
                    <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">{{
                        trans("common.quantity")
                    }}</label>
                    <InputText v-model="item.target_quantity" type="number" min="1" step="1" class="w-full" />
                    <p v-if="form.errors[`items.${index}.target_quantity`]" class="text-xs text-red-700">
                        {{ form.errors[`items.${index}.target_quantity`] }}
                    </p>
                </div>

                <!-- MENNYISÉG -->
                <div class="space-y-1">
                    <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">{{
                        trans("admin_production_plans.form.unit")
                    }}</label>
                    <InputText v-model="item.unit_label" class="w-full" />
                    <p v-if="form.errors[`items.${index}.unit_label`]" class="text-xs text-red-700">
                        {{ form.errors[`items.${index}.unit_label`] }}
                    </p>
                </div>

                <!-- SORREND -->
                <div class="space-y-1">
                    <label class="text-xs font-medium uppercase tracking-[0.14em] text-bakery-brown/80">{{
                        trans("admin_production_plans.form.sort_order")
                    }}</label>
                    <InputText v-model="item.sort_order" type="number" min="0" step="1" class="w-full" />
                </div>

                <div class="flex items-end justify-end">
                    <Button
                        type="button"
                        icon="pi pi-trash"
                        size="small"
                        text
                        rounded
                        severity="danger"
                        :disabled="!canRemoveRows"
                        @click="removeItemRow(index)"
                    />
                </div>
            </div>
        </div>

        <div
            v-if="ingredientRequirements.length > 0"
            data-test="ingredient-requirements"
            class="space-y-2 rounded-xl border border-bakery-brown/15 bg-white p-4"
        >
            <h4 class="text-sm font-semibold text-bakery-dark">
                {{ trans("admin_production_plans.requirements.title") }}
            </h4>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-[0.12em] text-bakery-brown/75">
                        <tr>
                            <th class="py-2 pr-2">{{ trans("admin_production_plans.requirements.ingredient") }}</th>
                            <th class="py-2 pr-2">{{ trans("admin_production_plans.requirements.required") }}</th>
                            <th class="py-2 pr-2">{{ trans("admin_production_plans.requirements.stock") }}</th>
                            <th class="py-2 pr-2">{{ trans("admin_production_plans.requirements.shortage") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in ingredientRequirements"
                            :key="row.ingredient_id"
                            data-test="ingredient-requirement-row"
                            class="border-t border-bakery-brown/10"
                        >
                            <td class="py-2 pr-2 font-medium text-bakery-dark">
                                {{ row.name }}
                                <span v-if="row.is_insufficient" class="ml-2 text-xs font-semibold text-red-700">
                                    {{ trans("admin.production_plans.flow.warnings.insufficient") }}
                                </span>
                                <span v-else-if="row.is_low_stock" class="ml-2 text-xs font-semibold text-amber-700">
                                    {{ trans("admin.production_plans.flow.warnings.low_stock") }}
                                </span>
                            </td>
                            <td class="py-2 pr-2 text-bakery-dark/80">{{ row.total_required }} {{ row.unit }}</td>
                            <td class="py-2 pr-2 text-bakery-dark/80">{{ row.current_stock }} {{ row.unit }}</td>
                            <td
                                class="py-2 pr-2"
                                :class="row.shortage > 0 ? 'font-semibold text-red-700' : 'text-bakery-dark/70'"
                            >
                                {{ row.shortage }} {{ row.unit }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
