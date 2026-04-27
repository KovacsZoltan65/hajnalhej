<script setup>
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import RecipeIngredientList from "./RecipeIngredientList.vue";
import RecipeStepList from "./RecipeStepList.vue";

defineProps({
    visible: { type: Boolean, required: true },
    recipe: { type: Object, default: null },
});

const emit = defineEmits([
    "update:visible",
    "open-ingredient-create",
    "open-ingredient-edit",
    "delete-ingredient",
    "open-step-create",
    "open-step-edit",
    "delete-step",
]);
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :header="recipe ? `Recept szerkesztés: ${recipe.name}` : 'Recept szerkesztése'"
        :style="{ width: '70rem', maxWidth: '98vw' }"
        :content-style="{ maxHeight: '70vh', overflowY: 'auto' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <div class="space-y-6">
            <div
                class="grid gap-2 rounded-2xl border border-bakery-brown/10 bg-[#fcf7ef] p-3 sm:grid-cols-2"
            >
                <p class="text-sm text-bakery-dark/80">
                    Kategória:
                    <span class="font-medium text-bakery-dark">{{
                        recipe?.category_name ?? "Nincs"
                    }}</span>
                </p>
                <p class="text-sm text-bakery-dark/80">
                    Alacsony készlet érintett:
                    <span class="font-medium text-bakery-dark">{{
                        recipe?.low_stock_ingredients_count ?? 0
                    }}</span>
                </p>
            </div>

            <section class="rounded-2xl border border-bakery-brown/15 bg-white/85 p-4">
                <div class="mb-3 flex items-center justify-between">
                    <h4 class="font-medium text-bakery-dark">Hozzávalók</h4>
                    <Button
                        icon="pi pi-plus"
                        label="Új hozzávaló"
                        size="small"
                        @click="emit('open-ingredient-create')"
                    />
                </div>
                <RecipeIngredientList
                    :items="recipe?.product_ingredients ?? []"
                    @edit="(item) => emit('open-ingredient-edit', item)"
                    @delete="(item) => emit('delete-ingredient', item)"
                />
            </section>

            <section class="rounded-2xl border border-bakery-brown/15 bg-white/85 p-4">
                <div class="mb-3 flex items-center justify-between">
                    <h4 class="font-medium text-bakery-dark">
                        Recept lépések és időzítés
                    </h4>
                    <Button
                        icon="pi pi-plus"
                        label="Új lépés"
                        size="small"
                        @click="emit('open-step-create')"
                    />
                </div>
                <RecipeStepList
                    :steps="recipe?.recipe_steps ?? []"
                    @edit="(step) => emit('open-step-edit', step)"
                    @delete="(step) => emit('delete-step', step)"
                />
            </section>

            <section class="rounded-2xl border border-bakery-brown/15 bg-[#fcf7ef] p-4">
                <h4 class="font-medium text-bakery-dark">Összegzés</h4>
                <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                    <p class="text-sm text-bakery-dark/80">
                        Hozzavalok:
                        <span class="font-semibold text-bakery-dark">{{
                            recipe?.recipe_summary?.ingredients_count ?? 0
                        }}</span>
                    </p>
                    <p class="text-sm text-bakery-dark/80">
                        Lépések:
                        <span class="font-semibold text-bakery-dark">{{
                            recipe?.recipe_summary?.steps_count ?? 0
                        }}</span>
                    </p>
                    <p class="text-sm text-bakery-dark/80">
                        Aktív idő:
                        <span class="font-semibold text-bakery-dark"
                            >{{
                                recipe?.recipe_summary?.total_active_minutes ?? 0
                            }}
                            p</span
                        >
                    </p>
                    <p class="text-sm text-bakery-dark/80">
                        Várakozás:
                        <span class="font-semibold text-bakery-dark"
                            >{{ recipe?.recipe_summary?.total_wait_minutes ?? 0 }} p</span
                        >
                    </p>
                    <p class="text-sm text-bakery-dark/80">
                        Teljes idő:
                        <span class="font-semibold text-bakery-dark"
                            >{{
                                recipe?.recipe_summary?.total_recipe_minutes ?? 0
                            }}
                            p</span
                        >
                    </p>
                </div>
            </section>
        </div>
        <template #footer>
            <div class="flex justify-end">
                <Button
                    type="button"
                    severity="secondary"
                    label="Bezárás"
                    @click="emit('update:visible', false)"
                />
            </div>
        </template>
    </Dialog>
</template>
