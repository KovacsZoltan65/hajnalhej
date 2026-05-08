import { mount } from "@vue/test-utils";
import WeeklyMenuItemForm from "./WeeklyMenuItemForm.vue";

vi.mock("@/composables/useLocaleFormat", () => ({
    useLocaleFormat: () => ({
        formatCurrency: (value) => `${value} Ft`,
    }),
}));

const translations = {
    "admin_weekly_menus.item_form.badge_text": "Badge szöveg",
    "admin_weekly_menus.item_form.badge_text_placeholder": "pl. Új",
    "admin_weekly_menus.item_form.override_name": "Felülírt név",
    "admin_weekly_menus.item_form.override_price": "Felülírt ár",
    "admin_weekly_menus.item_form.override_price_placeholder": "Ft",
    "admin_weekly_menus.item_form.override_short_description": "Rövid leírás (felülírás)",
    "admin_weekly_menus.item_form.product_placeholder": "Válassz terméket",
    "admin_weekly_menus.item_form.stock_note": "Készlet megjegyzés",
    "admin_weekly_menus.item_form.stock_note_placeholder": "pl. limitált",
    "common.active": "Aktív",
    "common.optional": "opcionális",
    "common.product": "Termék",
    "common.sort_order": "Sorrend",
    "common.status": "Állapot",
};

const stubs = {
    InputNumber: {
        props: ["modelValue", "placeholder"],
        template: '<input :value="modelValue" :placeholder="placeholder" />',
    },
    InputText: {
        props: ["modelValue", "placeholder"],
        template: '<input :value="modelValue" :placeholder="placeholder" />',
    },
    Select: {
        props: ["placeholder"],
        template: '<input role="combobox" :placeholder="placeholder" />',
    },
    ToggleSwitch: {
        props: ["modelValue"],
        template: '<input type="checkbox" :checked="modelValue" />',
    },
};

describe("WeeklyMenuItemForm", () => {
    it("renders localized labels and placeholders", () => {
        const wrapper = mount(WeeklyMenuItemForm, {
            props: {
                form: {
                    product_id: null,
                    override_name: "",
                    override_price: null,
                    override_short_description: "",
                    sort_order: 0,
                    badge_text: "",
                    stock_note: "",
                    is_active: true,
                },
                products: [],
            },
            global: {
                mocks: {
                    $t: (key) => translations[key] ?? key,
                },
                stubs,
            },
        });

        expect(wrapper.text()).toContain("Termék");
        expect(wrapper.text()).toContain("Felülírt név");
        expect(wrapper.text()).toContain("Felülírt ár");
        expect(wrapper.text()).toContain("Rövid leírás (felülírás)");
        expect(wrapper.text()).toContain("Sorrend");
        expect(wrapper.text()).toContain("Badge szöveg");
        expect(wrapper.text()).toContain("Készlet megjegyzés");
        expect(wrapper.text()).toContain("Állapot");
        expect(wrapper.text()).toContain("Aktív");

        expect(wrapper.find('input[role="combobox"]').attributes("placeholder")).toBe("Válassz terméket");
        expect(wrapper.find('input[placeholder="pl. Új"]').exists()).toBe(true);
        expect(wrapper.find('input[placeholder="pl. limitált"]').exists()).toBe(true);
    });
});
