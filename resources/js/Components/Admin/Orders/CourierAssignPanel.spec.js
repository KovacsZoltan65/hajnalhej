import { mount } from "@vue/test-utils";
import CourierAssignPanel from "./CourierAssignPanel.vue";

const { routeMock, postMock } = vi.hoisted(() => ({
    routeMock: vi.fn((name, params) => `/${name}/${params}`),
    postMock: vi.fn(),
}));

globalThis.route = routeMock;

vi.mock("@inertiajs/vue3", () => ({
    useForm: (data) => ({
        ...data,
        errors: {},
        processing: false,
        post: postMock,
        reset: vi.fn(),
    }),
}));

vi.mock("primevue/button", () => ({
    default: {
        props: ["label"],
        emits: ["click"],
        template: "<button @click=\"$emit('click')\">{{ label }}<slot /></button>",
    },
}));
vi.mock("primevue/inputtext", () => ({ default: { template: "<input />" } }));
vi.mock("primevue/select", () => ({ default: { template: "<select />" } }));
vi.mock("primevue/textarea", () => ({ default: { template: "<textarea />" } }));

const translate = (key) =>
    ({
        "delivery.actions.assign": "Futár hozzárendelése",
        "delivery.actions.cancel": "Kiszállítás lemondása",
        "delivery.actions.mark_failed": "Sikertelen kézbesítés",
        "delivery.actions.start": "Kiszállítás indítása",
        "delivery.fields.courier": "Futár",
        "delivery.fields.delivered_at": "Kézbesítve ekkor",
        "delivery.fields.delivery_scheduled_at": "Tervezett kiszállítás",
        "delivery.fields.failed_delivery_reason": "Sikertelen kézbesítés oka",
        "delivery.fields.out_for_delivery_at": "Kiszállítás indult",
        "delivery.panel_title": "Kiszállítási workflow",
    })[key] ?? key;

describe("CourierAssignPanel", () => {
    it("renders for a delivery order", () => {
        const wrapper = mount(CourierAssignPanel, {
            props: {
                order: {
                    id: 1,
                    fulfillment_method: "delivery",
                    delivery_status: "assigned",
                    delivery_status_label: "Futárhoz rendelve",
                    courier: { id: 2, name: "Biciklis Futár" },
                    delivery_scheduled_at: "2026-05-12 09:00:00",
                    out_for_delivery_at: null,
                    delivered_at: null,
                    failed_delivery_reason: null,
                },
                couriers: [
                    {
                        id: 2,
                        name: "Biciklis Futár",
                        vehicle_type_label: "Bicikli",
                        active: true,
                    },
                ],
            },
            global: {
                stubs: {
                    DeliveryStatusBadge: {
                        props: ["status", "label"],
                        template: "<span>{{ label || status }}</span>",
                    },
                },
                mocks: {
                    $t: translate,
                },
            },
        });

        expect(wrapper.text()).toContain("Kiszállítási workflow");
        expect(wrapper.text()).toContain("Biciklis Futár");
        expect(wrapper.text()).toContain("Futárhoz rendelve");
        expect(wrapper.text()).toContain("Kiszállítás indítása");
    });
});
