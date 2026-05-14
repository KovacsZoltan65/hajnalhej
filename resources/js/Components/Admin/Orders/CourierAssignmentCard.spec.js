import { mount } from "@vue/test-utils";
import CourierAssignmentCard from "./CourierAssignmentCard.vue";

const { routeMock, patchMock, formState } = vi.hoisted(() => ({
    routeMock: vi.fn((name, params) => `/${name}/${params}`),
    patchMock: vi.fn(),
    formState: { processing: false },
}));

globalThis.route = routeMock;

vi.mock("@inertiajs/vue3", () => ({
    useForm: (data) => ({
        ...data,
        errors: {},
        processing: formState.processing,
        patch: patchMock,
    }),
}));

vi.mock("primevue/button", () => ({
    default: {
        props: ["label", "loading", "disabled"],
        template: '<button :disabled="disabled">{{ label }}<span v-if="loading"> loading</span></button>',
    },
}));

vi.mock("primevue/card", () => ({
    default: { template: '<section><slot name="title" /><slot name="content" /></section>' },
}));

vi.mock("primevue/message", () => ({
    default: { props: ["severity"], template: '<div :data-severity="severity"><slot /></div>' },
}));

vi.mock("primevue/select", () => ({
    default: {
        props: ["options", "disabled", "placeholder"],
        template:
            '<select :disabled="disabled"><option>{{ placeholder }}</option><option v-for="option in options" :key="option.id">{{ option.label }}</option></select>',
    },
}));

vi.mock("@/Components/Admin/Couriers/CourierStatusBadge.vue", () => ({
    default: { props: ["status"], template: "<span>{{ status }}</span>" },
}));

const translate = (key) =>
    ({
        "admin_orders.courier_assignment.empty": "Nincs futár hozzárendelve.",
        "admin_orders.courier_assignment.locked": "Nem módosítható.",
        "admin_orders.courier_assignment.pickup_disabled": "Átvételes rendeléshez nem szükséges futár.",
        "admin_orders.courier_assignment.select_label": "Aktív futár",
        "admin_orders.courier_assignment.select_placeholder": "Válassz aktív futárt",
        "admin_orders.courier_assignment.title": "Futár hozzárendelés",
        "common.save": "Mentés",
    })[key] ?? key;

const deliveryOrder = (overrides = {}) => ({
    id: 11,
    status: "confirmed",
    fulfillment_method: "delivery",
    delivery_status: "pending",
    courier: null,
    ...overrides,
});

const mountCard = (props = {}) =>
    mount(CourierAssignmentCard, {
        props: {
            order: deliveryOrder(),
            couriers: [
                { id: 1, name: "Aktív Futár", phone: "+36301234567", status: "active" },
                { id: 2, name: "Másik Futár", phone: "+36307654321", status: "active" },
            ],
            canAssign: true,
            ...props,
        },
        global: {
            mocks: { $t: translate },
        },
    });

describe("CourierAssignmentCard", () => {
    beforeEach(() => {
        routeMock.mockClear();
        patchMock.mockClear();
        formState.processing = false;
    });

    it("renders for a delivery order", () => {
        const wrapper = mountCard({
            order: deliveryOrder({
                courier: { id: 1, name: "Aktív Futár", phone: "+36301234567", status: "active" },
            }),
        });

        expect(wrapper.text()).toContain("Futár hozzárendelés");
        expect(wrapper.text()).toContain("Aktív Futár");
        expect(wrapper.text()).toContain("+36301234567");
        expect(wrapper.text()).toContain("active");
    });

    it("shows an informative disabled state for pickup orders", () => {
        const wrapper = mountCard({
            order: deliveryOrder({ fulfillment_method: "pickup" }),
        });

        expect(wrapper.text()).toContain("Átvételes rendeléshez nem szükséges futár.");
        expect(wrapper.find("select").attributes("disabled")).toBeDefined();
    });

    it("renders active couriers in the select", () => {
        const wrapper = mountCard();

        expect(wrapper.text()).toContain("Aktív Futár - +36301234567");
        expect(wrapper.text()).toContain("Másik Futár - +36307654321");
    });

    it("calls the Ziggy named route on save", async () => {
        const wrapper = mountCard();

        await wrapper.find("form").trigger("submit");

        expect(routeMock).toHaveBeenCalledWith("admin.orders.assign-courier", 11);
        expect(patchMock).toHaveBeenCalledWith("/admin.orders.assign-courier/11", {
            preserveScroll: true,
        });
    });

    it("shows the loading state", () => {
        formState.processing = true;

        const wrapper = mountCard();

        expect(wrapper.text()).toContain("loading");
        expect(wrapper.find("button").attributes("disabled")).toBeDefined();
    });
});
