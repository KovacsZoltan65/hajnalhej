<script setup>
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import ToggleSwitch from 'primevue/toggleswitch';

const props = defineProps({
    form: { type: Object, required: true },
    products: { type: Array, required: true },
});
</script>

<template>
    <div class="grid gap-3 md:grid-cols-3">
        <!-- Termék -->
        <div class="space-y-2 md:col-span-3">
            <label class="text-sm font-medium text-bakery-dark">Termék</label>
            <Select
                v-model="form.product_id"
                :options="products"
                option-label="name"
                option-value="id"
                placeholder="Válassz terméket"
                class="w-full"
                filter
            >
                <template #option="slotProps">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="font-medium">{{ slotProps.option.name }}</p>
                            <p class="text-xs text-bakery-dark/70">
                                {{ slotProps.option.category_name ?? 'Kategória nélkül' }}
                            </p>
                        </div>
                        <span class="text-xs text-bakery-brown">
                            {{ new Intl.NumberFormat('hu-HU').format(slotProps.option.price) }} Ft
                        </span>
                    </div>
                </template>
            </Select>
        </div>

        <!-- Felülírt név -->
        <div class="space-y-2 md:col-span-2">
            <label class="text-sm font-medium text-bakery-dark">Felülírt név</label>
            <InputText v-model="form.override_name" placeholder="Opcionális" class="w-full" />
        </div>

        <!-- Felülírt ár -->
        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">Felülírt ár</label>
            <InputNumber
                v-model="form.override_price"
                mode="decimal"
                :min="0"
                :min-fraction-digits="2"
                :max-fraction-digits="2"
                placeholder="Ft"
                fluid
            />
        </div>

        <!-- Rövid leírás -->
        <div class="space-y-2 md:col-span-2">
            <label class="text-sm font-medium text-bakery-dark">Rövid leírás (felülírás)</label>
            <InputText v-model="form.override_short_description" placeholder="Opcionális" class="w-full" />
        </div>

        <!-- Sorrend -->
        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">Sorrend</label>
            <InputNumber v-model="form.sort_order" :min="0" placeholder="0" fluid />
        </div>

        <!-- Badge -->
        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">Badge szöveg</label>
            <InputText v-model="form.badge_text" placeholder="pl. Új" class="w-full" />
        </div>

        <!-- Készlet -->
        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">Készlet megjegyzés</label>
            <InputText v-model="form.stock_note" placeholder="pl. limitált" class="w-full" />
        </div>

        <!-- Aktív -->
        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">Állapot</label>
            <div class="flex h-[42px] items-center gap-2">
                <ToggleSwitch v-model="form.is_active" />
                <span class="text-sm text-bakery-dark/80">Aktív</span>
            </div>
        </div>
    </div>
</template>
