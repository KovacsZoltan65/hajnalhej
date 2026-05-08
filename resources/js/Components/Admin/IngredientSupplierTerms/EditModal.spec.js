import { mount } from "@vue/test-utils";
import EditModal from "./EditModal.vue";
import { trans } from "laravel-vue-i18n";

const stubs = {
    Button: { props: ["label"], template: "<button>{{ label }}</button>" },
    Dialog: {
        props: ["visible", "header"],
        template: '<div v-if="visible"><h2>{{ header }}</h2><slot /><slot name="footer" /></div>',
    },
    TermForm: { template: "<div>Term form</div>" },
};

describe("IngredientSupplierTerms EditModal", () => {
    it("renders edit modal", () => {
        const wrapper = mount(EditModal, {
            props: {
                visible: true,
                form: { processing: false, errors: {} },
                ingredients: [],
                suppliers: [],
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain(trans("admin_supplier_terms.actions.edit"));
        expect(wrapper.text()).toContain(trans("common.save"));
    });
});
