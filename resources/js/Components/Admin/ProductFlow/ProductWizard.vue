<script setup>
import { computed, reactive, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import Stepper from "primevue/stepper";
import StepList from "primevue/steplist";
import Step from "primevue/step";
import StepPanels from "primevue/steppanels";
import StepPanel from "primevue/steppanel";
import ProductBasicsStep from "./ProductBasicsStep.vue";
import ProductRecipeStep from "./ProductRecipeStep.vue";
import ProductRecipeStepsStep from "./ProductRecipeStepsStep.vue";
import ProductProductionPreviewStep from "./ProductProductionPreviewStep.vue";
import WizardFooter from "./WizardFooter.vue";

const props = defineProps({
    categories: { type: Array, required: true },
    ingredients: { type: Array, required: true },
    stockStatuses: { type: Array, required: true },
});

const step = ref("1");
const localErrors = reactive({});

let product = reactive({
    category_id: props.categories[0]?.id ?? null,
    name: "",
    slug: "",
    short_description: "",
    description: "",
    price: 0,
    is_active: true,
    is_featured: false,
    stock_status: props.stockStatuses[0]?.value ?? "in_stock",
    image_path: "",
    sort_order: 0,
});

const recipeIngredients = ref([]);
const recipeSteps = ref([]);

const form = useForm({
    product,
    ingredients: recipeIngredients,
    recipe_steps: recipeSteps,
});

const steps = computed(() => [
    { value: "1", label: "admin.products.flow.steps.basics" },
    { value: "2", label: "admin.products.flow.steps.recipe" },
    { value: "3", label: "admin.products.flow.steps.recipe_steps" },
    { value: "4", label: "admin.products.flow.steps.production_preview" },
]);

const currentIndex = computed(() => steps.value.findIndex((item) => item.value === step.value));
const isLast = computed(() => step.value === "4");

const clearLocalErrors = () => {
    Object.keys(localErrors).forEach((key) => delete localErrors[key]);
};

const validateCurrent = () => {
    clearLocalErrors();

    if (step.value === "1") {
        if (!product.name) {
            localErrors["product.name"] = "admin.products.flow.validation.name";
        }
        if (!product.category_id) {
            localErrors["product.category_id"] = "admin.products.flow.validation.category";
        }
        if (Number(product.price) < 0) {
            localErrors["product.price"] = "admin.products.flow.validation.price";
        }
    }

    if (step.value === "2" && recipeIngredients.value.length === 0) {
        localErrors.ingredients = "admin.products.flow.validation.ingredients";
    }

    if (step.value === "3" && recipeSteps.value.length === 0) {
        localErrors.recipe_steps = "admin.products.flow.validation.steps";
    }

    return Object.keys(localErrors).length === 0;
};

const translatedErrors = computed(() =>
    Object.fromEntries(
        Object.entries({ ...localErrors, ...form.errors }).map(([key, value]) => [
            key,
            value?.startsWith?.("admin.") ? value : value,
        ])
    )
);

const goNext = () => {
    if (!validateCurrent()) {
        return;
    }

    step.value = steps.value[Math.min(currentIndex.value + 1, steps.value.length - 1)].value;
};

const goBack = () => {
    clearLocalErrors();
    step.value = steps.value[Math.max(currentIndex.value - 1, 0)].value;
};

const submit = () => {
    if (!validateCurrent()) {
        return;
    }

    form.transform(() => ({
        product: { ...product },
        ingredients: recipeIngredients.value,
        recipe_steps: recipeSteps.value,
    })).post(route("admin.products.create-flow.store"), {
        preserveScroll: true,
    });
};
</script>

<template>
    <section class="rounded-lg border border-bakery-brown/15 bg-white/85 p-4 shadow-sm sm:p-5">
        <Stepper v-model:value="step" linear>
            <StepList>
                <Step v-for="item in steps" :key="item.value" :value="item.value">
                    {{ $t(item.label) }}
                </Step>
            </StepList>
            <StepPanels>
                <StepPanel value="1">
                    <ProductBasicsStep
                        v-model="product"
                        :categories="categories"
                        :stock-statuses="stockStatuses"
                        :errors="translatedErrors"
                    />
                </StepPanel>
                <StepPanel value="2">
                    <ProductRecipeStep
                        v-model="recipeIngredients"
                        :ingredients="ingredients"
                        :errors="translatedErrors"
                    />
                </StepPanel>
                <StepPanel value="3">
                    <ProductRecipeStepsStep v-model="recipeSteps" />
                    <p v-if="translatedErrors.recipe_steps" class="mt-2 text-xs text-red-700">
                        {{ $t(translatedErrors.recipe_steps) }}
                    </p>
                </StepPanel>
                <StepPanel value="4">
                    <ProductProductionPreviewStep
                        :product="product"
                        :ingredients="ingredients"
                        :recipe-ingredients="recipeIngredients"
                        :recipe-steps="recipeSteps"
                        :categories="categories"
                    />
                </StepPanel>
            </StepPanels>
        </Stepper>
        <div class="mt-4">
            <WizardFooter
                :can-go-back="currentIndex > 0"
                :is-last="isLast"
                :saving="form.processing"
                @back="goBack"
                @next="goNext"
                @submit="submit"
            />
        </div>
    </section>
</template>
