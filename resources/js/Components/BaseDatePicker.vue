<script setup>
import { computed, getCurrentInstance, useAttrs } from "vue";
import DatePicker from "primevue/datepicker";
import { trans } from "laravel-vue-i18n";

defineOptions({ name: "BaseDatePicker", inheritAttrs: false });

const props = defineProps({
    modelValue: {
        type: [String, Date, null],
        default: null,
    },
    id: {
        type: String,
        default: null,
    },
    name: {
        type: String,
        default: null,
    },
    label: {
        type: String,
        default: null,
    },
    labelKey: {
        type: String,
        default: null,
    },
    placeholder: {
        type: String,
        default: null,
    },
    placeholderKey: {
        type: String,
        default: null,
    },
    helperText: {
        type: String,
        default: null,
    },
    helperKey: {
        type: String,
        default: null,
    },
    error: {
        type: String,
        default: null,
    },
    errorKey: {
        type: String,
        default: null,
    },
    mode: {
        type: String,
        default: "date",
        validator: (value) => ["date", "datetime"].includes(value),
    },
    outputType: {
        type: String,
        default: "string",
        validator: (value) => ["string", "date"].includes(value),
    },
    minDate: {
        type: [Date, String, null],
        default: null,
    },
    maxDate: {
        type: [Date, String, null],
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
    showIcon: {
        type: Boolean,
        default: true,
    },
    appendTo: {
        type: String,
        default: "body",
    },
    manualInput: {
        type: Boolean,
        default: true,
    },
    fluid: {
        type: Boolean,
        default: true,
    },
    dateFormat: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(["update:modelValue", "date-select"]);

const attrs = useAttrs();
const instance = getCurrentInstance();
const pad = (part) => String(part).padStart(2, "0");

function formatLocalDate(date) {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

function formatLocalDateTime(date) {
    return `${formatLocalDate(date)} ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
}

const parseDateParts = (value) => {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);

    if (!match) {
        return null;
    }

    return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
};

const parseDateTimeParts = (value) => {
    const match = /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?$/.exec(value);

    if (!match) {
        return null;
    }

    return new Date(
        Number(match[1]),
        Number(match[2]) - 1,
        Number(match[3]),
        Number(match[4] ?? 0),
        Number(match[5] ?? 0),
        Number(match[6] ?? 0)
    );
};

function parseDateValue(value, mode = props.mode) {
    if (!value) {
        return null;
    }

    if (value instanceof Date) {
        return Number.isNaN(value.getTime()) ? null : value;
    }

    const normalizedValue = String(value).trim();
    const localDate = mode === "datetime" ? parseDateTimeParts(normalizedValue) : parseDateParts(normalizedValue);
    const parsedDate = localDate ?? new Date(normalizedValue);

    return Number.isNaN(parsedDate.getTime()) ? null : parsedDate;
}

function normalizeOutputValue(value) {
    const date = parseDateValue(value);

    if (!date) {
        return null;
    }

    if (props.outputType === "date") {
        return date;
    }

    return props.mode === "datetime" ? formatLocalDateTime(date) : formatLocalDate(date);
}

const translate = (key) => (key ? trans(key) : null);

const resolvedLabel = computed(() => translate(props.labelKey) ?? props.label);
const resolvedPlaceholder = computed(() => translate(props.placeholderKey) ?? props.placeholder);
const resolvedHelperText = computed(() => translate(props.helperKey) ?? props.helperText);
const resolvedError = computed(() => translate(props.errorKey) ?? props.error);
const hasError = computed(() => Boolean(resolvedError.value));
const internalValue = computed({
    get() {
        return parseDateValue(props.modelValue);
    },
    set(value) {
        emit("update:modelValue", normalizeOutputValue(value));
    },
});
const normalizedMinDate = computed(() => parseDateValue(props.minDate));
const normalizedMaxDate = computed(() => parseDateValue(props.maxDate));
const generatedId = `base-date-picker-${instance?.uid ?? Math.random().toString(36).slice(2)}`;
const resolvedId = computed(() => props.id ?? attrs.inputId ?? attrs["input-id"] ?? props.name ?? generatedId);
const supportId = computed(() => `${resolvedId.value}-support`);
const rootClass = computed(() => [
    "space-y-1",
    {
        "base-date-picker--invalid": hasError.value,
    },
    attrs.class,
]);
const rootStyle = computed(() => attrs.style);
const resolvedDateFormat = computed(() => props.dateFormat ?? "yy-mm-dd");

const datePickerAttrs = computed(() => {
    const {
        class: _class,
        style: _style,
        inputId: _inputId,
        "input-id": _inputIdKebab,
        invalid: _invalid,
        "aria-invalid": _ariaInvalid,
        "aria-describedby": _ariaDescribedby,
        ...rest
    } = attrs;

    return rest;
});
</script>

<template>
    <div :class="rootClass" :style="rootStyle">
        <label v-if="resolvedLabel" :for="resolvedId" class="block text-sm font-medium text-bakery-dark">
            {{ resolvedLabel }}
            <span v-if="required" class="text-red-500" aria-hidden="true">*</span>
        </label>

        <DatePicker
            :input-id="resolvedId"
            :name="name"
            v-model="internalValue"
            :date-format="resolvedDateFormat"
            :show-time="mode === 'datetime'"
            hour-format="24"
            :show-icon="showIcon"
            :append-to="appendTo"
            :manual-input="manualInput"
            :fluid="fluid"
            :min-date="normalizedMinDate"
            :max-date="normalizedMaxDate"
            :disabled="disabled"
            :invalid="hasError || Boolean(attrs.invalid)"
            :placeholder="resolvedPlaceholder"
            :aria-invalid="hasError ? 'true' : undefined"
            :aria-describedby="hasError || resolvedHelperText ? supportId : undefined"
            class="w-full"
            v-bind="datePickerAttrs"
            @date-select="emit('date-select', $event)"
        />

        <p v-if="resolvedError" :id="supportId" class="text-sm text-red-600">
            {{ resolvedError }}
        </p>
        <p v-else-if="resolvedHelperText" :id="supportId" class="text-sm text-gray-500">
            {{ resolvedHelperText }}
        </p>
    </div>
</template>
