import { mount } from "@vue/test-utils";
import CouriersIndexPage from "./Index.vue";

const { routeMock, translate } = vi.hoisted(() => {
    const translations = {
        "admin_couriers.actions.create": "Új futár",
        "admin_couriers.actions.delete": "Futár törlése",
        "admin_couriers.actions.edit": "Futár szerkesztése",
        "admin_couriers.description": "Futárok kezelése.",
        "admin_couriers.empty": "Nincs megjeleníthető futár.",
        "admin_couriers.empty_title": "Nincs futár",
        "admin_couriers.eyebrow": "Admin / Futárok",
        "admin_couriers.meta_title": "Futárok",
        "admin_couriers.search_placeholder": "Név, email vagy telefon",
        "admin_couriers.title": "Futárok",
        "common.actions": "Műveletek",
        "common.all": "Mind",
        "common.clear_filters": "Szűrők törlése",
        "common.create": "Létrehozva",
        "common.name": "Név",
        "common.phone": "Telefon",
        "common.rows_per_page": "Találat / oldal",
        "common.search": "Keresés",
        "common.status": "Állapot",
    };

    return {
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
    usePage: () => ({ props: { locale: "hu" } }),
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
        delete: vi.fn(),
    }),
}));

vi.mock("laravel-vue-i18n", () => ({
    trans: translate,
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
    BaseDataTable: {
        props: ["value", "emptyTitle", "emptyDescription"],
        template:
            '<div><div v-if="!value.length">{{ emptyTitle }} {{ emptyDescription }}</div><div v-for="row in value" :key="row.id">{{ row.name }} {{ row.email }} {{ row.status }}<slot name="body" :data="row" /></div><slot /></div>',
    },
    RowActionMenu: {
        props: ["items"],
        template:
            '<div><button v-for="item in items" :key="item.label" :aria-label="item.label" @click="item.command">{{ item.label }}</button></div>',
    },
    CourierStatusBadge: {
        props: ["status"],
        template: '<span>{{ status === "active" ? "Aktív" : "Inaktív" }}</span>',
    },
    CreateModal: {
        props: ["visible"],
        template: '<div v-if="visible">create courier modal open</div>',
    },
    EditModal: {
        props: ["visible"],
        template: '<div v-if="visible">edit courier modal open</div>',
    },
    DeleteModal: {
        props: ["visible"],
        template: '<div v-if="visible">delete courier modal open</div>',
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
                status: "",
                sort_field: "name",
                sort_direction: "asc",
                per_page: 10,
            },
            options: {
                statusOptions: [
                    { value: "", label: "Mind" },
                    { value: "active", label: "Aktív" },
                    { value: "inactive", label: "Inaktív" },
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
                status: "active",
                notes: null,
                created_at: "2026-05-14 10:00:00",
            },
        ]);

        expect(wrapper.text()).toContain("Admin / Futárok");
        expect(wrapper.text()).toContain("Hajnalhéj Biciklis Futár");
        expect(wrapper.text()).toContain("Aktív");
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
                status: "active",
                notes: null,
                created_at: "2026-05-14 10:00:00",
            },
        ]);

        await wrapper
            .findAll("button")
            .find((button) => button.attributes("aria-label") === "Futár szerkesztése")
            .trigger("click");

        expect(wrapper.text()).toContain("edit courier modal open");
    });

    it("renders empty state", () => {
        const wrapper = mountPage();

        expect(wrapper.text()).toContain("Nincs megjeleníthető futár.");
    });

    it("opens delete modal", async () => {
        const wrapper = mountPage([
            {
                id: 1,
                name: "Törlendő Futár",
                email: null,
                phone: null,
                status: "inactive",
                notes: null,
                created_at: "2026-05-14 10:00:00",
            },
        ]);

        await wrapper
            .findAll("button")
            .find((button) => button.attributes("aria-label") === "Futár törlése")
            .trigger("click");

        expect(wrapper.text()).toContain("delete courier modal open");
    });
});
