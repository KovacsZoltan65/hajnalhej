<script setup>
import InputNumber from "primevue/inputnumber";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import ToggleSwitch from "primevue/toggleswitch";
import { useLocaleFormat } from "@/composables/useLocaleFormat";

const props = defineProps({
    form: { type: Object, required: true },
    products: { type: Array, required: true },
});

const { formatCurrency } = useLocaleFormat();
</script>

<template>
    <div class="grid gap-3 md:grid-cols-3">
        <div class="space-y-2 md:col-span-3">
            <label class="text-sm font-medium text-bakery-dark">{{ $t("common.product") }}</label>
            <Select
                v-model="form.product_id"
                :options="products"
                option-label="name"
                option-value="id"
                :placeholder="$t('admin_weekly_menus.item_form.product_placeholder')"
                class="w-full"
                filter
            >
                <template #option="slotProps">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="font-medium">{{ slotProps.option.name }}</p>
                            <p class="text-xs text-bakery-dark/70">
                                {{ slotProps.option.category_name ?? $t("admin_weekly_menus.item_form.no_category") }}
                            </p>
                        </div>
                        <span class="text-xs text-bakery-brown">
                            {{ formatCurrency(slotProps.option.price) }}
                        </span>
                    </div>
                </template>
            </Select>
        </div>

        <div class="space-y-2 md:col-span-2">
            <label class="text-sm font-medium text-bakery-dark">{{
                $t("admin_weekly_menus.item_form.override_name")
            }}</label>
            <InputText v-model="form.override_name" :placeholder="$t('common.optional')" class="w-full" />
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">{{
                $t("admin_weekly_menus.item_form.override_price")
            }}</label>
            <InputNumber
                v-model="form.override_price"
                mode="decimal"
                :min="0"
                :min-fraction-digits="2"
                :max-fraction-digits="2"
                :placeholder="$t('admin_weekly_menus.item_form.override_price_placeholder')"
                fluid
            />
        </div>

        <div class="space-y-2 md:col-span-2">
            <label class="text-sm font-medium text-bakery-dark">{{
                $t("admin_weekly_menus.item_form.override_short_description")
            }}</label>
            <InputText v-model="form.override_short_description" :placeholder="$t('common.optional')" class="w-full" />
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">{{ $t("common.sort_order") }}</label>
            <InputNumber v-model="form.sort_order" :min="0" placeholder="0" fluid />
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">{{
                $t("admin_weekly_menus.item_form.badge_text")
            }}</label>
            <InputText
                v-model="form.badge_text"
                :placeholder="$t('admin_weekly_menus.item_form.badge_text_placeholder')"
                class="w-full"
            />
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">{{
                $t("admin_weekly_menus.item_form.stock_note")
            }}</label>
            <InputText
                v-model="form.stock_note"
                :placeholder="$t('admin_weekly_menus.item_form.stock_note_placeholder')"
                class="w-full"
            />
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">{{ $t("common.status") }}</label>
            <div class="flex h-10.5 items-center gap-2">
                <ToggleSwitch v-model="form.is_active" />
                <span class="text-sm text-bakery-dark/80">{{ $t("common.active") }}</span>
            </div>
        </div>
    </div>
</template>
