import { mount } from "@vue/test-utils";
import BranchesIndexPage from "./Index.vue";

const { confirmRequire, translate, routeMock } = vi.hoisted(() => {
    const translations = {
        "admin_branches.actions.create": "Új üzlet vagy pékség",
        "admin_branches.actions.delete": "Telephely törlése",
        "admin_branches.actions.edit": "Telephely szerkesztése",
        "admin_branches.branch_type": "Telephely típus",
        "admin_branches.description": "Üzletek, pékségek, átvételi pontok és raktárak kezelése.",
        "admin_branches.empty": "Nincs megjeleníthető telephely.",
        "admin_branches.eyebrow": "Admin / Üzletek és pékségek",
        "admin_branches.meta_title": "Üzletek és pékségek",
        "admin_branches.search_placeholder": "Név, kód, email, telefon vagy cím",
        "admin_branches.title": "Üzletek és pékségek",
        "admin_branches.types.bakery": "Pékség",
        "admin_branches.types.shop": "Üzlet",
        "common.actions": "Műveletek",
        "common.address": "Cím",
        "common.all": "Mind",
        "common.clear_filters": "Szűrők törlése",
        "common.delete": "Törlés",
        "common.email": "Email",
        "common.name": "Név",
        "common.phone": "Telefon",
        "common.rows_per_page": "Találat / oldal",
        "common.search": "Keresés",
        "common.status": "Állapot",
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
            '<div><slot name="empty" /><div v-for="row in value" :key="row.id">{{ row.name }} {{ row.code }} {{ row.type_label }}<slot name="body" :data="row" /></div><slot /></div>',
    },
}));
vi.mock("primevue/column", () => ({
    default: {
        props: ["header"],
        computed: {
            rows() {
                return globalThis.__branchRows ?? [];
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
    BranchStatusBadge: {
        props: ["active"],
        template: '<span>{{ active ? "Aktív" : "Inaktív" }}</span>',
    },
    BranchTypeBadge: {
        props: ["type", "label"],
        template: "<span>{{ label || type }}</span>",
    },
    CreateModal: {
        props: ["visible"],
        template: '<div v-if="visible">create modal open</div>',
    },
    EditModal: {
        props: ["visible"],
        template: '<div v-if="visible">edit modal open</div>',
    },
    SectionTitle: {
        props: ["eyebrow", "title", "description"],
        template: "<section>{{ eyebrow }} {{ title }} {{ description }}</section>",
    },
};

const mountPage = (branches = []) => {
    globalThis.__branchRows = branches;

    return mount(BranchesIndexPage, {
        props: {
            branches: {
                data: branches,
                current_page: 1,
                per_page: 10,
                total: branches.length,
            },
            filters: {
                search: "",
                type: "",
                active: "",
                sort_field: "name",
                sort_direction: "asc",
                per_page: 10,
            },
            options: {
                types: [
                    { value: "bakery", label: "Pékség" },
                    { value: "shop", label: "Üzlet" },
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

describe("Admin Branches Index", () => {
    it("renders the index page", () => {
        const wrapper = mountPage([
            {
                id: 1,
                name: "Hajnalhéj Belvárosi Üzlet",
                code: "SHOP-01",
                type: "shop",
                type_label: "Üzlet",
                email: "shop@example.test",
                phone: "+36 30 111 1111",
                address: "Budapest",
                active: true,
                meta: null,
            },
        ]);

        expect(wrapper.text()).toContain("Admin / Üzletek és pékségek");
        expect(wrapper.text()).toContain("Hajnalhéj Belvárosi Üzlet");
        expect(wrapper.text()).toContain("SHOP-01");
    });

    it("opens create modal", async () => {
        const wrapper = mountPage();

        await wrapper
            .findAll("button")
            .find((button) => button.text().includes("Új üzlet vagy pékség"))
            .trigger("click");

        expect(wrapper.text()).toContain("create modal open");
    });

    it("opens edit modal", async () => {
        const wrapper = mountPage([
            {
                id: 1,
                name: "Hajnalhéj Pékség",
                code: "BAKERY-01",
                type: "bakery",
                type_label: "Pékség",
                email: null,
                phone: null,
                address: null,
                active: true,
                meta: null,
            },
        ]);

        await wrapper
            .findAll("button")
            .find((button) => button.attributes("aria-label") === "Telephely szerkesztése")
            .trigger("click");

        expect(wrapper.text()).toContain("edit modal open");
    });

    it("renders type badge", () => {
        const wrapper = mountPage([
            {
                id: 1,
                name: "Hajnalhéj Pékség",
                code: "BAKERY-01",
                type: "bakery",
                type_label: "Pékség",
                email: null,
                phone: null,
                address: null,
                active: true,
                meta: null,
            },
        ]);

        expect(wrapper.text()).toContain("Pékség");
    });

    it("renders empty state", () => {
        const wrapper = mountPage();

        expect(wrapper.text()).toContain("Nincs megjeleníthető telephely.");
        expect(wrapper.text()).toContain("Szűrők törlése");
    });
});
