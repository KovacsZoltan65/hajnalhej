<script setup>
import { reactive, watch } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import ToggleSwitch from 'primevue/toggleswitch';

const props = defineProps({
    visible: { type: Boolean, required: true },
    item: { type: Object, default: null },
    stepTypes: { type: Array, required: true },
    errors: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:visible', 'submit']);

const form = reactive({
    id: null,
    title: '',
    step_type: 'preparation',
    description: '',
    work_instruction: '',
    completion_criteria: '',
    attention_points: '',
    required_tools: '',
    expected_result: '',
    duration_minutes: null,
    wait_minutes: null,
    temperature_celsius: null,
    sort_order: 0,
    is_active: true,
});

const resetForm = () => {
    form.id = null;
    form.title = '';
    form.step_type = props.stepTypes[0]?.value ?? 'preparation';
    form.description = '';
    form.work_instruction = '';
    form.completion_criteria = '';
    form.attention_points = '';
    form.required_tools = '';
    form.expected_result = '';
    form.duration_minutes = null;
    form.wait_minutes = null;
    form.temperature_celsius = null;
    form.sort_order = 0;
    form.is_active = true;
};

const fillForm = () => {
    if (!props.item) {
        resetForm();
        return;
    }

    form.id = props.item.id;
    form.title = props.item.title ?? '';
    form.step_type = props.item.step_type ?? (props.stepTypes[0]?.value ?? 'preparation');
    form.description = props.item.description ?? '';
    form.work_instruction = props.item.work_instruction ?? '';
    form.completion_criteria = props.item.completion_criteria ?? '';
    form.attention_points = props.item.attention_points ?? '';
    form.required_tools = props.item.required_tools ?? '';
    form.expected_result = props.item.expected_result ?? '';
    form.duration_minutes = props.item.duration_minutes;
    form.wait_minutes = props.item.wait_minutes;
    form.temperature_celsius = props.item.temperature_celsius;
    form.sort_order = props.item.sort_order ?? 0;
    form.is_active = props.item.is_active ?? true;
};

watch(
    () => props.visible,
    (visible) => {
        if (visible) {
            fillForm();
        }
    },
);

const submit = () => {
    emit('submit', {
        id: form.id,
        title: form.title,
        step_type: form.step_type,
        description: form.description || null,
        work_instruction: form.work_instruction || null,
        completion_criteria: form.completion_criteria || null,
        attention_points: form.attention_points || null,
        required_tools: form.required_tools || null,
        expected_result: form.expected_result || null,
        duration_minutes: form.duration_minutes,
        wait_minutes: form.wait_minutes,
        temperature_celsius: form.temperature_celsius,
        sort_order: form.sort_order ?? 0,
        is_active: form.is_active,
    });
};

const close = () => emit('update:visible', false);
</script>

<template>
    <Dialog
        :visible="visible"
        modal
        :header="item ? 'Receptlepes szerkesztese' : 'Uj receptlepes'"
        :style="{ width: '48rem', maxWidth: '97vw' }"
        :content-style="{ maxHeight: '70vh', overflowY: 'auto' }"
        @update:visible="(value) => emit('update:visible', value)"
    >
        <form id="recipe-step-form" class="grid gap-4 md:grid-cols-2" @submit.prevent="submit">
            <div class="space-y-2 md:col-span-2">
                <label class="text-sm font-medium text-bakery-dark">Lepes cim</label>
                <InputText v-model="form.title" class="w-full" />
                <p v-if="errors.title" class="text-xs text-red-700">{{ errors.title }}</p>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-bakery-dark">Lepes tipus</label>
                <Select v-model="form.step_type" :options="stepTypes" option-label="label" option-value="value" class="w-full" />
                <p v-if="errors.step_type" class="text-xs text-red-700">{{ errors.step_type }}</p>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-bakery-dark">Sorrend</label>
                <InputNumber v-model="form.sort_order" :min="0" fluid />
                <p v-if="errors.sort_order" class="text-xs text-red-700">{{ errors.sort_order }}</p>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-bakery-dark">Aktiv ido (perc)</label>
                <InputNumber v-model="form.duration_minutes" :min="0" fluid />
                <p v-if="errors.duration_minutes" class="text-xs text-red-700">{{ errors.duration_minutes }}</p>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-bakery-dark">Varakozasi ido (perc)</label>
                <InputNumber v-model="form.wait_minutes" :min="0" fluid />
                <p v-if="errors.wait_minutes" class="text-xs text-red-700">{{ errors.wait_minutes }}</p>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-bakery-dark">Homerseklet (C)</label>
                <InputNumber v-model="form.temperature_celsius" mode="decimal" :min-fraction-digits="0" :max-fraction-digits="1" fluid />
                <p v-if="errors.temperature_celsius" class="text-xs text-red-700">{{ errors.temperature_celsius }}</p>
            </div>

            <div class="flex items-center gap-2 pt-7">
                <ToggleSwitch v-model="form.is_active" />
                <label class="text-sm text-bakery-dark/80">Aktiv lepes</label>
            </div>

            <div class="space-y-2 md:col-span-2">
                <label class="text-sm font-medium text-bakery-dark">Leiras</label>
                <Textarea v-model="form.description" rows="4" class="w-full" auto-resize />
                <p v-if="errors.description" class="text-xs text-red-700">{{ errors.description }}</p>
            </div>

            <div class="space-y-2 md:col-span-2">
                <label class="text-sm font-medium text-bakery-dark">Mit kell csinalni?</label>
                <Textarea v-model="form.work_instruction" rows="3" class="w-full" auto-resize />
                <p v-if="errors.work_instruction" class="text-xs text-red-700">{{ errors.work_instruction }}</p>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-bakery-dark">Mibol latszik, hogy kesz?</label>
                <Textarea v-model="form.completion_criteria" rows="3" class="w-full" auto-resize />
                <p v-if="errors.completion_criteria" class="text-xs text-red-700">{{ errors.completion_criteria }}</p>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-bakery-dark">Mire figyelj?</label>
                <Textarea v-model="form.attention_points" rows="3" class="w-full" auto-resize />
                <p v-if="errors.attention_points" class="text-xs text-red-700">{{ errors.attention_points }}</p>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-bakery-dark">Szukseges eszkoz</label>
                <Textarea v-model="form.required_tools" rows="3" class="w-full" auto-resize />
                <p v-if="errors.required_tools" class="text-xs text-red-700">{{ errors.required_tools }}</p>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-bakery-dark">Elvart eredmeny</label>
                <Textarea v-model="form.expected_result" rows="3" class="w-full" auto-resize />
                <p v-if="errors.expected_result" class="text-xs text-red-700">{{ errors.expected_result }}</p>
            </div>
        </form>
        <template #footer>
            <div class="flex justify-end gap-2">
                <Button type="button" severity="secondary" label="Megse" @click="close" />
                <Button type="submit" form="recipe-step-form" :label="item ? 'Mentes' : 'Hozzaadas'" />
            </div>
        </template>
    </Dialog>
</template>
