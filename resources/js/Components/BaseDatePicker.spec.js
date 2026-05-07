import { mount } from "@vue/test-utils";
import BaseDatePicker from "./BaseDatePicker.vue";

vi.mock("laravel-vue-i18n", () => ({
    trans: (key) =>
        ({
            "forms.date.label": "Dátum",
            "forms.date.placeholder": "Válassz dátumot",
            "forms.date.helper": "Backend-kompatibilis dátum.",
            "forms.date.error": "Kötelező dátum.",
        })[key] ?? key,
}));

const DatePickerStub = {
    name: "DatePicker",
    props: [
        "inputId",
        "name",
        "modelValue",
        "dateFormat",
        "showTime",
        "hourFormat",
        "showIcon",
        "appendTo",
        "minDate",
        "maxDate",
        "disabled",
        "invalid",
        "placeholder",
        "manualInput",
        "fluid",
    ],
    emits: ["update:modelValue", "date-select"],
    template: '<input :id="inputId" :name="name" :placeholder="placeholder" />',
};

const mountPicker = (props = {}, attrs = {}) =>
    mount(BaseDatePicker, {
        props,
        attrs,
        global: {
            stubs: {
                DatePicker: DatePickerStub,
            },
        },
    });

describe("BaseDatePicker", () => {
    it("renders with a plain label", () => {
        const wrapper = mountPicker({
            label: "Átvétel dátuma",
        });

        expect(wrapper.text()).toContain("Átvétel dátuma");
        expect(wrapper.find("label").attributes("for")).toMatch(/^base-date-picker-/);
    });

    it("translates labelKey and placeholderKey with priority over plain text", () => {
        const wrapper = mountPicker({
            id: "delivery-date",
            name: "delivery_date",
            labelKey: "forms.date.label",
            label: "Fallback label",
            placeholderKey: "forms.date.placeholder",
            placeholder: "Fallback placeholder",
        });

        const datePicker = wrapper.findComponent(DatePickerStub);

        expect(wrapper.text()).toContain("Dátum");
        expect(wrapper.text()).not.toContain("Fallback label");
        expect(datePicker.props("placeholder")).toBe("Válassz dátumot");
    });

    it("renders helper text only when no error is present", () => {
        const wrapper = mountPicker({
            helperKey: "forms.date.helper",
        });

        expect(wrapper.text()).toContain("Backend-kompatibilis dátum.");
    });

    it("renders error state and hides helper text when error exists", () => {
        const wrapper = mountPicker({
            id: "delivery-date",
            helperText: "Segítő szöveg",
            errorKey: "forms.date.error",
            required: true,
        });

        const datePicker = wrapper.findComponent(DatePickerStub);

        expect(wrapper.text()).toContain("Kötelező dátum.");
        expect(wrapper.text()).not.toContain("Segítő szöveg");
        expect(datePicker.props("inputId")).toBe("delivery-date");
        expect(datePicker.props("invalid")).toBe(true);
        expect(datePicker.attributes("aria-invalid")).toBe("true");
        expect(datePicker.attributes("aria-describedby")).toBe("delivery-date-support");
    });

    it("renders the required marker", () => {
        const wrapper = mountPicker({
            label: "Dátum",
            required: true,
        });

        expect(wrapper.find("label").text()).toContain("*");
    });

    it("parses Y-m-d strings into DatePicker-compatible local Date instances", () => {
        const wrapper = mountPicker({
            modelValue: "2026-05-07",
        });

        const datePicker = wrapper.findComponent(DatePickerStub);
        const pickerValue = datePicker.props("modelValue");

        expect(pickerValue).toBeInstanceOf(Date);
        expect(pickerValue.getFullYear()).toBe(2026);
        expect(pickerValue.getMonth()).toBe(4);
        expect(pickerValue.getDate()).toBe(7);
        expect(pickerValue.getHours()).toBe(0);
    });

    it("emits Y-m-d strings after selecting a Date", async () => {
        const wrapper = mountPicker();
        const datePicker = wrapper.findComponent(DatePickerStub);

        await datePicker.vm.$emit("update:modelValue", new Date(2026, 4, 8, 23, 30, 45));

        expect(wrapper.emitted("update:modelValue")[0]).toEqual(["2026-05-08"]);
    });

    it("formats date-only output without UTC day shifting", async () => {
        const wrapper = mountPicker();
        const datePicker = wrapper.findComponent(DatePickerStub);
        const localEarlyMorning = new Date(2026, 0, 1, 0, 30, 0);

        await datePicker.vm.$emit("update:modelValue", localEarlyMorning);

        expect(wrapper.emitted("update:modelValue")[0]).toEqual(["2026-01-01"]);
    });

    it("emits backend-compatible datetime strings in local time", async () => {
        const wrapper = mountPicker({
            mode: "datetime",
            modelValue: "2026-05-07 08:15:00",
            minDate: "2026-05-07 06:00:00",
        });

        const datePicker = wrapper.findComponent(DatePickerStub);

        expect(datePicker.props("showTime")).toBe(true);
        expect(datePicker.props("hourFormat")).toBe("24");
        expect(datePicker.props("modelValue").getHours()).toBe(8);
        expect(datePicker.props("minDate").getHours()).toBe(6);

        await datePicker.vm.$emit("update:modelValue", new Date(2026, 4, 7, 9, 30, 0));

        expect(wrapper.emitted("update:modelValue")[0]).toEqual(["2026-05-07 09:30:00"]);
    });

    it("can emit Date instances for workflows that need Date arithmetic", async () => {
        const wrapper = mountPicker({
            outputType: "date",
        });
        const selectedDate = new Date(2026, 4, 7, 9, 30, 0);

        await wrapper.findComponent(DatePickerStub).vm.$emit("update:modelValue", selectedDate);

        expect(wrapper.emitted("update:modelValue")[0][0]).toBe(selectedDate);
    });

    it("handles minDate and maxDate from string and Date inputs", () => {
        const maxDate = new Date(2026, 4, 20, 11, 0, 0);
        const wrapper = mountPicker({
            minDate: "2026-05-07",
            maxDate,
        });

        const datePicker = wrapper.findComponent(DatePickerStub);

        expect(datePicker.props("minDate")).toBeInstanceOf(Date);
        expect(datePicker.props("minDate").getFullYear()).toBe(2026);
        expect(datePicker.props("minDate").getMonth()).toBe(4);
        expect(datePicker.props("minDate").getDate()).toBe(7);
        expect(datePicker.props("maxDate")).toBe(maxDate);
    });

    it("passes disabled and PrimeVue UX defaults through", () => {
        const wrapper = mountPicker({
            disabled: true,
        });

        const datePicker = wrapper.findComponent(DatePickerStub);

        expect(datePicker.props("disabled")).toBe(true);
        expect(datePicker.props("manualInput")).toBe(true);
        expect(datePicker.props("fluid")).toBe(true);
        expect(datePicker.props("dateFormat")).toBe("yy-mm-dd");
        expect(datePicker.props("appendTo")).toBe("body");
    });

    it("forwards extra attributes to PrimeVue DatePicker", () => {
        const wrapper = mountPicker({}, { "data-test": "pickup-date", showWeek: true });
        const datePicker = wrapper.findComponent(DatePickerStub);

        expect(datePicker.attributes("data-test")).toBe("pickup-date");
        expect(datePicker.attributes("showweek")).toBe("true");
    });

    it("emits null for invalid manual input values without throwing", async () => {
        const wrapper = mountPicker();

        await wrapper.findComponent(DatePickerStub).vm.$emit("update:modelValue", "not-a-date");

        expect(wrapper.emitted("update:modelValue")[0]).toEqual([null]);
    });
});
