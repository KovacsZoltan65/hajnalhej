import { mount } from "@vue/test-utils";
import { nextTick } from "vue";
import { reactive } from "vue";
import CheckoutPage from "./Index.vue";

const translations = {
    "orders.order_summary": "Rendelés összegzése",
    "orders.fulfillment.title": "Teljesítés",
    "orders.fulfillment.method": "Teljesítési mód",
    "orders.fulfillment.pickup_branch": "Átvételi pont",
    "orders.fulfillment.delivery_notes": "Szállítási megjegyzés",
};

const translate = (key) => translations[key] ?? key;

let formState = reactive({
    customer_name: "",
    customer_email: "",
    customer_phone: "",
    notes: "",
    pickup_date: null,
    pickup_time_slot: null,
    fulfillment_method: "pickup",
    pickup_branch_id: 1,
    billing_address: {
        name: "",
        country: "Magyarország",
        postal_code: "",
        city: "",
        street: "",
        house_number: "",
        floor: "",
        door: "",
        company_name: "",
        tax_number: "",
        phone: "",
        notes: "",
    },
    shipping_address: {
        name: "",
        country: "Magyarország",
        postal_code: "",
        city: "",
        street: "",
        house_number: "",
        floor: "",
        door: "",
        company_name: "",
        tax_number: "",
        phone: "",
        notes: "",
    },
    same_as_billing: true,
    delivery_notes: "",
    accept_privacy: false,
    accept_terms: false,
    errors: {},
    processing: false,
    post: vi.fn(),
});

vi.mock("@inertiajs/vue3", () => ({
    Head: { name: "Head", template: "<span />" },
    Link: { name: "Link", props: ["href"], template: '<a :href="href"><slot /></a>' },
    usePage: () => ({
        props: {
            preferences: { locale: "hu-HU", currency: "HUF" },
        },
    }),
    useForm: () => formState,
}));

vi.mock("../../Layouts/PublicLayout.vue", () => ({
    default: { template: "<div><slot /></div>" },
}));

vi.mock("primevue/button", () => ({
    default: {
        name: "Button",
        props: ["disabled", "label"],
        template: '<button :disabled="disabled">{{ label }}</button>',
    },
}));
vi.mock("primevue/inputtext", () => ({
    default: { name: "InputText", template: "<input />" },
}));
vi.mock("primevue/inputmask", () => ({
    default: { name: "InputMask", template: "<input />" },
}));
vi.mock("primevue/textarea", () => ({
    default: { name: "Textarea", template: "<textarea />" },
}));
vi.mock("primevue/select", () => ({
    default: {
        name: "Select",
        props: ["modelValue", "options", "optionLabel", "optionValue", "placeholder"],
        emits: ["update:modelValue"],
        template:
            '<select :aria-label="placeholder || optionLabel" :value="modelValue" @change="$emit(\'update:modelValue\', $event.target.value)"><option v-for="option in options" :key="option[optionValue] ?? option.value" :value="option[optionValue] ?? option.value">{{ option[optionLabel] ?? option.label }}</option></select>',
    },
}));
vi.mock("primevue/checkbox", () => ({
    default: { name: "Checkbox", template: '<input type="checkbox" />' },
}));
vi.mock("primevue/datepicker", () => ({
    default: {
        name: "DatePicker",
        props: ["modelValue"],
        emits: ["update:modelValue"],
        template: '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    },
}));

describe("Checkout page", () => {
    beforeEach(() => {
        formState = reactive({
            customer_name: "",
            customer_email: "",
            customer_phone: "",
            notes: "",
            pickup_date: null,
            pickup_time_slot: null,
            fulfillment_method: "pickup",
            pickup_branch_id: 1,
            billing_address: {
                name: "",
                country: "Magyarország",
                postal_code: "",
                city: "",
                street: "",
                house_number: "",
                floor: "",
                door: "",
                company_name: "",
                tax_number: "",
                phone: "",
                notes: "",
            },
            shipping_address: {
                name: "",
                country: "Magyarország",
                postal_code: "",
                city: "",
                street: "",
                house_number: "",
                floor: "",
                door: "",
                company_name: "",
                tax_number: "",
                phone: "",
                notes: "",
            },
            same_as_billing: true,
            delivery_notes: "",
            accept_privacy: false,
            accept_terms: false,
            errors: {},
            processing: false,
            post: vi.fn(),
        });
    });

    const mountPage = (prefillOverrides = {}) =>
        mount(CheckoutPage, {
            props: {
                cart: {
                    items: [{ product_id: 1, name: "Kovaszos vekni", quantity: 1, unit_price: 1200, line_total: 1200 }],
                    summary: { total: 1200 },
                },
                prefill: {
                    customer_name: "Teszt",
                    customer_email: "teszt@example.com",
                    customer_phone: "",
                    notes: "",
                    pickup_date: null,
                    pickup_time_slot: null,
                    fulfillment_method: "pickup",
                    pickup_branch_id: 1,
                    billing_address: formState.billing_address,
                    shipping_address: formState.shipping_address,
                    same_as_billing: true,
                    delivery_notes: "",
                    ...prefillOverrides,
                },
                fulfillmentOptions: [
                    { value: "pickup", label: "Átvétel üzletben" },
                    { value: "delivery", label: "Kiszállítás" },
                ],
                pickupBranches: [{ id: 1, name: "Belvarosi uzlet", code: "BEL", type: "shop", address: "Fo utca 1." }],
            },
            global: {
                mocks: {
                    $t: translate,
                    route: (name) => `/${name.replaceAll(".", "/")}`,
                },
            },
        });

    it("renders order summary", () => {
        const wrapper = mountPage();

        expect(wrapper.text()).toContain("Rendelés összegzése");
        expect(wrapper.text()).toContain("Kovaszos vekni");
    });

    it("renders checkout fulfillment selector", () => {
        const wrapper = mountPage();

        expect(wrapper.text()).toContain("Teljesítés");
        expect(wrapper.text()).toContain("Teljesítési mód");
        expect(wrapper.text()).toContain("Átvétel üzletben");
    });

    it("shows pickup branch selector for pickup orders", () => {
        formState.fulfillment_method = "pickup";

        const wrapper = mountPage();

        expect(wrapper.text()).toContain("Átvételi pont");
        expect(wrapper.text()).toContain("Belvarosi uzlet - Fo utca 1.");
    });

    it("shows delivery address block for delivery orders", async () => {
        formState.fulfillment_method = "delivery";
        formState.same_as_billing = false;

        const wrapper = mountPage({ fulfillment_method: "delivery", same_as_billing: false });
        await nextTick();

        expect(wrapper.find("#shipping_name").exists()).toBe(true);
        expect(wrapper.text()).toContain("Szállítási megjegyzés");
    });

    it("toggles shipping form with same as billing checkbox state", async () => {
        formState.fulfillment_method = "delivery";
        formState.same_as_billing = true;

        const wrapper = mountPage({ fulfillment_method: "delivery", same_as_billing: true });

        expect(wrapper.find("#shipping_name").exists()).toBe(false);

        formState.same_as_billing = false;
        await nextTick();

        expect(wrapper.find("#shipping_name").exists()).toBe(true);
    });

    it("disables submit while processing", () => {
        formState.processing = true;

        const wrapper = mountPage();

        expect(wrapper.find("button").attributes("disabled")).toBeDefined();
    });
});
