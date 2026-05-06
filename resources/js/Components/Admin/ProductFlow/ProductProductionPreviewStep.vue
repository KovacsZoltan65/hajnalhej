<script setup>
import { computed } from "vue";

const props = defineProps({
    product: { type: Object, required: true },
    ingredients: { type: Array, required: true },
    recipeIngredients: { type: Array, required: true },
    recipeSteps: { type: Array, required: true },
    categories: { type: Array, required: true },
});

const totalMinutes = computed(() =>
    props.recipeSteps.reduce(
        (sum, step) => sum + Number(step.duration_minutes ?? 0) + Number(step.wait_minutes ?? 0),
        0
    )
);

const categoryName = computed(
    () => props.categories.find((category) => category.id === props.product.category_id)?.name ?? "-"
);

const ingredientById = (id) => props.ingredients.find((ingredient) => ingredient.id === id);
</script>

<template>
    <div class="grid gap-4 xl:grid-cols-3">
        <section class="rounded-lg border border-bakery-brown/10 bg-white p-4">
            <p class="text-xs font-semibold uppercase text-bakery-brown/70">
                {{ $t("admin.products.flow.preview.product") }}
            </p>
            <h3 class="mt-2 text-lg font-semibold text-bakery-dark">
                {{ product.name }}
            </h3>
            <p class="text-sm text-bakery-dark/65">{{ categoryName }} / {{ product.price }}</p>
        </section>
        <section class="rounded-lg border border-bakery-brown/10 bg-white p-4">
            <p class="text-xs font-semibold uppercase text-bakery-brown/70">
                {{ $t("admin.products.flow.preview.ingredients") }}
            </p>
            <ul class="mt-2 space-y-1 text-sm text-bakery-dark/75">
                <li v-for="row in recipeIngredients" :key="row.ingredient_id">
                    {{ ingredientById(row.ingredient_id)?.name }}: {{ row.quantity }}
                    {{ ingredientById(row.ingredient_id)?.unit }}
                </li>
            </ul>
        </section>
        <section class="rounded-lg border border-bakery-brown/10 bg-white p-4">
            <p class="text-xs font-semibold uppercase text-bakery-brown/70">
                {{ $t("admin.products.flow.preview.production") }}
            </p>
            <p class="mt-2 text-2xl font-semibold text-bakery-dark">{{ totalMinutes }} min</p>
            <p class="text-sm text-bakery-dark/65">
                {{ $t("admin.products.flow.preview.total_time") }}
            </p>
        </section>
        <section class="rounded-lg border border-bakery-brown/10 bg-white p-4 xl:col-span-3">
            <p class="text-xs font-semibold uppercase text-bakery-brown/70">
                {{ $t("admin.products.flow.preview.steps") }}
            </p>
            <ol class="mt-2 grid gap-2 md:grid-cols-2">
                <li v-for="(step, index) in recipeSteps" :key="index" class="rounded-md bg-[#fcf7ef] p-3 text-sm">
                    <strong>{{ index + 1 }}. {{ step.title }}</strong>
                    <p class="text-bakery-dark/65">{{ step.duration_minutes }} min</p>
                </li>
            </ol>
        </section>
    </div>
</template>
