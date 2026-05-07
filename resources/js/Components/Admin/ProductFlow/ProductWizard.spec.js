import { mount } from "@vue/test-utils";
import { describe, expect, it, vi } from "vitest";
import ProductWizard from "./ProductWizard.vue";

vi.mock("@inertiajs/vue3", async () => {
    const actual = await vi.importActual("@inertiajs/vue3");

    return {
        ...actual,
        useForm: (data) => ({
            ...data,
            errors: {},
            processing: false,
            transform(callback) {
                this.payload = callback();
                return this;
            },
            post: vi.fn(),
        }),
    };
});

const stubs = {
    Stepper: { template: "<div><slot /></div>" },
    StepList: { template: "<div><slot /></div>" },
    Step: { template: "<button><slot /></button>" },
    StepPanels: { template: "<div><slot /></div>" },
    StepPanel: { template: "<section><slot /></section>" },
    InputText: {
        template: '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
        props: ["modelValue"],
    },
    InputNumber: {
        template:
            '<input type="number" :value="modelValue" @input="$emit(\'update:modelValue\', Number($event.target.value))" />',
        props: ["modelValue"],
    },
    Textarea: {
        template: '<textarea :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
        props: ["modelValue"],
    },
    Select: {
        template:
            "<select :value=\"modelValue\" @change=\"$emit('update:modelValue', Number($event.target.value)); $emit('change')\"><option v-for=\"option in options\" :key=\"option[optionValue || 'id']\" :value=\"option[optionValue || 'id']\">{{ option[optionLabel || 'name'] }}</option></select>",
        props: ["modelValue", "options", "optionLabel", "optionValue"],
    },
    Checkbox: {
        template:
            '<input type="checkbox" :checked="modelValue" @change="$emit(\'update:modelValue\', $event.target.checked)" />',
        props: ["modelValue"],
    },
    Button: {
        template: '<button :disabled="disabled" @click="$emit(\'click\')">{{ label }}<slot /></button>',
        props: ["label", "disabled"],
    },
    Link: { template: "<a><slot /></a>" },
};

const factory = () =>
    mount(ProductWizard, {
        props: {
            categories: [{ id: 1, name: "Bread" }],
            ingredients: [{ id: 1, name: "Flour", unit: "kg", is_low_stock: false }],
            stockStatuses: [{ value: "in_stock", label: "In stock" }],
        },
        global: { stubs },
    });

describe("ProductWizard", () => {
    it("renders the wizard steps", () => {
        const wrapper = factory();

        expect(wrapper.text()).toContain("admin.products.flow.steps.basics");
        expect(wrapper.text()).toContain("admin.products.flow.steps.production_preview");
    });

    it("blocks next without required basics", async () => {
        const wrapper = factory();

        await wrapper
            .findAll("button")
            .find((button) => button.text().includes("admin.products.flow.actions.next"))
            .trigger("click");

        expect(wrapper.text()).toContain("admin.products.flow.validation.name");
    });

    it("moves next and previous after valid basics", async () => {
        const wrapper = factory();

        await wrapper.find("input").setValue("Country loaf");
        await wrapper
            .findAll("button")
            .find((button) => button.text().includes("admin.products.flow.actions.next"))
            .trigger("click");

        expect(wrapper.text()).toContain("admin.products.flow.empty.ingredients");

        await wrapper
            .findAll("button")
            .find((button) => button.text().includes("common.previous"))
            .trigger("click");

        expect(wrapper.text()).toContain("Country loaf");
    });

    it("shows the production preview summary", async () => {
        const wrapper = factory();

        wrapper.vm.product.name = "Country loaf";
        wrapper.vm.recipeIngredients.push({
            ingredient_id: 1,
            quantity: 2,
            sort_order: 1,
        });
        wrapper.vm.recipeSteps.push({
            title: "Mix",
            duration_minutes: 15,
            wait_minutes: 0,
        });
        wrapper.vm.step = "4";
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain("Country loaf");
        expect(wrapper.text()).toContain("15 min");
    });
});
