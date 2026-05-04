import { mount } from "@vue/test-utils";
import PermissionsIndexPage from "./Index.vue";

const { translate } = vi.hoisted(() => {
    const translations = {
        "admin_permissions.filters.per_page_option": ":count / oldal",
        "admin_permissions.meta_title": "Jogosultságok",
        "admin_permissions.eyebrow": "Admin / Szerepkörök és jogosultságok",
        "admin_permissions.title": "Jogosultságok",
        "admin_permissions.description":
            "Registry-alapú jogosultságlista, használatnézet és drift ellenőrzés.",
        "admin_permissions.filters.search": "Keresés",
        "admin_permissions.filters.search_placeholder": "Név, címke, leírás...",
        "admin_permissions.filters.module": "Modul",
        "admin_permissions.filters.all_modules": "Minden modul",
        "admin_permissions.filters.usage": "Használat",
        "admin_permissions.filters.registry_state": "Registry állapot",
        "admin_permissions.filters.all_registry_states": "Minden állapot",
        "admin_permissions.filters.sort_field": "Rendezés mező",
        "admin_permissions.filters.sort_direction": "Irány",
        "admin_permissions.filters.per_page": "Találat / oldal",
        "admin_permissions.filters.dangerous_only": "Csak veszélyes",
        "admin_permissions.columns.permission": "Jogosultság",
        "admin_permissions.columns.module": "Modul",
        "admin_permissions.columns.risk": "Kockázat",
        "admin_permissions.columns.registry_state": "Registry állapot",
        "admin_permissions.columns.roles": "Szerepkörök",
        "admin_permissions.columns.users": "Felhasználók",
        "admin_permissions.columns.guard": "Guard",
        "admin_permissions.actions.filter": "Szűrés",
        "admin_permissions.actions.registry_sync": "Registry szinkron",
        "admin_permissions.actions.details": "Részletek",
        "admin_permissions.empty": "Nincs megjeleníthető jogosultság.",
        "admin_permissions.usage_states.used": "Használt",
        "admin_permissions.usage_states.unused": "Nem használt",
        "admin_permissions.registry_states.synced": "Szinkronizált",
        "admin_permissions.registry_states.missing_in_db":
            "Hiányzik az adatbázisból",
        "admin_permissions.registry_states.orphan_db_only": "Csak adatbázisban",
        "common.name": "Név",
        "admin_permissions.sort_fields.module": "Modul",
        "admin_permissions.sort_fields.roles_count": "Szerepkör használat",
        "admin_permissions.sort_fields.users_count": "Felhasználói használat",
        "admin_permissions.sort_fields.registry_state": "Registry állapot",
        "admin_permissions.sort_directions.asc": "Növekvő",
        "admin_permissions.sort_directions.desc": "Csökkenő",
        "common.all": "Mind",
        "common.clear_filters": "Szűrők törlése",
        "common.actions": "Műveletek",
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
    Link: {
        name: "Link",
        props: ["href"],
        template: '<a :href="href"><slot /></a>',
    },
    router: { get: vi.fn(), post: vi.fn() },
    usePage: () => ({ props: { flash: {} } }),
}));

vi.mock("laravel-vue-i18n", () => ({
    trans: translate,
}));

vi.mock("@/Layouts/AdminLayout.vue", () => ({
    default: { template: "<div><slot /></div>" },
}));

vi.mock("primevue/button", () => ({
    default: {
        props: ["label"],
        template: "<button>{{ label }}<slot /></button>",
    },
}));
vi.mock("primevue/checkbox", () => ({
    default: { template: '<input type="checkbox" />' },
}));
vi.mock("primevue/inputtext", () => ({
    default: {
        props: ["placeholder"],
        template: '<input :placeholder="placeholder" />',
    },
}));
vi.mock("primevue/select", () => ({
    default: { template: "<div />" },
}));
vi.mock("primevue/datatable", () => ({
    default: {
        props: ["value"],
        template:
            '<div><slot name="empty" /><div v-for="row in value" :key="row.name">{{ row.name }} {{ row.module }}</div><slot /></div>',
    },
}));
vi.mock("primevue/column", () => ({
    default: { props: ["header"], template: "<div>{{ header }}<slot /></div>" },
}));

const stubs = {
    AdminTableToolbar: {
        template: '<div><slot name="filters" /><slot name="actions" /></div>',
    },
    PermissionBadge: {
        props: ["name"],
        template: "<span>{{ name }}</span>",
    },
    PermissionDangerBadge: {
        template: "<span>risk</span>",
    },
    PermissionRegistryStateBadge: {
        props: ["state"],
        template: "<span>{{ state }}</span>",
    },
    PermissionSyncSummaryModal: {
        template: "<div />",
    },
    SectionTitle: {
        props: ["eyebrow", "title", "description"],
        template:
            "<section>{{ eyebrow }} {{ title }} {{ description }}</section>",
    },
};

describe("Admin Permissions Index", () => {
    it("renders localized permission list controls and rows", () => {
        const wrapper = mount(PermissionsIndexPage, {
            props: {
                permissions: {
                    data: [
                        {
                            name: "permissions.view",
                            module: "Permissions",
                            dangerous: false,
                            registry_state: "synced",
                            roles_count: 1,
                            users_count: 2,
                            guard_name: "web",
                        },
                    ],
                    current_page: 1,
                    per_page: 20,
                    total: 1,
                },
                modules: ["Permissions"],
                filters: {},
                can: { sync: true },
            },
            global: {
                stubs,
                mocks: { $t: translate },
            },
        });

        expect(wrapper.text()).toContain(
            "Admin / Szerepkörök és jogosultságok",
        );
        expect(wrapper.text()).toContain("Keresés");
        expect(wrapper.text()).toContain("Jogosultság");
        expect(wrapper.text()).toContain("permissions.view");
        expect(wrapper.text()).toContain("Műveletek");
    });
});
