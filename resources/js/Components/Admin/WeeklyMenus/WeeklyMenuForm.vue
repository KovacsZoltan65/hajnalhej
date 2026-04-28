<script setup>
import { watch } from "vue";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import ToggleSwitch from "primevue/toggleswitch";
import { slugify } from "../../../Utils/slugify";
import { DatePicker, Message } from "primevue";

const props = defineProps({
    form: { type: Object, required: true },
    statuses: { type: Array, required: true },
});

watch(
    () => props.form.title,
    (title) => {
        props.form.slug = slugify(title);
    }
);

watch(
    () => props.form.week_start,
    (val) => {
        if (val instanceof Date) {
            props.form.week_start = val.toISOString().split("T")[0];
        }
    }
);
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2">
        <!-- Cím -->
        <div class="space-y-2 md:col-span-2">
            <label class="text-sm font-medium text-bakery-dark">Cím</label>
            <InputText
                id="weekly-menu-title"
                v-model="form.title"
                class="w-full"
                :invalid="Boolean(form.errors.title)"
            />
            <p v-if="form.errors.title" class="text-xs text-red-700">
                {{ form.errors.title }}
            </p>
        </div>

        <!-- Slug -->
        <div class="space-y-2 md:col-span-2">
            <label class="text-sm font-medium text-bakery-dark">Slug</label>
            <InputText
                id="weekly-menu-slug"
                v-model="form.slug"
                class="w-full"
                disabled
            />
            <p class="text-xs text-bakery-dark/60">
                Automatikusan generalodik a cim alapjan.
            </p>
            <p v-if="form.errors.slug" class="text-xs text-red-700">
                {{ form.errors.slug }}
            </p>
        </div>

        <!-- Hét kezdete -->
        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">Hét kezdete</label>
            <DatePicker
                v-model="form.week_start"
                show-icon
                showWeek
                dateFormat="yy.mm.dd"
                class="w-full"
            />
            <Message
                v-if="form.errors.week_start"
                severity="error"
                size="small"
                variant="simple"
            >
                {{ form.errors.week_start }}
            </Message>
        </div>

        <!-- Hét vége -->
        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">Hét vége</label>
            <DatePicker
                v-model="form.week_end"
                show-icon
                showWeek
                dateFormat="yy.mm.dd"
                class="w-full"
            />
            <Message
                v-if="form.errors.week_end"
                severity="error"
                size="small"
                variant="simple"
            >
                {{ form.errors.week_end }}
            </Message>
        </div>

        <!-- Állapot -->
        <div class="space-y-2">
            <label class="text-sm font-medium text-bakery-dark">Státusz</label>
            <Select
                v-model="form.status"
                :options="statuses"
                option-label="label"
                option-value="value"
                class="w-full"
            />
        </div>

        <!-- Kiemelt menu -->
        <div class="flex items-center gap-2 pt-7">
            <ToggleSwitch v-model="form.is_featured" />
            <label class="text-sm text-bakery-dark/80">Kiemelt menu</label>
        </div>

        <!-- Publikus megjegyzés -->
        <div class="space-y-2 md:col-span-2">
            <label class="text-sm font-medium text-bakery-dark">Public megjegyzés</label>
            <Textarea v-model="form.public_note" rows="3" class="w-full" auto-resize />
        </div>

        <!-- Belső megjegyzés -->
        <div class="space-y-2 md:col-span-2">
            <label class="text-sm font-medium text-bakery-dark"
                >Internal megjegyzés</label
            >
            <Textarea v-model="form.internal_note" rows="3" class="w-full" auto-resize />
        </div>
    </div>
</template>
