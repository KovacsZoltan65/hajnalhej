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
                setError(field, message) {
                    this.errors[field] = message;
                },
                transform(callback) {
                    this.payload = callback();
                    return this;
                },
                post,
            }),
    };
});

vi.mock("laravel-vue-i18n", () => ({
    trans: (key, replacements = {}) => {
        let value = key;
        Object.entries(replacements).forEach(([name, replacement]) => {
            value = value.replace(`:${name}`, replacement);
        });
        return value;
    },
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
    BaseDatePicker: {
        name: "BaseDatePicker",
        template:
            "<input data-testid='target-date' @input=\"$emit('update:modelValue', new Date('2026-05-07T09:00:00'))\" />",
        props: ["modelValue", "minDate"],
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

        wrapper.vm.form.items[0].product_id = 1;
        wrapper.vm.form.items[0].target_quantity = 1;
        await wrapper.vm.$nextTick();

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
        expect(wrapper.vm.form.items[1]).toEqual({
            product_id: null,
            target_quantity: null,
            unit_label: "",
            sort_order: null,
        });
    });

    it("adds consecutive product rows without inherited values", async () => {
        const wrapper = factory({
            products: [
                product,
                { ...product, id: 2, name: "Baguette", slug: "baguette" },
                { ...product, id: 3, name: "Rye", slug: "rye" },
            ],
        });

        wrapper.vm.form.items[0].product_id = 1;
        wrapper.vm.form.items[0].target_quantity = 9;
        wrapper.vm.form.items[0].unit_label = "kg";
        await wrapper.vm.$nextTick();

        const addButton = wrapper
            .findAll("button")
            .find((button) => button.text().includes("admin.production_plans.flow.actions.add_product"));

        await addButton.trigger("click");
        await addButton.trigger("click");

        expect(wrapper.vm.form.items[1]).toEqual({
            product_id: null,
            target_quantity: null,
            unit_label: "",
            sort_order: null,
        });
        expect(wrapper.vm.form.items[2]).toEqual({
            product_id: null,
            target_quantity: null,
            unit_label: "",
            sort_order: null,
        });
        expect(wrapper.vm.form.items[1]).not.toBe(wrapper.vm.form.items[2]);
    });

    it("product change does not carry an old quantity into an empty row", async () => {
        const wrapper = factory({
            products: [product, { ...product, id: 2, name: "Baguette", slug: "baguette" }],
        });

        wrapper.vm.form.items[0].product_id = 1;
        wrapper.vm.form.items[0].target_quantity = 6;
        await wrapper.vm.$nextTick();

        await wrapper
            .findAll("button")
            .find((button) => button.text().includes("admin.production_plans.flow.actions.add_product"))
            .trigger("click");

        wrapper.vm.form.items[1].product_id = 2;
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.form.items[1]).toMatchObject({
            product_id: 2,
            target_quantity: null,
            unit_label: "",
            sort_order: null,
        });
    });

    it("renders ingredient preview", async () => {
        const wrapper = factory();

        wrapper.vm.form.items[0].product_id = 1;
        wrapper.vm.form.items[0].target_quantity = 1;
        wrapper.vm.step = 3;
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain("Flour");
        expect(wrapper.text()).toContain("500");
    });

    it("renders timeline preview", async () => {
        const wrapper = factory();

        wrapper.vm.form.items[0].product_id = 1;
        wrapper.vm.form.items[0].target_quantity = 1;
        wrapper.vm.form.target_ready_at = new Date("2026-05-07T09:00:00");
        wrapper.vm.step = 4;
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain("Mix");
        expect(wrapper.text()).toContain("Mix gently.");
    });

    it("shows submit loading state", async () => {
        processing = true;
        const wrapper = factory();

        wrapper.vm.form.items[0].product_id = 1;
        wrapper.vm.form.items[0].target_quantity = 1;
        wrapper.vm.form.target_ready_at = new Date("2026-05-07T09:00:00");
        wrapper.vm.step = 4;
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain("loading");
        processing = false;
    });

    it("passes a dynamic minimum date to the target date picker", () => {
        vi.useFakeTimers();
        vi.setSystemTime(new Date("2026-05-06T10:00:00"));
        const wrapper = factory();

        wrapper.vm.form.items[0].product_id = 1;
        wrapper.vm.step = 2;

        expect(wrapper.vm.minTargetReadyAt.toISOString()).toBe("2026-05-06T09:00:00.000Z");
        vi.useRealTimers();
    });

    it("blocks submit and shows an error for a past target time", async () => {
        vi.useFakeTimers();
        vi.setSystemTime(new Date("2026-05-06T10:00:00"));
        post.mockClear();
        const wrapper = factory();

        wrapper.vm.form.items[0].product_id = 1;
        wrapper.vm.form.items[0].target_quantity = 1;
        wrapper.vm.form.target_ready_at = new Date("2026-05-06T10:30:00");
        wrapper.vm.step = 4;
        await wrapper.vm.$nextTick();

        await wrapper
            .findAll("button")
            .find((button) => button.text().includes("admin.production_plans.flow.actions.save"))
            .trigger("click");

        expect(post).not.toHaveBeenCalled();
        expect(wrapper.vm.form.errors.target_ready_at).toContain(
            "admin.production_plans.validation.too_early_for_recipe"
        );
        expect(wrapper.text()).toContain("admin.production_plans.validation.too_early_for_recipe");
        vi.useRealTimers();
    });

    it("uses the longest recipe duration across selected products", async () => {
        vi.useFakeTimers();
        vi.setSystemTime(new Date("2026-05-06T10:00:00"));
        const wrapper = factory({
            products: [
                product,
                {
                    ...product,
                    id: 2,
                    name: "Slow rye",
                    slug: "slow-rye",
                    recipe_steps: [
                        {
                            id: 2,
                            title: "Proof",
                            duration_minutes: 20,
                            wait_minutes: 100,
                            sort_order: 0,
                        },
                    ],
                },
            ],
        });

        wrapper.vm.form.items[0].product_id = 1;
        await wrapper
            .findAll("button")
            .find((button) => button.text().includes("admin.production_plans.flow.actions.add_product"))
            .trigger("click");
        wrapper.vm.form.items[1].product_id = 2;
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.longestRecipeDurationMinutes).toBe(120);
        expect(wrapper.vm.minTargetReadyAt.toISOString()).toBe("2026-05-06T10:00:00.000Z");
        vi.useRealTimers();
    });

    it("renders helper text with the earliest ready time", async () => {
        vi.useFakeTimers();
        vi.setSystemTime(new Date("2026-05-06T10:00:00"));
        const wrapper = factory();

        wrapper.vm.form.items[0].product_id = 1;
        wrapper.vm.step = 2;
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain("admin.production_plans.flow.target.earliest_ready_at");
        vi.useRealTimers();
    });
});
