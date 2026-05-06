import { mount } from "@vue/test-utils";
import ProductionPlanForm from "./ProductionPlanForm.vue";

vi.mock("laravel-vue-i18n", () => ({
    trans: (key, replacements = {}) => {
        let value =
            {
                "admin_production_plans.form.target_ready_at": "Kész legyen ekkor",
                "admin_production_plans.form.status": "Státusz",
                "admin_production_plans.form.lock_plan": "Terv lezárása",
                "admin_production_plans.form.notes": "Megjegyzés",
                "admin_production_plans.form.items_title": "Termékek és mennyiségek",
                "admin_production_plans.form.add_item": "Új tétel",
                "common.product": "Termék",
                "common.quantity": "Mennyiség",
                "admin_production_plans.form.unit": "Egység",
                "admin_production_plans.form.sort_order": "Sorrend",
                "admin_production_plans.requirements.title": "Összesített alapanyag igény",
                "admin_production_plans.requirements.ingredient": "Alapanyag",
                "admin_production_plans.requirements.required": "Szükséges",
                "admin_production_plans.requirements.stock": "Készlet",
                "admin_production_plans.requirements.shortage": "Hiány",
                "admin.production_plans.flow.warnings.insufficient": "Nincs elég készlet",
                "admin.production_plans.flow.warnings.low_stock": "Alacsony készlet",
                "admin.production_plans.flow.target.earliest_ready_at":
                    "A kiválasztott termékek alapján a legkorábbi elkészülési idő: :datetime",
            }[key] ?? key;

        Object.entries(replacements).forEach(([name, replacement]) => {
            value = value.replace(`:${name}`, replacement);
        });

        return value;
    },
}));

