import { mount } from "@vue/test-utils";
import ProductionPlanForm from "./ProductionPlanForm.vue";

vi.mock("laravel-vue-i18n", () => ({
    trans: (key) =>
        ({
            "admin_production_plans.form.target_ready_at": "Kész legyen ekkor",
            "admin_production_plans.form.status": "Státusz",
            "admin_production_plans.form.lock_plan": "Terv lezárása",
            "admin_production_plans.form.notes": "Megjegyzés",
            "admin_production_plans.form.items_title": "Termékek és mennyiségek",
            "admin_production_plans.form.add_item": "Új tétel",
            "admin_production_plans.form.product": "Termék",
            "admin_production_plans.form.quantity": "Mennyiség",
            "admin_production_plans.form.unit": "Egység",
            "admin_production_plans.form.sort_order": "Sorrend",
        })[key] ?? key,
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
        template:
            '<textarea :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
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
            "<select @change=\"$emit('update:modelValue', $event.target.value)\"></select>",
    },
    Button: {
        emits: ["click"],
        template:
            '<button type="button" @click="$emit(\'click\')"><slot /></button>',
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

    it("renders key fields", () => {
        const wrapper = mount(ProductionPlanForm, {
            props: {
                form: makeForm(),
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

        expect(wrapper.text()).toContain("Kész legyen ekkor");
        expect(wrapper.text()).toContain("Megjegyzés");
        expect(wrapper.text()).toContain("Termékek és mennyiségek");
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

        await buttons[1].trigger("click");
        expect(form.items).toHaveLength(1);
    });
});
