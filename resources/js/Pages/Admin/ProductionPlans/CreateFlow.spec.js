import { mount } from "@vue/test-utils";
import { reactive } from "vue";
import { describe, expect, it, vi } from "vitest";
import CreateFlow from "./CreateFlow.vue";

const post = vi.fn();
let processing = false;

vi.mock("@inertiajs/vue3", async () => {
    const actual = await vi.importActual("@inertiajs/vue3");

    return {
        ...actual,
        Head: { template: "<div />" },
        Link: { template: "<a><slot /></a>" },
        useForm: (data) =>
            reactive({
                ...data,
                errors: {},
                processing,
                transform(callback) {
                    this.payload = callback();
                    return this;
                },
                post,
            }),
    };
});

vi.mock("laravel-vue-i18n", () => ({
    trans: (key) => key,
}));

const product = {
    id: 1,
    name: "Country loaf",
    slug: "country-loaf",
    category_name: "Bread",
    unit_label: "db",
    product_ingredients: [
        {
            ingredient_id: 1,
            ingredient_name: "Flour",
            ingredient_unit: "g",
            quantity: 500,
            current_stock: 1200,
            minimum_stock: 1000,
        },
    ],
    recipe_steps: [
        {
            id: 1,
            title: "Mix",
            work_instruction: "Mix gently.",
            duration_minutes: 15,
            wait_minutes: 45,
            sort_order: 0,
        },
    ],
};

const stubs = {
    Head: { template: "<div />" },
    Link: { template: "<a><slot /></a>" },
    SectionTitle: {
        template: "<header>{{ title }} {{ description }}</header>",
        props: ["title", "description"],
    },
    AdminTableEmptyState: {
        template: "<div>{{ title }} {{ description }}</div>",
        props: ["title", "description"],
    },
    EntityStatusBadge: { template: "<span><slot /></span>" },
    Button: {
        template:
            "<button :disabled='disabled' @click=\"$emit('click')\">{{ loading ? 'loading' : label }}<slot /></button>",
        props: ["label", "disabled", "loading"],
    },
    DatePicker: {
        template:
            "<input data-testid='target-date' @input=\"$emit('update:modelValue', new Date('2026-05-07T09:00:00'))\" />",
        props: ["modelValue"],
    },
    InputNumber: {
        template:
            "<input type='number' :value='modelValue' @input=\"$emit('update:modelValue', Number($event.target.value))\" />",
        props: ["modelValue"],
    },
    Select: {
        template:
            "<select :value='modelValue' @change=\"$emit('update:modelValue', Number($event.target.value))\"><option v-for='option in options' :key='option.id' :value='option.id'>{{ option.name }}</option></select>",
        props: ["modelValue", "options"],
    },
    Textarea: {
        template: "<textarea :value='modelValue' @input=\"$emit('update:modelValue', $event.target.value)\" />",
        props: ["modelValue"],
    },
};

const factory = (options = {}) =>
    mount(CreateFlow, {
        props: {
            products: options.products ?? [product],
            statuses: [],
        },
        global: { stubs },
    });

describe("ProductionPlans CreateFlow", () => {
    it("navigates wizard steps", async () => {
        const wrapper = factory();

        expect(wrapper.text()).toContain("admin.production_plans.flow.products.title");

        await wrapper
            .findAll("button")
            .find((button) => button.text().includes("admin.production_plans.flow.actions.next"))
            .trigger("click");

        expect(wrapper.text()).toContain("admin.production_plans.flow.target.title");
    });

    it("supports product selection rows", async () => {
        const wrapper = factory({
            products: [product, { ...product, id: 2, name: "Baguette", slug: "baguette" }],
        });

        await wrapper
            .findAll("button")
            .find((button) => button.text().includes("admin.production_plans.flow.actions.add_product"))
            .trigger("click");

        expect(wrapper.vm.form.items.length).toBeGreaterThan(1);
        expect(wrapper.vm.form.items.map((item) => item.product_id)).toContain(2);
    });

    it("renders ingredient preview", async () => {
        const wrapper = factory();

        wrapper.vm.step = 3;
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain("Flour");
        expect(wrapper.text()).toContain("500");
    });

    it("renders timeline preview", async () => {
        const wrapper = factory();

        wrapper.vm.form.target_ready_at = new Date("2026-05-07T09:00:00");
        wrapper.vm.step = 4;
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain("Mix");
        expect(wrapper.text()).toContain("Mix gently.");
    });

    it("shows submit loading state", async () => {
        processing = true;
        const wrapper = factory();

        wrapper.vm.form.target_ready_at = new Date("2026-05-07T09:00:00");
        wrapper.vm.step = 4;
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain("loading");
        processing = false;
    });
});