const stubs = {
    InputText: {
        props: ["modelValue", "type", "min", "step"],
        emits: ["update:modelValue"],
        template:
            '<input :value="modelValue" :type="type" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    },
    Textarea: {
        props: ["modelValue"],
        emits: ["update:modelValue"],
        template: '<textarea :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    },
    BaseDatePicker: {
        name: "BaseDatePicker",
        props: ["modelValue", "minDate"],
        emits: ["update:modelValue"],
        template: "<input />",
    },
    ToggleSwitch: {
        props: ["modelValue"],
        emits: ["update:modelValue"],
        template:
            '<input type="checkbox" :checked="modelValue" @change="$emit(\'update:modelValue\', $event.target.checked)" />',
    },
    Select: {
        props: ["modelValue", "options", "optionLabel", "optionValue"],
        emits: ["update:modelValue"],
        template:
            "<select :value='modelValue' @change=\"$emit('update:modelValue', $event.target.value)\"><option v-for='option in options' :key='option[optionValue]' :value='option[optionValue]'>{{ option[optionLabel] }}</option></select>",
    },
    Button: {
        props: ["label"],
        emits: ["click"],
        template: '<button type="button" @click="$emit(\'click\')">{{ label }}<slot /></button>',
    },
};

describe("ProductionPlanForm", () => {
    const makeForm = () => ({
        target_ready_at: "",
        status: "draft",
        is_locked: false,
        notes: "",
        items: [
            {
                product_id: 1,
                target_quantity: 1,
                unit_label: "db",
                sort_order: 0,
            },
        ],
        errors: {},
        processing: false,
    });

    const productsWithIngredients = () => [
        {
            id: 1,
            name: "Kovaszos feher kenyer",
            slug: "kovaszos-feher-kenyer",
            product_ingredients: [
                {
                    ingredient_id: 1,
                    ingredient_name: "Liszt",
                    ingredient_unit: "g",
                    quantity: 500,
                    current_stock: 1200,
                    minimum_stock: 200,
                },
                {
                    ingredient_id: 2,
                    ingredient_name: "So",
                    ingredient_unit: "g",
                    quantity: 10,
                    current_stock: 1000,
                    minimum_stock: 100,
                },
            ],
            recipe_steps: [{ id: 1, duration_minutes: 25, wait_minutes: 35 }],
        },
        {
            id: 2,
            name: "Baguette",
            slug: "baguette",
            product_ingredients: [
                {
                    ingredient_id: 1,
                    ingredient_name: "Liszt",
                    ingredient_unit: "g",
                    quantity: 300,
                    current_stock: 1200,
                    minimum_stock: 200,
                },
                {
                    ingredient_id: 3,
                    ingredient_name: "Eleszto",
                    ingredient_unit: "g",
                    quantity: 8,
                    current_stock: 20,
                    minimum_stock: 50,
                },
            ],
            recipe_steps: [{ id: 2, duration_minutes: 15, wait_minutes: 20 }],
        },
    ];

    const mountForm = (form = makeForm(), products = productsWithIngredients()) =>
        mount(ProductionPlanForm, {
            props: {
                form,
                products,
                statuses: [{ value: "draft", label: "Draft" }],
                mode: "edit",
            },
            global: { stubs },
        });

    it("renders key fields", () => {
        vi.useFakeTimers();
        vi.setSystemTime(new Date("2026-05-06T10:00:00"));
        const wrapper = mount(ProductionPlanForm, {
            props: {
                form: makeForm(),
                products: [
                    {
                        id: 1,
                        name: "Kovaszos feher kenyer",
                        slug: "kovaszos-feher-kenyer",
                        recipe_steps: [
                            {
                                id: 1,
                                duration_minutes: 25,
                                wait_minutes: 35,
                            },
                        ],
                    },
                ],
                statuses: [{ value: "draft", label: "Draft" }],
                mode: "create",
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain("Kész legyen ekkor");
        expect(wrapper.text()).toContain("Megjegyzés");
        expect(wrapper.text()).toContain("Termékek és mennyiségek");
        expect(wrapper.findComponent({ name: "BaseDatePicker" }).props("minDate").toISOString()).toBe(
            "2026-05-06T09:00:00.000Z"
        );
        expect(wrapper.text()).toContain("A kiválasztott termékek alapján a legkorábbi elkészülési idő:");
        vi.useRealTimers();
    });

    it("adds and removes item row", async () => {
        const form = makeForm();
        const wrapper = mount(ProductionPlanForm, {
            props: {
                form,
                products: [
                    {
                        id: 1,
                        name: "Kovaszos feher kenyer",
                        slug: "kovaszos-feher-kenyer",
                    },
                ],
                statuses: [{ value: "draft", label: "Draft" }],
                mode: "create",
            },
            global: { stubs },
        });

        const buttons = wrapper.findAll("button");
        await buttons[0].trigger("click");

        expect(form.items).toHaveLength(2);
        expect(form.items[1]).toEqual({
            product_id: null,
            target_quantity: null,
            unit_label: "",
            sort_order: null,
        });

        await buttons[1].trigger("click");
        expect(form.items).toHaveLength(1);
    });

    it("adds consecutive rows with fresh empty item state", async () => {
        const form = makeForm();
        form.items[0].target_quantity = 12;
        form.items[0].unit_label = "kg";
        form.items[0].sort_order = 4;

        const wrapper = mount(ProductionPlanForm, {
            props: {
                form,
                products: [{ id: 1, name: "Kovaszos feher kenyer" }],
                statuses: [{ value: "draft", label: "Draft" }],
                mode: "create",
            },
            global: { stubs },
        });

        const addButton = wrapper.findAll("button").find((button) => button.text().includes("Új tétel"));

        await addButton.trigger("click");
        await addButton.trigger("click");

        expect(form.items[1]).toEqual({
            product_id: null,
            target_quantity: null,
            unit_label: "",
            sort_order: null,
        });
        expect(form.items[2]).toEqual({
            product_id: null,
            target_quantity: null,
            unit_label: "",
            sort_order: null,
        });
        expect(form.items[1]).not.toBe(form.items[2]);
    });

    it("adds an empty item in edit mode without changing existing rows", async () => {
        const form = makeForm();
        form.items[0] = {
            product_id: 1,
            target_quantity: 8,
            unit_label: "db",
            sort_order: 0,
        };

        const wrapper = mount(ProductionPlanForm, {
            props: {
                form,
                products: [{ id: 1, name: "Kovaszos feher kenyer" }],
                statuses: [{ value: "draft", label: "Draft" }],
                mode: "edit",
            },
            global: { stubs },
        });

        await wrapper
            .findAll("button")
            .find((button) => button.text().includes("Új tétel"))
            .trigger("click");

        expect(form.items[0]).toEqual({
            product_id: 1,
            target_quantity: 8,
            unit_label: "db",
            sort_order: 0,
        });
        expect(form.items[1]).toEqual({
            product_id: null,
            target_quantity: null,
            unit_label: "",
            sort_order: null,
        });
    });

    it("product change only updates product id on a new row", async () => {
        const form = makeForm();
        const wrapper = mount(ProductionPlanForm, {
            props: {
                form,
                products: [
                    { id: 1, name: "Kovaszos feher kenyer" },
                    { id: 2, name: "Baguette" },
                ],
                statuses: [{ value: "draft", label: "Draft" }],
                mode: "create",
            },
            global: { stubs },
        });

        await wrapper
            .findAll("button")
            .find((button) => button.text().includes("Új tétel"))
            .trigger("click");
        await wrapper.findAll("select")[1].setValue("2");

        expect(form.items[1]).toMatchObject({
            product_id: "2",
            target_quantity: null,
            unit_label: "",
            sort_order: null,
        });
    });

    it("updates ingredient preview when quantity changes", async () => {
        const form = makeForm();
        const wrapper = mountForm(form);

        expect(wrapper.text()).toContain("Liszt");
        expect(wrapper.text()).toContain("500 g");

        await wrapper.setProps({
            form: {
                ...form,
                items: [{ ...form.items[0], target_quantity: 3 }],
            },
        });

        expect(wrapper.text()).toContain("1500 g");
        expect(wrapper.text()).toContain("300 g");
        expect(wrapper.text()).toContain("Nincs elég készlet");
    });

    it("updates ingredient preview when product is added and aggregates identical ingredients", async () => {
        const form = makeForm();
        const wrapper = mountForm(form);

        await wrapper.setProps({
            form: {
                ...form,
                items: [
                    ...form.items,
                    {
                        product_id: 2,
                        target_quantity: 2,
                        unit_label: "db",
                        sort_order: 1,
                    },
                ],
            },
        });

        expect(wrapper.text()).toContain("1100 g");
        expect(wrapper.text()).toContain("Eleszto");
        expect(wrapper.text()).toContain("16 g");
    });

    it("updates ingredient preview when product is removed", async () => {
        const form = makeForm();
        form.items.push({
            product_id: 2,
            target_quantity: 2,
            unit_label: "db",
            sort_order: 1,
        });
        const wrapper = mountForm(form);

        expect(wrapper.text()).toContain("Eleszto");

        await wrapper.setProps({
            form: {
                ...form,
                items: [form.items[0]],
            },
        });

        expect(wrapper.find('[data-test="ingredient-requirements"]').text()).not.toContain("Eleszto");
        expect(wrapper.text()).toContain("500 g");
    });

    it("replaces ingredient preview rows when product changes", async () => {
        const form = makeForm();
        const wrapper = mountForm(form);

        expect(wrapper.text()).toContain("So");

        await wrapper.setProps({
            form: {
                ...form,
                items: [{ ...form.items[0], product_id: 2 }],
            },
        });

        expect(wrapper.find('[data-test="ingredient-requirements"]').text()).not.toContain("So");
        expect(wrapper.text()).toContain("Eleszto");
        expect(wrapper.text()).toContain("300 g");
    });

    it("updates insufficient stock badge after quantity change", async () => {
        const form = makeForm();
        const wrapper = mountForm(form);

        expect(wrapper.text()).not.toContain("Nincs elég készlet");

        await wrapper.setProps({
            form: {
                ...form,
                items: [{ ...form.items[0], target_quantity: 4 }],
            },
        });

        expect(wrapper.text()).toContain("Nincs elég készlet");
        expect(wrapper.text()).toContain("800 g");
    });
});
