import { mount } from "@vue/test-utils";
import { reactive } from "vue";
import TermForm from "./TermForm.vue";

const translations = {
    "admin_supplier_terms.form.active_helper": "Listázásban és ajánlásokban használható.",
    "admin_supplier_terms.form.lead_time_days": "Lead time (nap)",
    "admin_supplier_terms.form.meta": "Meta JSON",
    "admin_supplier_terms.form.minimum_order_quantity": "Minimum rendelési mennyiség",
    "admin_supplier_terms.form.pack_size": "Kiszerelés",
    "admin_supplier_terms.form.preferred": "Preferált",
    "admin_supplier_terms.form.preferred_helper": "Egy alapanyaghoz csak egy aktív lehet.",
    "common.active": "Aktív",
    "common.ingredient": "Alapanyag",
    "common.individual_unit_price": "Egyedi egységár",
    "common.supplier": "Beszállító",
};

const translate = (key) => translations[key] ?? key;

const stubs = {
    InputNumber: {
        props: ["modelValue"],
        emits: ["update:modelValue"],
        template:
            '<input type="number" :value="modelValue" @input="$emit(\'update:modelValue\', Number($event.target.value))" />',
    },
    Select: {
        props: ["modelValue", "options", "optionLabel", "optionValue"],
        emits: ["update:modelValue"],
        template:
            '<select><option v-for="option in options" :key="option.id" :value="option.id">{{ option[optionLabel] }}</option></select>',
    },
    Textarea: {
        props: ["modelValue"],
        emits: ["update:modelValue"],
        template: '<textarea :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    },
    ToggleSwitch: {
        props: ["modelValue", "disabled"],
        emits: ["update:modelValue"],
        template:
            '<input type="checkbox" :checked="modelValue" :disabled="disabled" @change="$emit(\'update:modelValue\', $event.target.checked)" />',
    },
};

const makeForm = () => ({
    ingredient_id: null,
    supplier_id: null,
    lead_time_days: null,
    minimum_order_quantity: null,
    pack_size: null,
    unit_cost_override: null,
    preferred: false,
    active: true,
    meta: "",
    errors: {},
    processing: false,
});

describe("IngredientSupplierTerms TermForm", () => {
    it("renders supplier term fields and validation errors", () => {
        const form = reactive(makeForm());
        form.errors.supplier_id = "A beszállító kötelező.";

        const wrapper = mount(TermForm, {
            props: {
                form,
                ingredients: [{ id: 1, name: "Buzaliszt", unit: "kg" }],
                suppliers: [{ id: 1, name: "Malom Kft." }],
            },
            global: {
                stubs,
                mocks: { $t: translate },
            },
        });

        expect(wrapper.text()).toContain("Alapanyag");
        expect(wrapper.text()).toContain("Beszállító");
        expect(wrapper.text()).toContain("Lead time (nap)");
        expect(wrapper.text()).toContain("Minimum rendelési mennyiség");
        expect(wrapper.text()).toContain("Kiszerelés");
        expect(wrapper.text()).toContain("Egyedi egységár");
        expect(wrapper.text()).toContain("Listázásban és ajánlásokban használható.");
        expect(wrapper.text()).toContain("Preferált");
        expect(wrapper.text()).toContain("Egy alapanyaghoz csak egy aktív lehet.");
        expect(wrapper.text()).toContain("Meta JSON");
        expect(wrapper.text()).toContain("A beszállító kötelező.");
    });
});
