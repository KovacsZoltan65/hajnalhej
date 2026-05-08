<script setup>
import Checkbox from "primevue/checkbox";
import InputNumber from "primevue/inputnumber";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import { watch } from "vue";
import { slugify } from "@/Utils/slugify.js";

const model = defineModel({ type: Object, required: true });

defineProps({
    categories: { type: Array, required: true },
    stockStatuses: { type: Array, required: true },
    errors: { type: Object, default: () => ({}) },
});

watch(
    () => model.value.name,
    (name) => {
        if (!model.value.slug) {
            model.value.slug = slugify(name ?? "");
        }
    }
);
</script>

<template>
    <div class="grid gap-4 lg:grid-cols-2">
        <div class="space-y-1">
            <label class="text-xs font-semibold uppercase text-bakery-brown/80">{{ $t("common.name") }}</label>
            <InputText v-model="model.name" class="w-full" />
            <p v-if="errors['product.name']" class="text-xs text-red-700">
                {{
                    errors["product.name"].startsWith?.("admin.") ? $t(errors["product.name"]) : errors["product.name"]
                }}
            </p>
        </div>
        <div class="space-y-1">
            <label class="text-xs font-semibold uppercase text-bakery-brown/80">{{ $t("common.slug") }}</label>
            <InputText v-model="model.slug" class="w-full" />
        </div>
        <div class="space-y-1">
            <label class="text-xs font-semibold uppercase text-bakery-brown/80">{{ $t("common.category") }}</label>
            <Select
                v-model="model.category_id"
                :options="categories"
                option-label="name"
                option-value="id"
                class="w-full"
            />
            <p v-if="errors['product.category_id']" class="text-xs text-red-700">
                {{
                    errors["product.category_id"].startsWith?.("admin.")
                        ? $t(errors["product.category_id"])
                        : errors["product.category_id"]
                }}
            </p>
        </div>
        <div class="space-y-1">
            <label class="text-xs font-semibold uppercase text-bakery-brown/80">{{
                $t("admin.products.flow.fields.price")
            }}</label>
            <InputNumber
                v-model="model.price"
                class="w-full"
                :min="0"
                mode="decimal"
                :min-fraction-digits="0"
                :max-fraction-digits="2"
            />
        </div>
        <div class="space-y-1">
            <label class="text-xs font-semibold uppercase text-bakery-brown/80">{{ $t("common.status") }}</label>
            <Select
                v-model="model.stock_status"
                :options="stockStatuses"
                option-label="label"
                option-value="value"
                class="w-full"
            />
        </div>
        <label class="flex items-center gap-2 pt-6 text-sm font-medium text-bakery-dark">
            <Checkbox v-model="model.is_active" binary />
            {{ $t("common.active") }}
        </label>
        <div class="space-y-1 lg:col-span-2">
            <label class="text-xs font-semibold uppercase text-bakery-brown/80">{{
                $t("admin.products.flow.fields.description")
            }}</label>
            <Textarea v-model="model.description" class="w-full" rows="4" />
        </div>
    </div>
</template>
