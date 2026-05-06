<script setup>
import Button from "primevue/button";
import InputNumber from "primevue/inputnumber";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import { reactive } from "vue";

const steps = defineModel({ type: Array, required: true });

const emptyStep = () => ({
    title: "",
    step_type: "mixing",
    duration_minutes: 10,
    wait_minutes: 0,
    work_instruction: "",
    completion_criteria: "",
    attention_points: "",
    required_tools: "",
    expected_result: "",
    description: "",
    temperature_celsius: null,
    sort_order: steps.value.length + 1,
    is_active: true,
});

const draft = reactive(emptyStep());

const resetDraft = () => Object.assign(draft, emptyStep());

const addStep = () => {
    if (!draft.title || Number(draft.duration_minutes ?? 0) + Number(draft.wait_minutes ?? 0) <= 0) {
        return;
    }

    steps.value.push({ ...draft, sort_order: steps.value.length + 1 });
    resetDraft();
};

const move = (index, direction) => {
    const target = index + direction;

    if (target < 0 || target >= steps.value.length) {
        return;
    }

    const [item] = steps.value.splice(index, 1);
    steps.value.splice(target, 0, item);
    steps.value.forEach((step, stepIndex) => {
        step.sort_order = stepIndex + 1;
    });
};

const removeStep = (index) => {
    steps.value.splice(index, 1);
};
</script>

<template>
    <div class="space-y-4">
        <div
            class="grid gap-3 rounded-lg border border-bakery-brown/15 bg-[#fcf7ef] p-3 lg:grid-cols-[minmax(0,1fr)_9rem_auto]"
        >
            <InputText v-model="draft.title" :placeholder="$t('admin.products.flow.fields.step_title')" />
            <InputNumber v-model="draft.duration_minutes" :min="0" suffix=" min" />
            <Button icon="pi pi-plus" :label="$t('admin.products.flow.actions.add_step')" @click="addStep" />
            <Textarea
                v-model="draft.work_instruction"
                class="lg:col-span-3"
                rows="2"
                :placeholder="$t('admin.products.flow.fields.work_instruction')"
            />
        </div>

        <div class="space-y-3">
            <article
                v-for="(step, index) in steps"
                :key="index"
                class="rounded-lg border border-bakery-brown/10 bg-white p-4"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase text-bakery-brown/70">
                            {{ $t("admin.products.flow.step_index", { count: index + 1 }) }}
                        </p>
                        <h3 class="text-base font-semibold text-bakery-dark">
                            {{ step.title }}
                        </h3>
                        <p class="text-sm text-bakery-dark/65">{{ step.duration_minutes }} min</p>
                    </div>
                    <div class="flex items-center gap-1">
                        <Button icon="pi pi-arrow-up" text rounded :disabled="index === 0" @click="move(index, -1)" />
                        <Button
                            icon="pi pi-arrow-down"
                            text
                            rounded
                            :disabled="index === steps.length - 1"
                            @click="move(index, 1)"
                        />
                        <Button icon="pi pi-trash" text rounded severity="danger" @click="removeStep(index)" />
                    </div>
                </div>
                <p v-if="step.work_instruction" class="mt-3 text-sm text-bakery-dark/75">
                    {{ step.work_instruction }}
                </p>
            </article>
        </div>
    </div>
</template>
