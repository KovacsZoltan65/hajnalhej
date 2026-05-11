import { mount } from "@vue/test-utils";
import CouriersIndexPage from "./Index.vue";

const { confirmRequire, routeMock, translate } = vi.hoisted(() => {
    const translations = {
        "admin_couriers.actions.create": "Új futár",
        "admin_couriers.actions.delete": "Futár törlése",
        "admin_couriers.actions.edit": "Futár szerkesztése",
        "admin_couriers.description": "Futárok kezelése.",
        "admin_couriers.empty": "Nincs megjeleníthető futár.",
        "admin_couriers.eyebrow": "Admin / Futárok",
        "admin_couriers.meta_title": "Futárok",
        "admin_couriers.search_placeholder": "Név, email vagy telefon",
        "admin_couriers.title": "Futárok",
        "common.actions": "Műveletek",
        "common.all": "Mind",
        "common.clear_filters": "Szűrők törlése",
        "common.name": "Név",
        "common.phone": "Telefon",
        "common.rows_per_page": "Találat / oldal",
        "common.search": "Keresés",
        "common.status": "Állapot",
        "delivery.vehicle_type": "Járműtípus",
    };

    return {
        confirmRequire: vi.fn(),
        routeMock: vi.fn((name, params) => (params ? `/${name}/${params}` : `/${name}`)),
        translate: (key, replacements = {}) => {
            let value = translations[key] ?? key;

            Object.entries(replacements).forEach(([name, replacement]) => {
                value = value.replace(`:${name}`, String(replacement));
            });

            return value;
        },
    };
});

globalThis.route = routeMock;

vi.mock("@inertiajs/vue3", () => ({
    Head: { name: "Head", template: "<span />" },
    router: { get: vi.fn(), delete: vi.fn() },
    useForm: (defaults) => ({
        ...defaults,
        errors: {},
        processing: false,
        reset: vi.fn(),
        clearErrors: vi.fn(),
        setError: vi.fn(),
        transform: vi.fn(function () {
            return this;
        }),
        post: vi.fn(),
        put: vi.fn(),
    }),
}));

vi.mock("laravel-vue-i18n", () => ({
    trans: translate,
}));

vi.mock("primevue/useconfirm", () => ({
    useConfirm: () => ({ require: confirmRequire }),
}));

vi.mock("@/Layouts/AdminLayout.vue", () => ({
    default: { template: "<div><slot /></div>" },
}));

vi.mock("primevue/button", () => ({
    default: {
        props: ["label", "ariaLabel"],
        emits: ["click"],
        template: '<button :aria-label="ariaLabel" @click="$emit(\'click\')">{{ label }}<slot /></button>',
    },
}));
vi.mock("primevue/confirmdialog", () => ({ default: { template: "<div />" } }));
vi.mock("primevue/datatable", () => ({
    default: {
        props: ["value"],
        template:
            '<div><slot name="empty" /><div v-for="row in value" :key="row.id">{{ row.name }} {{ row.email }} {{ row.vehicle_type_label }}<slot name="body" :data="row" /></div><slot /></div>',
    },
}));
vi.mock("primevue/column", () => ({
    default: {
        props: ["header"],
        computed: {
            rows() {
                return globalThis.__courierRows ?? [];
            },
        },
        template: '<div>{{ header }}<slot v-for="row in rows" name="body" :data="row" /><slot /></div>',
    },
}));
vi.mock("primevue/inputtext", () => ({
    default: { props: ["placeholder"], template: '<input :placeholder="placeholder" />' },
}));
vi.mock("primevue/select", () => ({ default: { template: "<select />" } }));

const stubs = {
    AdminTableToolbar: {
        template: '<div><slot name="filters" /><slot name="actions" /></div>',
    },
    CourierStatusBadge: {
        props: ["active"],
        template: '<span>{{ active ? "Aktív" : "Inaktív" }}</span>',
    },
    VehicleTypeBadge: {
        props: ["type", "label"],
        template: "<span>{{ label || type }}</span>",
    },
    CreateModal: {
        props: ["visible"],
        template: '<div v-if="visible">create courier modal open</div>',
    },
    EditModal: {
        props: ["visible"],
        template: '<div v-if="visible">edit courier modal open</div>',
    },
    SectionTitle: {
        props: ["eyebrow", "title", "description"],
        template: "<section>{{ eyebrow }} {{ title }} {{ description }}</section>",
    },
};

const mountPage = (couriers = []) => {
    globalThis.__courierRows = couriers;

    return mount(CouriersIndexPage, {
        props: {
            couriers: {
                data: couriers,
                current_page: 1,
                per_page: 10,
                total: couriers.length,
            },
            filters: {
                search: "",
                vehicle_type: "",
                active: "",
                sort_field: "name",
                sort_direction: "asc",
                per_page: 10,
            },
            options: {
                vehicleTypes: [
                    { value: "bicycle", label: "Bicikli" },
                    { value: "car", label: "Autó" },
                ],
                activeOptions: [
                    { value: "", label: "Mind" },
                    { value: "1", label: "Aktív" },
                    { value: "0", label: "Inaktív" },
                ],
            },
            can: {
                create: true,
                update: true,
                delete: true,
            },
        },
        global: {
            stubs,
            mocks: {
                $t: translate,
            },
        },
    });
};

describe("Admin Couriers Index", () => {
    it("renders the index page", () => {
        const wrapper = mountPage([
            {
                id: 1,
                name: "Hajnalhéj Biciklis Futár",
                email: "courier@example.test",
                phone: "+36 30 111 1111",
                vehicle_type: "bicycle",
                vehicle_type_label: "Bicikli",
                active: true,
                notes: null,
                meta: null,
            },
        ]);

        expect(wrapper.text()).toContain("Admin / Futárok");
        expect(wrapper.text()).toContain("Hajnalhéj Biciklis Futár");
        expect(wrapper.text()).toContain("Bicikli");
    });

    it("opens create modal", async () => {
        const wrapper = mountPage();

        await wrapper
            .findAll("button")
            .find((button) => button.text().includes("Új futár"))
            .trigger("click");

        expect(wrapper.text()).toContain("create courier modal open");
    });

    it("opens edit modal", async () => {
        const wrapper = mountPage([
            {
                id: 1,
                name: "Autós Futár",
                email: null,
                phone: null,
                vehicle_type: "car",
                vehicle_type_label: "Autó",
                active: true,
                notes: null,
                meta: null,
            },
        ]);

        await wrapper
            .findAll("button")
            .find((button) => button.attributes("aria-label") === "Futár szerkesztése")
            .trigger("click");

        expect(wrapper.text()).toContain("edit courier modal open");
    });
});
