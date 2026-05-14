import { mount } from "@vue/test-utils";
import OrdersShowPage from "./Show.vue";

const { translate } = vi.hoisted(() => {
    const translations = {
        "admin_orders.eyebrow": "Admin / Rendelések",
        "admin_orders.show_meta_title": "Rendelés :number",
        "admin_orders.show_title": "Rendelés: :number",
        "admin_orders.show_description":
            "Rendelési adatok, tételek, státuszfrissítés és belső megjegyzések egy helyen.",
        "admin_orders.sections.customer_details": "Ügyfél adatok",
        "admin_orders.sections.items": "Rendelési tételek",
        "common.actions": "Műveletek",
        "common.back_to_list": "Vissza a listára",
        "common.name": "Név",
        "common.email": "Email",
        "common.phone": "Telefon",
        "common.pickup": "Átvétel",
        "common.status": "Státusz",
        "orders.address.address": "Cím",
        "orders.address.billing": "Számlázási cím",
        "orders.address.company_name": "Cégnév",
        "orders.address.door": "Ajtó",
        "orders.address.floor": "Emelet",
        "orders.address.notes": "Megjegyzés",
        "orders.address.shipping": "Szállítási cím",
        "orders.address.street": "Utca",
        "orders.address.tax_number": "Adószám",
        "orders.fulfillment.delivery_fee": "Szállítási díj",
        "orders.fulfillment.delivery_notes": "Szállítási megjegyzés",
        "orders.fulfillment.method": "Teljesítési mód",
        "orders.fulfillment.pickup_branch": "Átvételi pont",
        "admin_orders.fields.internal_notes": "Belső megjegyzés",
        "common.pickup_date": "Átvétel dátuma",
        "common.pickup_time_slot": "Átvételi idősáv",
        "admin_orders.fields.subtotal": "Részösszeg",
        "admin_orders.fields.total": "Végösszeg",
        "admin_orders.actions.update_status": "Státusz frissítése",
        "admin_orders.placeholders.pickup_time_slot": "pl. 08:00-10:00",
    };

    return {
        translate: (key, replacements = {}) => {
            let value = translations[key] ?? key;
            Object.entries(replacements).forEach(([name, replacement]) => {
                value = value.replace(`:${name}`, replacement);
            });
            return value;
        },
    };
});

vi.mock("@inertiajs/vue3", () => ({
    Head: { name: "Head", template: "<span />" },
    Link: { name: "Link", props: ["href"], template: '<a :href="href"><slot /></a>' },
    usePage: () => ({
        props: {
            locale: "hu-HU",
            preferences: { currency: "HUF", locale: "hu-HU" },
        },
    }),
    useForm: (data) => ({
        ...data,
        errors: {},
        processing: false,
        patch: vi.fn(),
    }),
}));

vi.mock("laravel-vue-i18n", () => ({
    trans: translate,
}));

vi.mock("@/Layouts/AdminLayout.vue", () => ({
    default: { template: "<div><slot /></div>" },
}));

vi.mock("primevue/button", () => ({
    default: { props: ["label"], template: "<button>{{ label }}</button>" },
}));
vi.mock("primevue/inputtext", () => ({
    default: { props: ["placeholder"], template: '<input :placeholder="placeholder" />' },
}));
vi.mock("primevue/select", () => ({
    default: { template: "<select />" },
}));
vi.mock("primevue/textarea", () => ({
    default: { template: "<textarea />" },
}));

const stubs = {
    CourierAssignmentCard: {
        props: ["order", "couriers", "canAssign"],
        template: "<section>courier assignment card</section>",
    },
    OrderFulfillmentBadge: { props: ["method", "label"], template: "<span>{{ label || method }}</span>" },
    OrderStatusBadge: { props: ["status"], template: "<span>{{ status }}</span>" },
    SectionTitle: {
        props: ["eyebrow", "title", "description"],
        template: "<section>{{ eyebrow }} {{ title }} {{ description }}</section>",
    },
};

describe("Admin Orders Show", () => {
    it("renders localized order detail sections", () => {
        const wrapper = mount(OrdersShowPage, {
            props: {
                order: {
                    id: 1,
                    order_number: "ORD-001",
                    status: "pending",
                    internal_notes: null,
                    pickup_date: "2026-05-02",
                    pickup_time_slot: "08:00-10:00",
                    fulfillment_method: "pickup",
                    fulfillment_label: "Átvétel",
                    pickup_branch: {
                        id: 1,
                        name: "Belvárosi üzlet",
                        code: "BEL",
                        type: "shop",
                        address: "Fő utca 1.",
                    },
                    billing_address_snapshot: {
                        name: "Teszt Elek",
                        country: "Magyarország",
                        postal_code: "1111",
                        city: "Budapest",
                        street: "Kovászos utca",
                        house_number: "12",
                    },
                    shipping_address_snapshot: null,
                    delivery_notes: null,
                    delivery_fee: 0,
                    customer_name: "Teszt Elek",
                    customer_email: "teszt@example.com",
                    customer_phone: "+361234567",
                    subtotal: 4000,
                    total: 4500,
                    items: [
                        {
                            id: 1,
                            product_name_snapshot: "Kovászos kenyér",
                            quantity: 2,
                            unit_price: 2000,
                            line_total: 4000,
                        },
                    ],
                },
                statusOptions: ["pending"],
            },
            global: {
                stubs,
                mocks: { $t: translate },
            },
        });

        expect(wrapper.text()).toContain("Rendelés: ORD-001");
        expect(wrapper.text()).toContain("Ügyfél adatok");
        expect(wrapper.text()).toContain("Rendelési tételek");
        expect(wrapper.text()).toContain("Teljesítési mód");
        expect(wrapper.text()).toContain("Számlázási cím");
        expect(wrapper.text()).toContain("Vissza a listára");
        expect(wrapper.find('a[href="/admin/orders"]').exists()).toBe(true);
        expect(wrapper.text()).toContain("Státusz frissítése");
    });
});
